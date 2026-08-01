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

            if ($type === 'tel' || $type === 'phone') {
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
                // Drop WebP data URLs: dompdf's imagecreatefromwebp() requires
                // a GD build with WebP support, which is not always available.
                $this->stripWebpPhoto($data);
                $html = View::make('report.social_template', compact('data', 'userEmail'))->render();
            } else {
                return response()->json([
                    'error' => 'PDF report not yet supported for this search type',
                    'type' => $type,
                    'supported_types' => ['tel', 'phone', 'email', 'vehicle', 'upi', 'challan', 'verification', 'social'],
                ], 501);
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
        // Cached search results are globally readable to any authenticated
        // user except auditor. Auditors are read-only at the OSINT layer and
        // shouldn't peek at saved investigations.
        return $user !== null && $user->cms_role !== 'auditor';
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

            $type = strtolower(pathinfo($url, PATHINFO_EXTENSION));

            // Skip WebP: dompdf's imagecreatefromwebp() requires a GD build
            // with WebP support, which is not always available. Returning null
            // makes the social template fall back to the candidate's initials.
            if (
                $type === 'webp' ||
                (strlen($imgData) >= 12 &&
                    substr($imgData, 0, 4) === 'RIFF' &&
                    substr($imgData, 8, 4) === 'WEBP')
            ) {
                return null;
            }

            return 'data:image/'.$type.';base64,'.base64_encode($imgData);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Drop `data['photo']['urlBase64']` and `data['_photoBase64']` whenever
     * they are WebP data URLs. dompdf's imagecreatefromwebp() requires a GD
     * build with WebP support, which is not always available. Letting the
     * template fall back to the candidate's initials avatar avoids the
     * "Function imagecreatefromwebp() not found" error.
     */
    private function stripWebpPhoto(array &$data): void
    {
        foreach (['_photoBase64', 'photo.urlBase64', 'photoUrl'] as $key) {
            $value = data_get($data, $key);
            if (! is_string($value) || $value === '') {
                continue;
            }
            $isWebpDataUrl = str_starts_with($value, 'data:image/webp');
            if (! $isWebpDataUrl && str_starts_with($value, 'data:')) {
                $comma = strpos($value, ',');
                if ($comma !== false) {
                    $raw = base64_decode(substr($value, $comma + 1), true);
                    if (is_string($raw) && strlen($raw) >= 12
                        && substr($raw, 0, 4) === 'RIFF'
                        && substr($raw, 8, 4) === 'WEBP') {
                        $isWebpDataUrl = true;
                    }
                }
            }
            if ($isWebpDataUrl) {
                data_set($data, $key, null);
            }
        }
    }
}
