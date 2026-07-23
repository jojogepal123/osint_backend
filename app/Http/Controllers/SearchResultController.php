<?php

namespace App\Http\Controllers;

use App\Models\SearchQuery;
use App\Models\SearchResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SearchResultController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $searchQueries = SearchQuery::with(['user', 'result'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($searchQueries);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:tel,email,vehicle,upi,challan,leak,corporate,verification,social'],
            'user_input' => ['required', 'string'],
            'results' => ['required', 'array'],
            'search_query_id' => ['sometimes', 'integer', 'exists:search_queries,id'],
        ]);

        $user = $request->user();

        if (! empty($validated['search_query_id'])) {
            $searchQuery = SearchQuery::find($validated['search_query_id']);

            if (! $searchQuery) {
                return response()->json(['error' => 'Search query not found'], 404);
            }

            if (! $this->canAccessSearchQuery($user, $searchQuery)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $result = $searchQuery->result;

            if ($result) {
                $result->update(['results' => $validated['results']]);
            } else {
                $result = SearchResult::create([
                    'search_query_id' => $searchQuery->id,
                    'results' => $validated['results'],
                ]);
            }

            return response()->json([
                'message' => 'Results updated',
                'search_result_id' => $result->id,
            ]);
        }

        $searchQuery = SearchQuery::create([
            'user_id' => $user->id,
            'query' => $validated['user_input'],
            'type' => $validated['type'],
            'ip_address' => $request->ip(),
            'case_id' => $user->active_case_id ?? null,
        ]);

        $result = SearchResult::create([
            'search_query_id' => $searchQuery->id,
            'results' => $validated['results'],
        ]);

        return response()->json([
            'message' => 'Results saved',
            'search_query_id' => $searchQuery->id,
            'search_result_id' => $result->id,
        ], 201);
    }

    public function show(Request $request, SearchQuery $searchQuery): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $searchQuery->load(['user', 'result']);

        if (! $this->canAccessSearchQuery($user, $searchQuery)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $searchQuery->result;

        if (! $result) {
            return response()->json(['error' => 'Result not found'], 404);
        }

        return response()->json([
            'search_query' => $searchQuery,
            'search_type' => $searchQuery->type,
            'results' => $result->results,
            'user_email' => $user->email,
        ]);
    }

    public function download(Request $request, SearchQuery $searchQuery)
    {
        $user = $request->user();

        $searchQuery->load(['user', 'result']);

        if (! $searchQuery) {
            return response()->json(['error' => 'Search query not found'], 404);
        }

        if (! $this->canAccessSearchQuery($user, $searchQuery)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $searchQuery->result;

        if (! $result) {
            return response()->json(['error' => 'Result not found'], 404);
        }

        try {
            $data = $result->results;
            if (isset($data['data']) && is_array($data['data'])) {
                $data = $data['data'];
            }
            $userEmail = $user->email;
            $userInput = $searchQuery->query;
            $type = $searchQuery->type;

            if ($type === 'tel') {
                $html = View::make('report.tel_template', compact('data', 'userEmail', 'userInput'))->render();
            } elseif ($type === 'email') {
                $html = View::make('report.email_template', compact('data', 'userEmail', 'userInput'))->render();
            } elseif ($type === 'vehicle') {
                $html = View::make('report.rc_template', compact('data', 'userEmail'))->render();
            } elseif ($type === 'upi') {
                $html = View::make('report.upi_template', compact('data', 'userEmail'))->render();
            } elseif ($type === 'challan') {
                $html = View::make('report.challan_template', compact('data', 'userEmail'))->render();
            } elseif ($type === 'verification') {
                $html = View::make('report.verification_template', [
                    'data' => $data,
                    'title' => $type,
                    'searchInput' => $userInput,
                    'userEmail' => $userEmail,
                ])->render();
            } elseif ($type === 'social') {
                $photoUrl = $data['photo']['url'] ?? $data['photoUrl'] ?? null;
                if ($photoUrl) {
                    $data['_photoBase64'] = $this->getImageBase64($photoUrl);
                }
                $html = View::make('report.social_template', compact('data', 'userEmail'))->render();
            } else {
                return response()->json(['error' => 'Unsupported search type for download'], 422);
            }

            $pdf = Pdf::loadHTML($html);
            $filename = $type.'_'.Str::slug($userInput).'_'.now()->format('Ymd_His').'.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('PDF generation failed', [
                'search_query_id' => $searchQueryId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to generate PDF'], 500);
        }
    }

    private function canAccessSearchQuery($user, SearchQuery $searchQuery): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($searchQuery->user_id === $user->id) {
            return true;
        }

        if ($searchQuery->case_id) {
            $case = $searchQuery->case;
            if ($case && $case->assignedUsers()->where('user_id', $user->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    private function getImageBase64($url): ?string
    {
        try {
            if (Str::startsWith($url, '/')) {
                $url = rtrim(config('app.url'), '/').$url;
            }

            $imgData = @file_get_contents($url);
            if ($imgData === false) {
                return null;
            }

            $type = pathinfo($url, PATHINFO_EXTENSION);

            return 'data:image/'.$type.';base64,'.base64_encode($imgData);
        } catch (Exception $e) {
            return null;
        }
    }
}
