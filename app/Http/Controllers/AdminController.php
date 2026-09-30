<?php

namespace App\Http\Controllers;

use App\Models\ApiEngine;
use App\Models\SearchQuery;
use App\Models\User;
use App\Services\SearchCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    // Valid search query types whitelist
    private const VALID_TYPES = ['phone', 'email', 'vehicle', 'challan', 'corporate', 'social', 'verification', 'leak', 'upi'];

    // Fields returned in user listings (never expose otp, password, remember_token)
    private const USER_FIELDS = ['id', 'name', 'email', 'app_mode', 'credits', 'is_admin', 'cms_role', 'email_verified_at', 'created_at', 'updated_at'];

    private function auditLog(string $action, array $context = []): void
    {
        Log::channel('stack')->info("[ADMIN AUDIT] {$action}", array_merge([
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()?->email,
            'ip' => request()->ip(),
        ], $context));
    }

    // ── Stats Overview ──────────────────────────────────────────────────────
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'total_queries' => SearchQuery::count(),
            'queries_today' => SearchQuery::whereDate('created_at', today())->count(),
            'queries_by_type' => SearchQuery::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
        ]);
    }

    // ── Users List ──────────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $request->validate([
            'search' => 'sometimes|string|max:100',
            'app_mode' => 'sometimes|in:trial,live',
            'page' => 'sometimes|integer|min:1|max:1000',
        ]);

        $query = User::select(self::USER_FIELDS)
            ->withCount('searchQueries');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($mode = $request->query('app_mode')) {
            $query->where('app_mode', $mode);
        }

        $users = $query->latest()->paginate(15);

        $allApiEngineIds = ApiEngine::all()->pluck('id')->toArray();
        $totalApis = count($allApiEngineIds);

        $users->getCollection()->transform(function ($user) use ($totalApis, $allApiEngineIds) {
            $userData = $user->toArray();
            if ($user->is_admin) {
                $userData['api_count'] = $totalApis;
                $userData['has_all_apis'] = true;
                $userData['api_engine_ids'] = $allApiEngineIds;
            } else {
                $userData['api_count'] = $user->apiEngines->count();
                $userData['has_all_apis'] = false;
                $userData['api_engine_ids'] = $user->apiEngines->pluck('id')->toArray();
            }

            return $userData;
        });

        return response()->json($users);
    }

    // ── Single User ─────────────────────────────────────────────────────────
    public function user($id)
    {
        abort_unless(is_numeric($id), 400, 'Invalid user ID.');

        $user = User::select(self::USER_FIELDS)
            ->withCount('searchQueries')
            ->with(['ips:id,user_id,ip,created_at'])
            ->findOrFail((int) $id);

        $queriesByType = SearchQuery::where('user_id', (int) $id)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        $apiEngines = $user->is_admin
            ? ApiEngine::all()->pluck('id')->toArray()
            : $user->apiEngines->pluck('id')->toArray();

        $userData = $user->toArray();
        $userData['api_engine_ids'] = $apiEngines;
        $userData['api_count'] = count($apiEngines);

        return response()->json([
            'user' => $userData,
            'queries_by_type' => $queriesByType,
        ]);
    }

    // ── Create User ─────────────────────────────────────────────────────────
    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[\pL\s\-]+$/u',
            'email' => 'required|email:rfc,dns|max:255|unique:users,email',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()],
            'credits' => 'sometimes|numeric|min:0|max:999999',
            'app_mode' => 'sometimes|in:trial,live',
            'is_admin' => 'sometimes|boolean',
            'cms_role' => 'sometimes|in:auditor,investigator,supervisor',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'email_verified_at' => now(),
            'credits' => $validated['credits'] ?? 0,
            'app_mode' => $validated['app_mode'] ?? 'live',
            'is_admin' => $validated['is_admin'] ?? false,
            'cms_role' => $validated['cms_role'] ?? 'auditor',
        ]);

        $this->auditLog('USER_CREATED', ['target_id' => $user->id, 'target_email' => $user->email]);

        return response()->json(['message' => 'User created.', 'user' => $user->only(self::USER_FIELDS)], 201);
    }

    // ── Update User ─────────────────────────────────────────────────────────
    public function updateUser(Request $request, $id)
    {
        abort_unless(is_numeric($id), 400, 'Invalid user ID.');

        $user = User::findOrFail((int) $id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'credits' => 'sometimes|numeric|min:0|max:999999',
            'app_mode' => 'sometimes|in:trial,live',
            'is_admin' => 'sometimes|boolean',
        ]);

        $wasAdmin = $user->is_admin;

        // Prevent admin from revoking their own admin access
        if (isset($validated['is_admin']) && ! $validated['is_admin'] && $user->id === auth()->id()) {
            // Allow update but don't change is_admin, just warn
            $user->update(['name' => $validated['name'] ?? $user->name, 'credits' => $validated['credits'] ?? $user->credits, 'app_mode' => $validated['app_mode'] ?? $user->app_mode]);

            $this->auditLog('SELF_REVOKE_PREVENTED', [
                'target_id' => $user->id,
                'target_email' => $user->email,
            ]);

            return response()->json([
                'message' => 'User updated.',
                'warning' => 'You cannot revoke your own admin access.',
                'user' => $user->only(self::USER_FIELDS),
            ]);
        }

        $wasAdmin = $user->is_admin;
        $before = $user->only(array_keys($validated));
        $user->update($validated);

        $adminChange = null;
        if (array_key_exists('is_admin', $validated)) {
            $allApiIds = ApiEngine::all()->pluck('id')->toArray();
            $currentPerms = $user->apiEngines->pluck('id')->toArray();

            if ($validated['is_admin']) {
                $adminChange = [
                    'action' => 'grant',
                    'permissions_count' => count($allApiIds),
                    'current_permissions' => count($currentPerms),
                ];
            } elseif ($wasAdmin && $user->id !== auth()->id()) {
                $adminChange = [
                    'action' => 'revoke',
                    'permissions_count' => count($currentPerms),
                    'current_permissions' => count($currentPerms),
                ];
            }
        }

        $this->auditLog('USER_UPDATED', [
            'target_id' => $user->id,
            'target_email' => $user->email,
            'before' => $before,
            'after' => $validated,
        ]);

        return response()->json([
            'message' => 'User updated.',
            'user' => $user->only(self::USER_FIELDS),
            'admin_change' => $adminChange,
        ]);
    }

    // ── Sync Admin Permissions ───────────────────────────────────────────────
    public function syncAdminPermissions(Request $request, $id)
    {
        abort_unless(is_numeric($id), 400, 'Invalid user ID.');

        $user = User::findOrFail((int) $id);

        $validated = $request->validate([
            'action' => 'required|in:grant,revoke',
        ]);

        $allApiIds = ApiEngine::all()->pluck('id')->toArray();

        if ($validated['action'] === 'grant') {
            $user->apiEngines()->sync($allApiIds);

            $this->auditLog('ADMIN_ROLE_GRANTED', [
                'target_id' => $user->id,
                'target_email' => $user->email,
                'permissions_granted' => count($allApiIds),
            ]);
        } else {
            $user->apiEngines()->sync([]);

            $this->auditLog('ADMIN_ROLE_REVOKED', [
                'target_id' => $user->id,
                'target_email' => $user->email,
                'permissions_revoked' => $user->apiEngines()->count(),
            ]);
        }

        return response()->json(['message' => 'Permissions synced.']);
    }

    // ── Delete User ─────────────────────────────────────────────────────────
    public function deleteUser($id)
    {
        abort_unless(is_numeric($id), 400, 'Invalid user ID.');

        $user = User::findOrFail((int) $id);

        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot delete your own account.'], 422);
        }

        // Prevent deleting other admins
        if ($user->is_admin) {
            return response()->json(['error' => 'Cannot delete another admin account.'], 422);
        }

        $this->auditLog('USER_DELETED', ['target_id' => $user->id, 'target_email' => $user->email]);

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    // ── Search Queries List ─────────────────────────────────────────────────
    public function queries(Request $request)
    {
        $request->validate([
            'search' => 'sometimes|string|max:100',
            'user' => 'sometimes|string|max:100',
            'type' => 'sometimes|in:'.implode(',', self::VALID_TYPES),
            'user_id' => 'sometimes|integer|min:1',
            'page' => 'sometimes|integer|min:1|max:1000',
        ]);

        $query = SearchQuery::with(['user:id,name,email', 'user.latestIp', 'result'])->latest();

        if ($search = $request->query('search')) {
            $query->where('query', 'like', "%{$search}%");
        }

        if ($name = $request->query('user')) {
            $query->whereHas('user', function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%")
                    ->orWhere('email', 'like', "%{$name}%");
            });
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type); // already whitelisted above
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', (int) $userId);
        }

        return response()->json($query->paginate(20));
    }

    // ── Resolve a Search Query by public_id, falling back to numeric id ──
    private function resolveSearchQuery(string $key): SearchQuery
    {
        $row = SearchQuery::where('public_id', $key)->first();

        if (! $row && ctype_digit($key)) {
            $row = SearchQuery::find((int) $key);
        }

        abort_if(! $row, 404, 'Search query not found.');

        return $row;
    }

    /**
     * Every hash under which this row could be serving cached data.
     *
     * SearchCache::findHit() hashes the value it was handed, while the row
     * stores the hash of the stringified query, so array-valued types (leak,
     * corporate) can carry a different hash than the lookup computes. Matching
     * on the whole set keeps the purge correct either way.
     *
     * NULLs are dropped on purpose: where('query_hash', null) compiles to
     * IS NULL and would match every hashless row of that type.
     *
     * @return array<int, string>
     */
    private function cacheHashesFor(SearchQuery $row): array
    {
        $hashes = [$row->query_hash];

        if (! empty($row->type)) {
            $hashes[] = SearchCache::hash($row->type, $row->query);

            $decoded = json_decode((string) $row->query, true);
            if (is_array($decoded)) {
                $hashes[] = SearchCache::hash($row->type, $decoded);
            }
        }

        return array_values(array_unique(array_filter($hashes)));
    }

    // ── Delete Impact Preview ────────────────────────────────────────
    public function queryDeleteImpact(string $key)
    {
        $row = $this->resolveSearchQuery($key);
        $hashes = $this->cacheHashesFor($row);

        $siblings = $hashes === []
            ? collect()
            : SearchQuery::where('type', $row->type)
                ->whereIn('query_hash', $hashes)
                ->where('id', '!=', $row->id)
                ->get(['id', 'case_id']);

        return response()->json([
            'total_count' => $siblings->count() + 1,
            'sibling_count' => $siblings->count(),
            'case_count' => $siblings->pluck('case_id')
                ->push($row->case_id)
                ->filter()
                ->unique()
                ->count(),
        ]);
    }

    // ── Delete a Search Query & Purge its Cached Result ─────────────────────
    /**
     * Hard-deletes the row plus every sibling sharing its (type, cache hash),
     * so SearchCache::findHit() can no longer serve a stale body and the next
     * search of that value goes upstream again. Attached search_results rows
     * follow via the FK cascade.
     *
     * A search already in flight when this runs may still write its result
     * afterwards and become the new cache — that data came from a live
     * upstream call, so the guarantee holds.
     */
    public function deleteQuery(string $key)
    {
        $row = $this->resolveSearchQuery($key);
        $hashes = $this->cacheHashesFor($row);

        [$deletedCount, $affectedCaseIds] = DB::transaction(function () use ($row, $hashes) {
            $targets = SearchQuery::query()
                ->when(
                    $hashes === [],
                    fn ($q) => $q->where('id', $row->id),
                    fn ($q) => $q->where('type', $row->type)->whereIn('query_hash', $hashes),
                )
                ->lockForUpdate()
                ->get(['id', 'case_id']);

            $ids = $targets->pluck('id')->all();
            $caseIds = $targets->pluck('case_id')->filter()->unique()->values()->all();

            // The target row is only in $targets when its own hash matched;
            // make sure it always goes.
            if (! in_array($row->id, $ids, true)) {
                $ids[] = $row->id;
            }

            SearchQuery::whereIn('id', $ids)->delete();

            return [count($ids), $caseIds];
        });

        $this->auditLog('SEARCH_QUERY_DELETED', [
            'target_id' => $row->id,
            'target_public_id' => $row->public_id,
            'type' => $row->type,
            // Truncated: query values are PII (phone / email / ID numbers).
            'query' => Str::limit((string) $row->query, 200),
            'owner_user_id' => $row->user_id,
            'case_id' => $row->case_id,
            'deleted_count' => $deletedCount,
            'affected_case_ids' => $affectedCaseIds,
        ]);

        return response()->json([
            'message' => 'Search query deleted. The next search of this value will fetch fresh data.',
            'deleted_count' => $deletedCount,
            'deleted_id' => $row->id,
            'deleted_public_id' => $row->public_id,
        ]);
    }

    // ── User's Search Queries ───────────────────────────────────────────────
    public function userQueries(Request $request, $id)
    {
        abort_unless(is_numeric($id), 400, 'Invalid user ID.');

        $request->validate([
            'type' => 'sometimes|in:'.implode(',', self::VALID_TYPES),
            'page' => 'sometimes|integer|min:1|max:1000',
        ]);

        // Ensure user exists
        User::findOrFail((int) $id);

        $query = SearchQuery::where('user_id', (int) $id)
            ->with('user:id,name,email')
            ->latest();

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        return response()->json($query->paginate(20));
    }

    // ── API Engines List ───────────────────────────────────────────────────
    public function apiEngines()
    {
        $engines = ApiEngine::orderBy('category')->orderBy('name')->get();

        return response()->json($engines);
    }

    // ── User's API Permissions ─────────────────────────────────────────────
    public function userPermissions($id)
    {
        abort_unless(is_numeric($id), 400, 'Invalid user ID.');

        $user = User::findOrFail((int) $id);

        if ($user->is_admin) {
            $allEngines = ApiEngine::all();

            return response()->json([
                'is_admin' => true,
                'permissions' => $allEngines->pluck('id')->toArray(),
            ]);
        }

        return response()->json([
            'is_admin' => false,
            'permissions' => $user->apiEngines->pluck('id')->toArray(),
        ]);
    }

    // ── Update User's API Permissions ───────────────────────────────────────
    public function updateUserPermissions(Request $request, $id)
    {
        abort_unless(is_numeric($id), 400, 'Invalid user ID.');

        $user = User::findOrFail((int) $id);

        if ($user->is_admin) {
            return response()->json(['error' => 'Admin users have full access to all APIs.'], 422);
        }

        $validated = $request->validate([
            'api_engine_ids' => 'required|array',
            'api_engine_ids.*' => 'integer|exists:api_engines,id',
        ]);

        $user->apiEngines()->sync($validated['api_engine_ids']);

        $this->auditLog('USER_PERMISSIONS_UPDATED', [
            'target_id' => $user->id,
            'target_email' => $user->email,
            'api_count' => count($validated['api_engine_ids']),
        ]);

        return response()->json(['message' => 'Permissions updated.']);
    }

    // ── Application Logs ────────────────────────────────────────────────────
    private const LOG_LEVELS = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

    /**
     * Parse storage/logs/laravel.log and return entries (newest first).
     * Query params: level (default "error" = error and above), search, page, per_page.
     */
    public function logs(Request $request)
    {
        $level = strtolower($request->query('level', 'error'));
        abort_unless(in_array($level, ['all', ...self::LOG_LEVELS]), 400, 'Invalid level.');
        $search = trim((string) $request->query('search', ''));
        $perPage = min(max((int) $request->query('per_page', 50), 1), 200);
        $page = max((int) $request->query('page', 1), 1);

        $path = storage_path('logs/laravel.log');
        if (!is_file($path)) {
            return response()->json(['data' => [], 'total' => 0, 'page' => 1, 'last_page' => 1, 'file_size' => 0]);
        }

        // Only read the tail of very large files to keep this fast.
        $maxBytes = 5 * 1024 * 1024;
        $size = filesize($path);
        $fh = fopen($path, 'r');
        if ($size > $maxBytes) {
            fseek($fh, -$maxBytes, SEEK_END);
            fgets($fh); // drop partial line
        }
        $content = stream_get_contents($fh);
        fclose($fh);

        // Split into entries: each starts with "[YYYY-MM-DD HH:MM:SS] env.LEVEL:"
        $chunks = preg_split('/^(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \w+\.\w+:)/m', $content, -1, PREG_SPLIT_NO_EMPTY);

        $allowed = $level === 'all'
            ? self::LOG_LEVELS
            : array_slice(self::LOG_LEVELS, 0, array_search($level, self::LOG_LEVELS) + 1);

        $entries = [];
        foreach ($chunks as $chunk) {
            if (!preg_match('/^\[([^\]]+)\] (\w+)\.(\w+): (.*)$/s', rtrim($chunk), $m)) {
                continue;
            }
            $entryLevel = strtolower($m[3]);
            if (!in_array($entryLevel, $allowed)) {
                continue;
            }
            $body = $m[4];
            if ($search !== '' && stripos($body, $search) === false) {
                continue;
            }
            $lines = explode("\n", $body, 2);
            $entries[] = [
                'timestamp' => $m[1],
                'env' => $m[2],
                'level' => $entryLevel,
                'message' => Str::limit(trim($lines[0]), 500),
                'context' => isset($lines[1]) ? Str::limit(trim($lines[1]), 5000) : null,
            ];
        }

        $entries = array_reverse($entries);
        $total = count($entries);
        $lastPage = max((int) ceil($total / $perPage), 1);
        $data = array_slice($entries, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
            'file_size' => $size,
            'truncated' => $size > $maxBytes,
        ]);
    }

    public function clearLogs()
    {
        $path = storage_path('logs/laravel.log');
        if (is_file($path)) {
            file_put_contents($path, '');
        }
        $this->auditLog('LOGS_CLEARED');
        return response()->json(['message' => 'Log file cleared.']);
    }
}
