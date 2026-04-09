<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SearchQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ── Stats Overview ──────────────────────────────────────────────────────
    public function stats()
    {
        return response()->json([
            'total_users'   => User::count(),
            'admin_users'   => User::where('is_admin', true)->count(),
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
        $query = User::withCount('searchQueries')
            ->with('ips');

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

        return response()->json($users);
    }

    // ── Single User ─────────────────────────────────────────────────────────
    public function user($id)
    {
        $user = User::withCount('searchQueries')
            ->with('ips')
            ->findOrFail($id);

        $queriesByType = SearchQuery::where('user_id', $id)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return response()->json([
            'user'            => $user,
            'queries_by_type' => $queriesByType,
        ]);
    }

    // ── Create User ─────────────────────────────────────────────────────────
    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'credits'  => 'sometimes|numeric|min:0',
            'app_mode' => 'sometimes|in:trial,live',
            'is_admin' => 'sometimes|boolean',
        ]);

        $validated['password']        = bcrypt($validated['password']);
        $validated['email_verified_at'] = now();
        $validated['credits']         = $validated['credits'] ?? 0;
        $validated['app_mode']        = $validated['app_mode'] ?? 'trial';

        $user = User::create($validated);

        return response()->json(['message' => 'User created.', 'user' => $user], 201);
    }

    // ── Update User ─────────────────────────────────────────────────────────
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'credits'  => 'sometimes|numeric|min:0',
            'app_mode' => 'sometimes|in:trial,live',
            'is_admin' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json(['message' => 'User updated.', 'user' => $user]);
    }

    // ── Delete User ─────────────────────────────────────────────────────────
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    // ── Search Queries List ─────────────────────────────────────────────────
    public function queries(Request $request)
    {
        $query = SearchQuery::with('user:id,name,email')
            ->latest();

        if ($search = $request->query('search')) {
            $query->where('query', 'like', "%{$search}%");
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        $queries = $query->paginate(20);

        return response()->json($queries);
    }

    // ── User's Search Queries ───────────────────────────────────────────────
    public function userQueries(Request $request, $id)
    {
        $query = SearchQuery::where('user_id', $id)
            ->with('user:id,name,email')
            ->latest();

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $queries = $query->paginate(20);

        return response()->json($queries);
    }
}
