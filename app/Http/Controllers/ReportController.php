<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    private function getImageBase64($url)
    {
        try {
            $imgData = $this->fetchUrl($url);
            if ($imgData === false || $imgData === null) {
                return null;
            }

            $type = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $supported = ['jpg', 'jpeg', 'png', 'gif'];

            // Detect WebP either by URL extension or by binary magic bytes
            // (RIFF????WEBP). dompdf's imagecreatefromwebp() requires a GD
            // build with WebP support, which is not always available. Rather
            // than let dompdf crash on a missing extension, skip the photo
            // entirely when it's WebP — the template falls back to initials.
            $isWebp =
                $type === 'webp' ||
                (strlen($imgData) >= 12 &&
                    substr($imgData, 0, 4) === 'RIFF' &&
                    substr($imgData, 8, 4) === 'WEBP');

            if ($isWebp) {
                return null;
            }

            if (in_array($type, $supported, true)) {
                return 'data:image/'.$type.';base64,'.base64_encode($imgData);
            }

            if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
                return null;
            }

            $src = @imagecreatefromstring($imgData);
            if ($src === false) {
                return null;
            }

            ob_start();
            imagejpeg($src, null, 85);
            $jpgData = ob_get_clean();
            imagedestroy($src);

            return 'data:image/jpeg;base64,'.base64_encode($jpgData);
        } catch (Exception $e) {
            return null;
        }
    }

    private function fetchUrl($url)
    {
        $data = @file_get_contents($url);
        if ($data !== false) {
            return $data;
        }

        if (! function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0',
        ]);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($code >= 200 && $code < 300) ? $data : false;
    }

    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:tel,email'],
            'userInput' => ['required', 'string'],
            'results' => ['required', 'array'],
        ]);

        $data = $validated['results'];
        $type = $validated['type'];
        $userInput = $validated['userInput'];

        $userEmail = Auth::check() ? Auth::user()->email : 'N/A';

        $filename = 'report_'.now()->format('Ymd_His').'_'.Str::uuid().'.pdf';

        $profileImages = data_get($data, 'profile.profileImages', []);

        if (is_array($profileImages)) {

            foreach ($profileImages as $key => $img) {

                $source = $img['source'] ?? 'Unknown';
                $url = $img['value'] ?? null;

                if (! $url) {
                    continue;
                }

                // Fix Telegram relative path
                if ($source === 'Telegram' && Str::startsWith($url, '/telegram_photos/')) {
                    $url = rtrim(env('FRONTEND_URL'), '/').$url;
                }

                if (! empty($img['base64'])) {
                    $data['profile']['profileImages'][$key]['base64'] = $img['base64'];

                    continue;
                }

                $base64 = $this->getImageBase64($url);

                if ($base64) {
                    $data['profile']['profileImages'][$key]['base64'] = $base64;
                }
            }
        }

        // Render view based on type
        if ($type === 'tel') {
            $html = View::make('report.tel_template', compact('data', 'userEmail'))->render();
        } elseif ($type === 'email') {
            if (! empty($data['breachData'])) {
                foreach ($data['breachData'] as $key => $value) {
                    if (! empty($value['LogoPath'])) {
                        $data['breachData'][$key]['LogoBase64'] = $this->getImageBase64($value['LogoPath']);
                    }
                }
            }
            if (! empty($data['gravatar']) && is_array($data['gravatar'])) {
                foreach ($data['gravatar'] as $key => $item) {
                    if (! empty($item['avatar_url'])) {
                        $data['gravatar'][$key]['avatar_base64'] = $this->getImageBase64($item['avatar_url']);
                    }
                }
            }
            $html = View::make('report.email_template', compact('data', 'userEmail'))->render();
        } else {
            Log::error("Invalid report type: $type");

            return response()->json(['error' => 'Invalid type'], 422);
        }

        try {
            $pdfContent = Pdf::loadHTML($html)->output();

            return response()->streamDownload(
                fn () => print ($pdfContent),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (Exception $e) {
            Log::error('PDF generation failed: '.$e->getMessage());

            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }

    public function generateAiReport(Request $request)
    {
        $validated = $request->validate([
            'userInput' => 'required|string',
            'type' => 'required|in:tel,email',
            'results' => 'required|array',
        ]);

        $userInput = $validated['userInput'];
        $type = $validated['type'];
        $results = $validated['results'];

        $userEmail = Auth::check() ? Auth::user()->email : 'N/A';

        $profileImages = data_get($results, 'profile.profileImages', []);
        $profileImagesForPdf = [];

        if (is_array($profileImages)) {
            foreach ($profileImages as $img) {

                $platform = $img['source'] ?? 'Unknown';
                $url = $img['value'] ?? null;

                if (! $url) {
                    continue;
                }

                if (Str::startsWith($url, '/')) {
                    $url = rtrim(config('app.furl'), '/').$url;
                }
                $base64 = $this->getImageBase64($url);

                if ($base64) {
                    $profileImagesForPdf[] = [
                        'platform' => $platform,
                        'url' => $url,
                        'base64' => $base64,
                    ];
                }
            }
        }

        $prettyResults = json_encode($results, JSON_PRETTY_PRINT);

        $prompt = <<<EOT
        You are a senior OSINT (Open Source Intelligence) analyst.

        the following JSON contains structured profile data collected through various public sources, including phones, emails, usernames, social media presence, locations, carriers, upi deatails,bank details, rc number, data breaches, online presence, imsi number, phone status, leaked databases, public locations and reviews and other identifiers. also use sources which are mentioned in json data.

        Include:
        - "intelligenceSummary": A full natural language summary of who this person is.
        - "riskLevel": Low | Medium | High, with a full explaination and justification.
        - "nextSteps": List of things an analyst should do next.
        - "profileHighlights": Bullet points with full name, emails, phones, location, public locations and reviews etc.
        - "confidenceScore": Score from 0 to 100 showing how reliable this data looks.
        - "anomalies": Any suspicious things (e.g. duplicate emails, mismatched names, missing fields).
        - "socialPresenceSummary": Summary of detected presence on WhatsApp, Facebook, etc.
        - "dataFreshness": "Active", "Outdated", or "Unknown", based on fields like last updated.

        Respond in this JSON format:

        {
        "intelligenceSummary": "...",
        "riskLevel": "...",
        "nextSteps": [...],
        "profileHighlights": [...],
        "confidenceScore": 0–100,
        "anomalies": [...],
        "socialPresenceSummary": "...",
        "dataFreshness": "...",
        }

        --- Begin Data ---
        {$prettyResults}
        --- End Data ---
        EOT;

        // Step 3: Call VPS AI API
        $response = Http::timeout(60)
            ->retry(3, 500)
            ->withHeaders([
                'x-api-key' => env('AI_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->post('https://intelltraceai.tech/v1/generate', [
                'prompt' => $prompt,
            ]);

        if (! $response->successful()) {
            Log::error('VPS AI request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json(['error' => 'AI failed to generate report.'], 500);
        }

        $responseJson = $response->json();
        $rawText = $responseJson['response']
            ?? $responseJson['text']
            ?? $responseJson['content']
            ?? $responseJson['output']
            ?? $response->body()
            ?? '{}';

        // Clean out markdown backticks (```json ... ```)
        $cleanJson = trim($rawText);
        $cleanJson = preg_replace('/^```json\s*/', '', $cleanJson); // remove start ```json
        $cleanJson = preg_replace('/```$/', '', $cleanJson);        // remove end ```

        // Now decode the clean JSON
        $responseData = json_decode($cleanJson, true);
        // Fallback-safe values
        $summary = $responseData['intelligenceSummary'] ?? 'No summary generated.';
        $riskLevel = $responseData['riskLevel'] ?? 'Unknown';
        $nextSteps = $responseData['nextSteps'] ?? [];
        $profileHighlights = $responseData['profileHighlights'] ?? [];
        $confidenceScore = $responseData['confidenceScore'] ?? null;
        $anomalies = $responseData['anomalies'] ?? [];
        $socialPresenceSummary = $responseData['socialPresenceSummary'] ?? null;
        $dataFreshness = $responseData['dataFreshness'] ?? null;

        $template = $type === 'tel' ? 'report.ai-tel_template' : 'report.ai-email_template';
        // Step 5: Render PDF with full results + Gemini summary
        $pdf = PDF::loadView($template, [
            'summary' => $summary,
            'riskLevel' => $riskLevel,
            'nextSteps' => $nextSteps,
            'profileHighlights' => $profileHighlights,
            'confidenceScore' => $confidenceScore,
            'anomalies' => $anomalies,
            'socialPresenceSummary' => $socialPresenceSummary,
            'dataFreshness' => $dataFreshness,
            'userInput' => $userInput,
            'generation_time' => now()->format('Y-m-d H:i:s'),
            'type' => $type,
            'results' => $results,
            'userEmail' => $userEmail, // full original results
            'profileImagesForPdf' => $profileImagesForPdf,
        ]);

        // Log::info("PDF Images", $profileImagesForPdf);

        $filename = 'ai-report-'.Str::slug($userInput).'.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    public function generateUpiReport(Request $request)
    {
        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $userEmail = Auth::check() ? Auth::user()->email : 'N/A';

        $data = array_filter($validated['data'], function ($value, $key) {
            $key = strtolower($key);
            if (in_array($key, ['client_id', 'clientid'])) {
                return false;
            }
            if (is_null($value) || $value === '') {
                return false;
            }
            if (! is_scalar($value)) {
                return true;
            }
            $v = strtolower(trim((string) $value));

            return ! in_array($v, ['n/a', 'na', 'n.a']);
        }, ARRAY_FILTER_USE_BOTH);

        $filename = 'upi_details_'.now()->format('Ymd_His').'_'.Str::uuid().'.pdf';

        try {
            $html = View::make('report.upi_template', [
                'data' => $data,
                'userEmail' => $userEmail,
            ])->render();
            $pdfContent = Pdf::loadHTML($html)->output();

            return response()->streamDownload(
                fn () => print ($pdfContent),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (Exception $e) {
            Log::error('UPI PDF generation error: '.$e->getMessage());

            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }

    public function generateRcReport(Request $request)
    {
        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);
        $userEmail = Auth::check() ? Auth::user()->email : 'N/A';

        $data = array_filter($validated['data'], function ($value, $key) {
            $key = strtolower($key);
            if (in_array($key, ['client_id', 'clientid'])) {
                return false;
            }
            if (is_null($value) || $value === '') {
                return false;
            }
            if (! is_scalar($value)) {
                return true;
            } // keep arrays/objects as-is
            $v = strtolower(trim((string) $value));

            return ! in_array($v, ['n/a', 'na', 'n.a']);
        }, ARRAY_FILTER_USE_BOTH);

        $filename = 'rc_details_'.now()->format('Ymd_His').'_'.Str::uuid().'.pdf';

        try {
            $html = View::make('report.rc_template', [
                'data' => $data,
                'userEmail' => $userEmail,
            ])->render();
            $pdfContent = Pdf::loadHTML($html)->output();

            return response()->streamDownload(
                fn () => print ($pdfContent),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (Exception $e) {
            Log::error('RC PDF generation error: '.$e->getMessage());

            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }

    public function generateChallanReport(Request $request)
    {

        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $userEmail = Auth::check() ? Auth::user()->email : 'N/A';

        // Fix: Properly handle nested arrays like challan_details
        $data = array_filter($validated['data'], function ($value, $key) {
            $key = strtolower($key);
            if (in_array($key, ['client_id', 'clientid'])) {
                return false;
            }
            if (is_null($value) || $value === '') {
                return false;
            }
            if (is_scalar($value)) {
                $v = strtolower(trim((string) $value));

                return ! in_array($v, ['n/a', 'na', 'n.a']);
            }

            return true; // Keep arrays like challan_details
        }, ARRAY_FILTER_USE_BOTH);

        $filename = 'challan_details_'.now()->format('Ymd_His').'_'.Str::uuid().'.pdf';

        try {
            $html = View::make('report.challan_template', [
                'data' => $data,
                'userEmail' => $userEmail,
            ])->render();
            $pdfContent = Pdf::loadHTML($html)->output();

            return response()->streamDownload(
                fn () => print ($pdfContent),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (Exception $e) {
            Log::error('Challan PDF generation error: '.$e->getMessage());

            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }

    public function generateVerificationReport(Request $request)
    {
        $validated = $request->validate([
            'data' => ['required', 'array'],
            'title' => ['sometimes', 'string'],
            'searchInput' => ['sometimes', 'string'],
        ]);

        $userEmail = Auth::check() ? Auth::user()->email : 'N/A';
        $title = $validated['title'] ?? 'Verification';
        $searchInput = $validated['searchInput'] ?? '';

        $data = array_filter($validated['data'], function ($value, $key) {
            if (in_array(strtolower($key), ['client_id', 'clientid', 'verification_id'])) {
                return false;
            }
            if (is_null($value) || $value === '') {
                return false;
            }
            if (is_scalar($value)) {
                $v = strtolower(trim((string) $value));

                return ! in_array($v, ['n/a', 'na', 'n.a']);
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);

        $filename = 'verification_'.now()->format('Ymd_His').'_'.Str::uuid().'.pdf';

        try {
            $html = View::make('report.verification_template', [
                'data' => $data,
                'title' => $title,
                'searchInput' => $searchInput,
                'userEmail' => $userEmail,
            ])->render();
            $pdfContent = Pdf::loadHTML($html)->output();

            return response()->streamDownload(
                fn () => print ($pdfContent),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (Exception $e) {
            Log::error('Verification PDF generation error: '.$e->getMessage());

            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }

    public function generateSocialReport(Request $request)
    {
        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $userEmail = Auth::check() ? Auth::user()->email : 'N/A';
        $data = $validated['data'];

        // Convert photo URL to base64
        $photoUrl = data_get($data, 'photo.url') ?? ($data['photoUrl'] ?? null);
        if ($photoUrl) {
            $base64 = $this->getImageBase64($photoUrl);
            if ($base64) {
                $data['_photoBase64'] = $base64;
            }
        }

        // The frontend may also pre-embed `_photoBase64` (e.g. from
        // SocialCandidateCard's profile-modal Save-as-pdf flow). If that data
        // URL is WebP, dompdf's imagecreatefromwebp() crashes because PHP's GD
        // is not built with WebP support on production. Detect WebP via the
        // data-URL header or the binary magic bytes (RIFF????WEBP) and drop
        // it so the template falls back to the candidate's initials avatar.
        if (! empty($data['_photoBase64']) && is_string($data['_photoBase64'])) {
            $pb = $data['_photoBase64'];
            $isWebpDataUrl = str_starts_with($pb, 'data:image/webp');
            if (! $isWebpDataUrl && str_starts_with($pb, 'data:')) {
                $comma = strpos($pb, ',');
                if ($comma !== false) {
                    $raw = base64_decode(substr($pb, $comma + 1), true);
                    if (is_string($raw) && strlen($raw) >= 12
                        && substr($raw, 0, 4) === 'RIFF'
                        && substr($raw, 8, 4) === 'WEBP') {
                        $isWebpDataUrl = true;
                    }
                }
            }
            if ($isWebpDataUrl) {
                unset($data['_photoBase64']);
            }
        }

        $filename = 'social_intel_'.Str::slug($data['fullName'] ?? 'profile').'_'.now()->format('Ymd_His').'.pdf';

        try {
            $html = View::make('report.social_template', [
                'data' => $data,
                'userEmail' => $userEmail,
            ])->render();
            $pdfContent = Pdf::loadHTML($html)->output();

            return response()->streamDownload(
                fn () => print ($pdfContent),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (Exception $e) {
            Log::error('Social Intel PDF error: '.$e->getMessage());

            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }
}
