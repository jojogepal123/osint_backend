<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    private function getImageBase64($url)
    {
        try {
            $imgData = file_get_contents($url);
            $type = pathinfo($url, PATHINFO_EXTENSION);
            return 'data:image/' . $type . ';base64,' . base64_encode($imgData);
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            Log::error("PDF generation failed: " . $e->getMessage());
            return response()->json(['error' => 'PDF generation failed'], 500);
        }
    }

    // public function generateReport(Request $request)
    // {
    //     $validated = $request->validate([
    //         'type' => ['required', 'in:tel,email'],
    //         'userInput' => ['required', 'string'],
    //         'results' => ['required', 'array'],
    //     ]);
    //     $data = $validated['results'];
    //     $type = $validated['type'];
    //     $userInput = $validated['userInput'];

    //     $filename = 'report_' . now()->format('Ymd_His') . '_' . Str::uuid() . '.pdf';
    //     $filePath = storage_path("app/private/reports/{$filename}");


    //     if ($type === 'tel') {
    //         // Render the Blade view to HTML
    //         $html = View::make('report.tel_template', compact('data'))->render();
    //     } else if ($type === 'email') {
    //         // Render the Blade view to HTML
    //         $html = View::make('report.email_template', compact('data'))->render();
    //     } else {
    //         Log::error("error occeured");
    //     }
    //     // Generate PDF from HTML
    //     $pdf = Pdf::loadHTML($html);
    //     // Save PDF to storage
    //     $pdf->save($filePath);
    //     // Optionally return download
    //     return response()->download($filePath, $filename);
    // }

    // public function generateAiReport(Request $request)
    // {
    //     $validated = $request->validate([
    //         'type' => ['required', 'in:tel,email'],
    //         'userInput' => ['required', 'string'],
    //         'results' => ['required', 'array'],
    //     ]);

    //     $type = $validated['type'];
    //     $results = $validated['results'];

    //     if ($type === 'tel') {
    //         $payload = [
    //             'type' => 'tel',
    //             'telProfile' => $results['profile'] ?? [],
    //             'osintDataResults' => $results['osintData'] ?? [],
    //         ];
    //     } else {
    //         $payload = [
    //             'type' => 'email',
    //             'emailProfile' => $results['profile'] ?? [],
    //             'emailData' => $results['emailData'] ?? [],
    //             'breachData' => $results['breachData'] ?? [],
    //             'gravatar' => $results['gravatar'] ?? [],
    //             'osintDataResults' => $results['osintData'] ?? [],
    //         ];
    //     }

    //     $response = Http::timeout(60)->post(env('GENREPORT_URL'), $payload);

    //     if(!$response->successful() || !$response->json('filename')){
    //         Log::error('Report generation failed',['response'=> $response->body()]);
    //         return response()->json(['error'=>'Report generation failed'],500);
    //     }

    //     $filename = $response->json('filename');
    //     $filepath = storage_path("app/private/reports/{$filename}");

    //     if(!file_exists($filepath)){
    //         Log::error('File not found',['path'=>$filepath]);
    //         return response()->json(['error'=>'File not found'],500);
    //     }

    //     Log::info("file downloaded successfully!",['filename'=>$filename]);
    //     return response()->download($filepath,$filename);
    // }
}
