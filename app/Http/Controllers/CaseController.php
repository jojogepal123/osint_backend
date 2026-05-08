<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    private function getCasesForUser(User $user)
    {
        if ($user->is_admin) {
            return CaseModel::with(['user', 'assignedUser', 'team']);
        }

        if ($user->cms_role === 'supervisor' && $user->team_id) {
            return CaseModel::with(['user', 'assignedUser', 'team'])
                ->where('team_id', $user->team_id);
        }

        return CaseModel::with(['user', 'assignedUser', 'team'])
            ->where('user_id', $user->id);
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

        if ($user->cms_role !== 'supervisor' && ! $user->is_admin) {
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
            'assigned_to' => $request->assigned_to,
        ]);

        $case->load(['user', 'assignedUser', 'team']);

        return response()->json([
            'message' => 'Case created successfully',
            'case' => $case,
        ], 201);
    }

    public function show(Request $request, CaseModel $case)
    {
        $user = $request->user();

        if ($user->is_admin) {
            $case->load(['user', 'assignedUser', 'team']);

            return response()->json($case);
        }

        if ($user->cms_role === 'supervisor' && $user->team_id && $case->team_id === $user->team_id) {
            $case->load(['user', 'assignedUser', 'team']);

            return response()->json($case);
        }

        if ($case->user_id === $user->id || $case->assigned_to === $user->id) {
            $case->load(['user', 'assignedUser', 'team']);

            return response()->json($case);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function update(UpdateCaseRequest $request, CaseModel $case)
    {
        $user = $request->user();

        if ($user->cms_role !== 'supervisor' && ! $user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->cms_role === 'supervisor' && $user->team_id && $case->team_id !== $user->team_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $case->update($request->validated());
        $case->load(['user', 'assignedUser', 'team']);

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

        if ($case->user_id !== $user->id && $case->assigned_to !== $user->id && ! $user->is_admin) {
            if ($user->cms_role === 'supervisor' && $user->team_id && $case->team_id !== $user->team_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $case->status = $request->status;

        if ($request->status === 'resolved') {
            $case->resolved_at = now();
        } elseif ($request->status === 'closed') {
            $case->closed_at = now();
        }

        $case->save();
        $case->load(['user', 'assignedUser', 'team']);

        return response()->json([
            'message' => 'Case status updated',
            'case' => $case,
        ]);
    }

    public function assign(Request $request, CaseModel $case)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        $user = $request->user();

        if ($user->cms_role !== 'supervisor' && ! $user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->cms_role === 'supervisor' && $user->team_id) {
            if ($case->team_id !== $user->team_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $case->assigned_to = $request->assigned_to;
        if ($request->has('team_id')) {
            $case->team_id = $request->team_id;
        }
        $case->save();
        $case->load(['user', 'assignedUser', 'team']);

        return response()->json([
            'message' => 'Case assigned successfully',
            'case' => $case,
        ]);
    }

    public function destroy(Request $request, CaseModel $case)
    {
        $user = $request->user();

        if ($user->cms_role !== 'supervisor' && ! $user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->cms_role === 'supervisor' && $user->team_id && $case->team_id !== $user->team_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $case->delete();

        return response()->json(['message' => 'Case deleted successfully']);
    }
}
