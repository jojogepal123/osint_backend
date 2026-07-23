<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Models\CaseActivity;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    private function getCasesForUser(User $user)
    {
        if ($user->is_admin) {
            return CaseModel::with(['user', 'assignedUsers']);
        }

        return CaseModel::with(['user', 'assignedUsers'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereRelation('assignedUsers', 'user_id', $user->id);
            });
    }

    private function isCaseMember(User $user, CaseModel $case): bool
    {
        if ($user->id === $case->user_id) {
            return true;
        }

        if ($user->is_admin) {
            return true;
        }

        return $case->assignedUsers()->where('user_id', $user->id)->exists();
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = $this->getCasesForUser($user);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('case_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $cases = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($cases);
    }

    public function store(StoreCaseRequest $request)
    {
        $user = $request->user();

        if (! $user->canCreateCase()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $lastCase = CaseModel::orderBy('id', 'desc')->first();
        $year = now()->format('Y');
        $sequence = $lastCase ? (int) substr($lastCase->case_number, -4) + 1 : 1;
        $caseNumber = sprintf('CASE-%s-%04d', $year, $sequence);

        $case = CaseModel::create([
            'user_id' => $user->id,
            'case_number' => $caseNumber,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'open',
            'priority' => $request->priority ?? 'medium',
            'category' => $request->category,
            'team_id' => $request->team_id,
            'assigned_to' => $request->assigned_to ?? null,
        ]);

        if ($request->has('assigned_to') && is_array($request->assigned_to)) {
            $filtered = array_filter($request->assigned_to, fn ($id) => (int) $id > 0);
            if (! empty($filtered)) {
                $case->assignedUsers()->sync(array_values($filtered));
            }
        }

        $case->load(['user', 'assignedUsers']);

        CaseActivity::log(
            $case->id,
            $user->id,
            'created',
            "Case created with title: {$case->title}",
            ['title' => $case->title, 'priority' => $case->priority]
        );

        return response()->json([
            'message' => 'Case created successfully',
            'case' => $case,
        ], 201);
    }

    public function show(Request $request, CaseModel $case)
    {
        $user = $request->user();

        if ($user->is_admin) {
            $case->load(['user', 'assignedUsers']);

            return response()->json($case);
        }

        if ($this->isCaseMember($user, $case)) {
            $case->load(['user', 'assignedUsers']);

            return response()->json($case);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function update(UpdateCaseRequest $request, CaseModel $case)
    {
        $user = $request->user();

        if (! $user->canEditCase()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $case->update($request->validated());
        $case->load(['user', 'assignedUsers']);

        CaseActivity::log(
            $case->id,
            $user->id,
            'updated',
            'Case details updated',
            ['changes' => $request->validated()]
        );

        return response()->json([
            'message' => 'Case updated successfully',
            'case' => $case,
        ]);
    }

    public function updateStatus(Request $request, CaseModel $case)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,pending,resolved,closed',
        ]);

        $user = $request->user();

        if (! $this->isCaseMember($user, $case)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $oldStatus = $case->status;
        $case->status = $request->status;

        if ($request->status === 'resolved') {
            $case->resolved_at = now();
        } elseif ($request->status === 'closed') {
            $case->closed_at = now();
        }

        $case->save();
        $case->load(['user', 'assignedUsers']);

        CaseActivity::log(
            $case->id,
            $user->id,
            'status_changed',
            "Status changed from {$oldStatus} to {$request->status}",
            ['old_status' => $oldStatus, 'new_status' => $request->status]
        );

        return response()->json([
            'message' => 'Case status updated',
            'case' => $case,
        ]);
    }

    public function assignUsers(Request $request, CaseModel $case)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $user = $request->user();

        if (! $user->canAssignCase()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $case->assignedUsers()->sync(array_values(array_filter($request->user_ids, fn ($id) => (int) $id > 0)));

        $case->load(['user', 'assignedUsers']);

        $assignedNames = $case->assignedUsers->pluck('name')->toArray();
        CaseActivity::log(
            $case->id,
            $user->id,
            'assigned',
            'Case assigned to: '.implode(', ', $assignedNames),
            ['user_ids' => $request->user_ids]
        );

        return response()->json([
            'message' => 'Case assigned successfully',
            'case' => $case,
        ]);
    }

    public function removeMember(Request $request, CaseModel $case, int $userId)
    {
        $user = $request->user();

        if (! $user->canAssignCase()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $case->assignedUsers()->detach($userId);
        $case->load(['user', 'assignedUsers']);

        CaseActivity::log(
            $case->id,
            $user->id,
            'unassigned',
            'User removed from case',
            ['removed_user_id' => $userId]
        );

        return response()->json([
            'message' => 'Member removed',
            'case' => $case,
        ]);
    }

    public function members(CaseModel $case)
    {
        return response()->json($case->assignedUsers);
    }

    public function assign(Request $request, CaseModel $case)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $user = $request->user();

        if (! $user->canAssignCase()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $case->assignedUsers()->sync([$request->assigned_to]);
        $case->assigned_to = $request->assigned_to;
        $case->save();
        $case->load(['user', 'assignedUsers']);

        $assignedUser = User::find($request->assigned_to);
        CaseActivity::log(
            $case->id,
            $user->id,
            'assigned',
            "Case assigned to {$assignedUser->name}",
            ['assigned_to' => $request->assigned_to]
        );

        return response()->json([
            'message' => 'Case assigned successfully',
            'case' => $case,
        ]);
    }

    public function destroy(Request $request, CaseModel $case)
    {
        $user = $request->user();

        if (! $user->canDeleteCase()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $case->delete();

        CaseActivity::log(
            $case->id,
            $user->id,
            'deleted',
            'Case deleted'
        );

        return response()->json(['message' => 'Case deleted successfully']);
    }

    public function activities(Request $request, CaseModel $case)
    {
        $user = $request->user();

        if ($user->is_admin) {
            $activities = $case->activities()->with('user')->orderBy('created_at', 'desc')->get();

            return response()->json($activities);
        }

        if ($this->isCaseMember($user, $case)) {
            $activities = $case->activities()->with('user')->orderBy('created_at', 'desc')->get();

            return response()->json($activities);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function searches(Request $request, CaseModel $case)
    {
        $user = $request->user();

        if ($user->is_admin) {
            return response()->json($case->searchQueries()->with(['user', 'result'])->orderBy('created_at', 'desc')->get());
        }

        if ($this->isCaseMember($user, $case)) {
            return response()->json($case->searchQueries()->with(['user', 'result'])->orderBy('created_at', 'desc')->get());
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
