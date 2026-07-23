<?php

namespace App\Http\Controllers;

use App\Models\ApiEngine;
use App\Models\SearchQuery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            'type' => 'sometimes|in:'.implode(',', self::VALID_TYPES),
            'user_id' => 'sometimes|integer|min:1',
            'page' => 'sometimes|integer|min:1|max:1000',
        ]);

        $query = SearchQuery::with(['user:id,name,email', 'result'])->latest();

        if ($search = $request->query('search')) {
            $query->where('query', 'like', "%{$search}%");
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type); // already whitelisted above
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', (int) $userId);
        }

        return response()->json($query->paginate(20));
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
}
