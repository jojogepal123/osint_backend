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
            $case->load(['user', 'assignedUsers']);

            return response()->json([
                'message' => 'Active case set successfully',
                'active_case' => $case,
            ]);
        }

        $isOwner = $case->user_id === $user->id;
        $isAssigned = $case->assignedUsers()->where('user_id', $user->id)->exists();

        if (! $isOwner && ! $isAssigned) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->active_case_id = $request->case_id;
        $user->save();
        $case->load(['user', 'assignedUsers']);

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
            'cms_role' => 'required|in:auditor,supervisor,investigator',
        ]);

        $currentUser = $request->user();

        if (! $currentUser->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $targetUser = \App\Models\User::find($request->user_id);
        $targetUser->cms_role = $request->cms_role;
        $targetUser->save();

        return response()->json([
            'message' => 'User CMS role updated successfully',
            'user' => $targetUser,
        ]);
    }
}
