<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            $teams = Team::with(['supervisor', 'members'])->get();
        } elseif ($user->cms_role === 'supervisor' && $user->team_id) {
            $teams = Team::with(['supervisor', 'members'])
                ->where('id', $user->team_id)
                ->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($teams);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();

        if ($user->cms_role !== 'supervisor' && ! $user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->cms_role === 'supervisor' && ! $user->is_admin) {
            $existingTeam = Team::where('supervisor_id', $user->id)->first();
            if ($existingTeam) {
                return response()->json(['message' => 'You already have a team'], 403);
            }
        }

        $team = Team::create([
            'name' => $request->name,
            'supervisor_id' => $request->supervisor_id,
        ]);

        $team->load(['supervisor', 'members']);

        return response()->json([
            'message' => 'Team created successfully',
            'team' => $team,
        ], 201);
    }

    public function show(Request $request, Team $team): JsonResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            $team->load(['supervisor', 'members']);

            return response()->json($team);
        }

        if ($user->cms_role === 'supervisor' && $user->team_id === $team->id) {
            $team->load(['supervisor', 'members']);

            return response()->json($team);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'supervisor_id' => 'sometimes|exists:users,id',
        ]);

        $user = $request->user();

        if ($user->cms_role !== 'supervisor' && ! $user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->cms_role === 'supervisor' && $user->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team->update($request->only(['name', 'supervisor_id']));
        $team->load(['supervisor', 'members']);

        return response()->json([
            'message' => 'Team updated successfully',
            'team' => $team,
        ]);
    }

    public function destroy(Request $request, Team $team): JsonResponse
    {
        $user = $request->user();

        if (! $user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team->delete();

        return response()->json(['message' => 'Team deleted successfully']);
    }

    public function addMember(Request $request, Team $team): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();

        if ($user->cms_role !== 'supervisor' && ! $user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->cms_role === 'supervisor' && $user->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $member = User::find($request->user_id);
        $member->team_id = $team->id;
        $member->save();

        $team->load(['supervisor', 'members']);

        return response()->json([
            'message' => 'Member added successfully',
            'team' => $team,
        ]);
    }

    public function removeMember(Request $request, Team $team, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->cms_role !== 'supervisor' && ! $currentUser->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($currentUser->cms_role === 'supervisor' && $currentUser->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->team_id !== $team->id) {
            return response()->json(['message' => 'User is not a member of this team'], 400);
        }

        if ($user->id === $team->supervisor_id) {
            return response()->json(['message' => 'Cannot remove team supervisor'], 400);
        }

        $user->team_id = null;
        $user->save();

        $team->load(['supervisor', 'members']);

        return response()->json([
            'message' => 'Member removed successfully',
            'team' => $team,
        ]);
    }
}
