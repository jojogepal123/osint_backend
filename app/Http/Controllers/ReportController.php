<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Validator;
class ReportController extends Controller
{
    private function getImageBase64($url)
    {
        try {
            $imgData = file_get_contents($url);
            $type = pathinfo($url, PATHINFO_EXTENSION);
            return 'data:image/' . $type . ';base64,' . base64_encode($imgData);
        } catch (Exception $e) {
            return null;
        }
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

        $filename = 'report_' . now()->format('Ymd_His') . '_' . Str::uuid() . '.pdf';
        $filePath = storage_path("app/private/reports/{$filename}");

        // Ensure directory exists
        Storage::makeDirectory('/reports');


        // Render view based on type
        if ($type === 'tel') {
            $html = View::make('report.tel_template', compact('data'))->render();
        } else if ($type === 'email') {
            if (!empty($data['breachData'])) {
                foreach ($data['breachData'] as $key => $value) {
                    if (!empty($value['LogoPath'])) {
                        $data['breachData'][$key]['LogoBase64'] = $this->getImageBase64($value['LogoPath']);
                    }
                }
            }
            $html = View::make('report.email_template', compact('data'))->render();
        } else {
            Log::error("Invalid report type: $type");
            return response()->json(['error' => 'Invalid type'], 422);
        }

        try {
            $pdf = Pdf::loadHTML($html);
            $pdf->save($filePath);
            return response()->download($filePath, $filename);
        } catch (Exception $e) {
            Log::error("PDF generation failed: " . $e->getMessage());
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


        // Step 3: Call Gemini API
        $response = Http::timeout(20)
            ->retry(3, 200)
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . env('GEMINI_API_KEY'),
                [
                    'contents' => [
                        [
                            'parts' => [['text' => $prompt]]
                        ]
                    ]
                ]
            );

        if (!$response->successful()) {
            Log::error("Gemini AI request failed", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json(['error' => 'Gemini AI failed to generate report.'], 500);
        }

        $rawText = $response['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

        // Clean out markdown backticks (```json ... ```)
        $cleanJson = trim($rawText);
        $cleanJson = preg_replace('/^```json\s*/', '', $cleanJson); // remove start ```json
        $cleanJson = preg_replace('/```$/', '', $cleanJson);        // remove end ```

        // Now decode the clean JSON
        $responseData = json_decode($cleanJson, true);


        // 🧾 Fallback-safe values
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
            'results' => $results, // full original results
        ]);



        $filename = 'ai-report-' . Str::slug($userInput) . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
    public function generateUpiReport(Request $request)
    {
        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $data = array_filter($validated['data'], function ($value, $key) {
            $key = strtolower($key);
            if (in_array($key, ['client_id', 'clientid']))
                return false;
            if (is_null($value) || $value === '')
                return false;
            $v = strtolower(trim((string) $value));
            return !in_array($v, ['n/a', 'na', 'n.a']);
        }, ARRAY_FILTER_USE_BOTH);

        $filename = 'upi_details_' . now()->format('Ymd_His') . '_' . Str::uuid() . '.pdf';
        $filePath = storage_path("app/private/reports/{$filename}");
        \Storage::makeDirectory('private/reports');

        try {
            $html = View::make('report.upi_template', compact('data'))->render();
            Pdf::loadHTML($html)->save($filePath);
            return response()->download($filePath, $filename);
        } catch (Exception $e) {
            Log::error("UPI PDF generation error: " . $e->getMessage());
            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }
    public function generateRcReport(Request $request)
    {
        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $data = array_filter($validated['data'], function ($value, $key) {
            $key = strtolower($key);
            if (in_array($key, ['client_id', 'clientid']))
                return false;
            if (is_null($value) || $value === '')
                return false;
            $v = strtolower(trim((string) $value));
            return !in_array($v, ['n/a', 'na', 'n.a']);
        }, ARRAY_FILTER_USE_BOTH);

        $filename = 'rc_details_' . now()->format('Ymd_His') . '_' . Str::uuid() . '.pdf';
        $filePath = storage_path("app/private/reports/{$filename}");
        Storage::makeDirectory('private/reports');

        try {
            $html = View::make('report.rc_template', compact('data'))->render();
            Pdf::loadHTML($html)->save($filePath);
            return response()->download($filePath, $filename);
        } catch (Exception $e) {
            Log::error("RC PDF generation error: " . $e->getMessage());
            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }
    public function generateChallanReport(Request $request)
    {


        $validated = $request->validate([
            'data' => ['required', 'array'],
        ]);

        // Fix: Properly handle nested arrays like challan_details
        $data = array_filter($validated['data'], function ($value, $key) {
            $key = strtolower($key);
            if (in_array($key, ['client_id', 'clientid']))
                return false;
            if (is_null($value) || $value === '')
                return false;
            if (is_scalar($value)) {
                $v = strtolower(trim((string) $value));
                return !in_array($v, ['n/a', 'na', 'n.a']);
            }
            return true; // Keep arrays like challan_details
        }, ARRAY_FILTER_USE_BOTH);

        $filename = 'challan_details_' . now()->format('Ymd_His') . '_' . Str::uuid() . '.pdf';
        $filePath = storage_path("app/private/reports/{$filename}");
        \Storage::makeDirectory('private/reports');

        try {
            $html = View::make('report.challan_template', compact('data'))->render();
            Pdf::loadHTML($html)->save($filePath);
            return response()->download($filePath, $filename);
        } catch (Exception $e) {
            Log::error("Challan PDF generation error: " . $e->getMessage());
            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }

}
