<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use HlrLookup\HLRLookupClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Http\Client\ConnectionException;
use GuzzleHttp\Exception\RequestException; // Import this

class ApiServiceController extends Controller
{
    private function sanitizePhoneNumber($number)
    {
        return preg_replace('/\D/', '', $number);
    }

    public function getTelData(Request $request)
    {
        try {
            $request->validate([
                'number' => ['required', 'string', 'max:15'],
            ]);

            $number = $this->sanitizePhoneNumber($request->query('number'));
            $localNumber = preg_replace('/^91/', '', $number); // for India

            $urls = [
                'osint' => env('OSINTDATA_URL'),
                'truecaller' => env('TRUECALLERDATA_URL'),
                'whatsapp' => env('WHATSAPPDATA_URL'),
                'telegram' => env('TELEGRAMDATA_URL'),
                // 'allmobile' => env('ALLMOBILEDATA_URL'),
                'socialmedia' => env('SOCIALMEDIADATA_URL'),
                'spkyc' => env('SPKYC_URL'),
                'spupi' => env('SPUPI_URL'),
                'spbank' => env('SPBANK_URL'),
                'sprc' => env('SPRC_URL'),
            ];

            try {
                $requests = [
                    'osintData' => fn($pool) => $pool->withHeaders([
                        'x-api-key' => env('X_API_KEY'),
                    ])->timeout(30)->get($urls['osint'], [
                        'phone' => $number,
                        'per_page' => 50,
                    ]),

                    'tcData' => fn($pool) => $pool->withHeaders([
                        'x-rapidapi-key' => env('TRUECALLER_API_KEY'),
                        'x-rapidapi-host' => env('TRUECALLER_API_HOST'),
                    ])->timeout(30)->get($urls['truecaller'] . "/{$number}"),

                    'wpData' => fn($pool) => $pool->withHeaders([
                        'x-rapidapi-key' => env('TEL_API_KEY'),
                        'x-rapidapi-host' => env('TEL_API_HOST'),
                    ])->timeout(30)->get($urls['whatsapp'] . "/{$number}"),

                    'telData' => fn($pool) => $pool->withHeaders([
                        'Content-Type' => 'application/json',
                    ])->timeout(30)->post($urls['telegram'], [
                        'phone' => $number,
                    ]),

                    // 'allData' => fn($pool) => $pool->withHeaders([
                    //     'x-rapidapi-host' => env('ALL_MOBILE_API_HOST'),
                    //     'x-rapidapi-key' => env('ALL_MOBILE_API_KEY'),
                    // ])->timeout(30)->get($urls['allmobile'] . "/{$number}"),

                    'smData' => fn($pool) => $pool->withHeaders([
                        'x-rapidapi-key' => env('SOCIAL_MEDIA_API_KEY'),
                        'x-rapidapi-host' => env('SOCIAL_MEDIA_API_HOST'),
                    ])->timeout(30)->get($urls['socialmedia'] . "/?phone={$number}"),

                    // 'sKData' => fn($pool) => $pool->withHeaders([
                    //     'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    // ])->asJson()->timeout(30)->post($urls['spkyc'], [
                    //     'mobile' => $localNumber,
                    // ]),
                    // 'suData' => fn($pool) => $pool->withHeaders([
                    //     'Content-Type' => 'application/json',
                    //     'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    // ])->timeout(30)->post($urls['spupi'], [
                    //     'mobile_number' => $localNumber,
                    // ]),

                    // 'sbData' => fn($pool) => $pool->withHeaders([
                    //     'Content-Type' => 'application/json',
                    //     'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    // ])->timeout(30)->post($urls['spbank'], [
                    //     'mobile_no' => $localNumber,
                    // ]),
                    // 'srData' => fn($pool) => $pool->withHeaders([
                    //     'Content-Type' => 'application/json',
                    //     'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    // ])->timeout(30)->post($urls['sprc'], [
                    //     'mobile_number' => $localNumber,
                    // ]),

                ];
                $responses = Http::pool(fn($pool) => array_map(fn($req) => $req($pool), $requests));
            } catch (\Exception $e) {
                Log::error('API Pool Request Error', [
                    'number' => $number,
                    'message' => $e->getMessage(),
                ]);
            }

            $data = [];

            try {
                $hlrData = null;
                $client = new HLRLookupClient(
                    env('HLR_API_KEY'),
                    env('HLR_API_SECRET'),
                    storage_path('logs/hlr-lookups.log')
                );

                $hlrResponse = $client->post('/hlr-lookup', ['msisdn' => $number]);
                if ($hlrResponse->httpStatusCode === 200) {
                    $hlrData = $hlrResponse->responseBody;
                    $data['hlrData'] = $hlrData;
                } else {
                    throw new \Exception("HLR API HTTP Status: {$hlrResponse->httpStatusCode}");
                }
            } catch (\Throwable $th) {
                Log::error('HLR API Error', [
                    'number' => $number,
                    'error' => $th->getMessage(),
                ]);
            }

            foreach (array_keys($requests) as $index => $key) {
                $response = $responses[$index];

                if ($response instanceof \Throwable) {
                    Log::error("[$key] API Exception", [
                        'type' => get_class($response),
                        'message' => $response->getMessage(),
                    ]);
                    $data[$key] = null;
                    continue;
                }

                try {
                    if ($response->successful()) {
                        $json = $response->json();
                        $data[$key] = $json;
                    } else {
                        Log::warning("[$key] API Failed", [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                        $data[$key] = null;
                    }
                } catch (\Throwable $e) {
                    Log::error("[$key] Unexpected error", ['message' => $e->getMessage()]);
                    $data[$key] = null;
                }
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Global Phone API Error (Caught outside API calls)', [
                'number' => $request->query('phone'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }


    public function getEmailData(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email', 'max:255'],
            ]);

            $email = $request->query('email');

            $urls = [
                'osint' => env('OSINTDATA_URL'),
                'zehef' => env('ZEHEFDATA_URL'),
                'holehe' => env('HOLEHEDATA_URL'),
                'gmail' => env('EMAILDATA_URL'),
                'hibp' => env('HIBPDATA_URL') . "/{$email}",
            ];

            try {
                $requests = [
                    'osintData' => fn($pool) => $pool->withHeaders([
                        'x-api-key' => env('X_API_KEY'),
                    ])->timeout(30)->get($urls['osint'], [
                        'email' => $email,
                        'per_page' => 50,
                    ]),
                    'zehefData' => fn($pool) => $pool->timeout(60)->post($urls['zehef'], ['email' => $email]),

                    'holeheData' => fn($pool) => $pool->timeout(30)->post($urls['holehe'], ['email' => $email]),

                    // 'emailData' => fn($pool) => $pool->timeout(30)->asJson()->post($urls['gmail'], ['email' => $email]),

                    'hibpData' => fn($pool) => $pool->withHeaders([
                        'hibp-api-key' => env('HIBP_API_KEY'),
                        'User-Agent' => 'LaravelApp/1.0',
                    ])->timeout(30)->get($urls['hibp'], ['truncateResponse' => 'false']),
                ];
                $responses = Http::pool(fn($pool) => array_map(fn($req) => $req($pool), $requests));
            } catch (\Exception $e) {
                // This catches other request-related errors (e.g., malformed URL, other Guzzle errors)
                Log::error('API Pool Request Error', [
                    'email' => $email,
                    'message' => $e->getMessage(),
                ]);
            }
            $data = [];

            foreach (array_keys($requests) as $index => $key) {
                $response = $responses[$index];

                if ($response instanceof \Throwable) {
                    // Handle exceptions like timeout, DNS failure, etc.
                    Log::error("[$key] API Exception", [
                        'type' => get_class($response),
                        'message' => $response->getMessage(),
                    ]);
                    $data[$key] = null;
                    continue;
                }

                try {
                    if ($response->successful()) {
                        $json = $response->json();
                        // Log::info("[$key] API Success", ['response' => $json]);
                        if ($key === 'emailData') {
                            // Flatten "data" into root
                            if (isset($json['data']) && is_array($json['data'])) {
                                // Merge data into root
                                $flattened = array_merge($json['data'], Arr::except($json, ['data']));
                                $data[$key] = $flattened;
                            } else {
                                $data[$key] = $json;
                            }
                        } else {
                            $data[$key] = $json;
                        }
                    } else {
                        Log::warning("[$key] API Failed", [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                        $data[$key] = null;
                    }
                } catch (\Throwable $e) {
                    Log::error("[$key] Unexpected error", ['message' => $e->getMessage()]);
                    $data[$key] = null;
                }
            }
            return response()->json($data);
        } catch (\Exception $e) {
            // This catches validation errors, or any other unexpected errors outside the API calls
            Log::error('Global Email API Error (Caught outside API calls)', [
                'email' => $request->query('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Add trace for more detail
            ]);
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }

    public function leakDataFinder(Request $request)
    {
        $data = $request->input('fields');
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);

        if (!$data || !is_array($data)) {
            return response()->json(['error' => 'Invalid search data'], 400);
        }

        $params = [
            'page' => $page,
            'per_page' => $perPage,
        ];
        $hasKeyValue = false;
        foreach ($data as $item) {
            $key = $item['type'] ?? null;
            $value = $item['value'] ?? null;

            if ($key && $value) {
                $params[$key] = $value;
                $hasKeyValue = true;
            }
        }

        if (!$hasKeyValue) {
            return response()->json(['error' => 'No valid search parameters provided'], 400);
        }
        try {
            $headers = [
                'x-api-key' => env('X_API_KEY'),
                'Content-Type' => 'application/json',
            ];
            $fastapiUrl = env('OSINTDATA_URL');
            $response = Http::withHeaders($headers)->timeout(30)->get($fastapiUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json($data);
            } else {
                return response()->json(['error' => 'Failed to fetch data from API'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Leak Data Finder Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }

    public function corporateData(Request $request)
    {
        $type = $request->input('type');
        $data = $request->input('data');

        if (!$type || !$data) {
            return response()->json(['error' => 'Invalid search request'], 400);
        }

        switch ($type) {
            case 'corporate_gstin':
                return $this->handleCorporateGstin($data);
            case 'credit_report':
                return $this->handleCreditReport($data);
            case 'corporate_cin':
                return $this->handleCorporateCin($data);
            case 'gst_intel':
                return $this->handleGstIntel($data);
            case 'employment_history':
                return $this->handleEmploymentHistory($data);
            case 'find_uan':
                return $this->handleFindUan($data);
            case 'pan_to_uan':
                return $this->handlePanToUan($data);
            default:
                return response()->json(['error' => 'Invalid search request'], 400);
        }
    }

    private function handleCorporateGstin($data)
    {
        return response()->json(['message' => 'Handled corporate_gstin', 'data' => $data]);
    }

    private function handleCreditReport($data)
    {
        // Validate and process $data as needed
        return response()->json(['message' => 'Handled credit_report', 'data' => $data]);
    }

    private function handleCorporateCin($data)
    {
        // Validate and process $data as needed
        return response()->json(['message' => 'Handled corporate_cin', 'data' => $data]);
    }

    private function handleGstIntel($data)
    {
        // Validate and process $data as needed
        return response()->json(['message' => 'Handled gst_intel', 'data' => $data]);
    }

    private function handleEmploymentHistory($data)
    {
        // Validate and process $data as needed
        return response()->json(['message' => 'Handled employment_history', 'data' => $data]);
    }

    private function handleFindUan($data)
    {
        // Validate and process $data as needed
        return response()->json(['message' => 'Handled find_uan', 'data' => $data]);
    }

    private function handlePanToUan($data)
    {
        // Validate and process $data as needed
        return response()->json(['message' => 'Handled pan_to_uan', 'data' => $data]);
    }
}
