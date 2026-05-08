<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getActiveCase(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('activeCase');

        return response()->json([
            'active_case' => $user->activeCase,
        ]);
    }

    public function setActiveCase(Request $request): JsonResponse
    {
        $request->validate([
            'case_id' => 'required|exists:cases,id',
        ]);

        $user = $request->user();

        $case = CaseModel::find($request->case_id);

        if (! $case) {
            return response()->json(['message' => 'Case not found'], 404);
        }

        if ($user->is_admin) {
            $user->active_case_id = $request->case_id;
            $user->save();
            $case->load(['user', 'assignedUser', 'team']);

            return response()->json([
                'message' => 'Active case set successfully',
                'active_case' => $case,
            ]);
        }

        if ($user->cms_role === 'supervisor' && $user->team_id) {
            if ($case->team_id !== $user->team_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        if ($case->user_id !== $user->id && $case->assigned_to !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->active_case_id = $request->case_id;
        $user->save();
        $case->load(['user', 'assignedUser', 'team']);

        return response()->json([
            'message' => 'Active case set successfully',
            'active_case' => $case,
        ]);
    }

    public function clearActiveCase(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->active_case_id = null;
        $user->save();

        return response()->json([
            'message' => 'Active case cleared',
        ]);
    }

    public function updateCmsRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'cms_role' => 'required|in:auditor,supervisor',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $currentUser = $request->user();

        if (! $currentUser->is_admin && $currentUser->cms_role !== 'supervisor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $targetUser = \App\Models\User::find($request->user_id);

        if ($currentUser->is_admin) {
            $targetUser->cms_role = $request->cms_role;
            if ($request->has('team_id')) {
                $targetUser->team_id = $request->team_id;
            }
            $targetUser->save();

            return response()->json([
                'message' => 'User CMS role updated successfully',
                'user' => $targetUser,
            ]);
        }

        if ($currentUser->cms_role === 'supervisor' && $currentUser->team_id) {
            if ($request->cms_role === 'supervisor') {
                return response()->json(['message' => 'Cannot assign supervisor role'], 403);
            }
            $targetUser->cms_role = $request->cms_role;
            $targetUser->team_id = $currentUser->team_id;
            $targetUser->save();

            return response()->json([
                'message' => 'User CMS role updated successfully',
                'user' => $targetUser,
            ]);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
