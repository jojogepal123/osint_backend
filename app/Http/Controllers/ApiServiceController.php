<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use HlrLookup\HLRLookupClient;
use Illuminate\Support\Facades\Log;
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
                'allmobile' => env('ALLMOBILEDATA_URL'),
                'socialmedia' => env('SOCIALMEDIADATA_URL'),
                'spkyc' => env('SPKYC_URL'),
                'spupi' => env('SPUPI_URL'),
                'spbank' => env('SPBANK_URL'),
            ];

            try {
                $requests = [
                    'osintData' => fn($pool) => $pool->withHeaders([
                        'x-api-key' => env('X_API_KEY'),
                    ])->timeout(30)->get($urls['osint'], [
                                'phone' => $number,
                                'per_page' => 50,
                            ]),

                    'truecallerData' => fn($pool) => $pool->withHeaders([
                        'x-rapidapi-key' => env('TRUECALLER_API_KEY'),
                        'x-rapidapi-host' => env('TRUECALLER_API_HOST'),
                    ])->timeout(30)->get($urls['truecaller'] . "/{$number}"),

                    'whatsappData' => fn($pool) => $pool->withHeaders([
                        'x-rapidapi-key' => env('TEL_API_KEY'),
                        'x-rapidapi-host' => env('TEL_API_HOST'),
                    ])->timeout(30)->get($urls['whatsapp'] . "/{$number}"),

                    'telegramData' => fn($pool) => $pool->withHeaders([
                        'Content-Type' => 'application/json',
                    ])->timeout(30)->post($urls['telegram'], [
                                'phone' => $number,
                            ]),

                    'allMobileData' => fn($pool) => $pool->withHeaders([
                        'x-rapidapi-host' => env('ALL_MOBILE_API_HOST'),
                        'x-rapidapi-key' => env('ALL_MOBILE_API_KEY'),
                    ])->timeout(30)->get($urls['allmobile'] . "/{$number}"),

                    'socialMediaData' => fn($pool) => $pool->withHeaders([
                        'x-rapidapi-key' => env('SOCIAL_MEDIA_API_KEY'),
                        'x-rapidapi-host' => env('SOCIAL_MEDIA_API_HOST'),
                    ])->timeout(30)->get($urls['socialmedia'] . "/?phone={$number}"),

                    'surepassKyc' => fn($pool) => $pool->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    ])->timeout(30)->post($urls['spkyc'], [
                                'mobile' => $localNumber,
                            ]),

                    'surepassUpi' => fn($pool) => $pool->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    ])->timeout(30)->post($urls['spupi'], [
                                'mobile_number' => $localNumber,
                            ]),

                    'surepassBank' => fn($pool) => $pool->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    ])->timeout(30)->post($urls['spbank'], [
                                'mobile_no' => $localNumber,
                            ]),
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
                'gmail' => env('EMAILDATA_URL') . "/{$email}",
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
                    'zehefData' => fn($pool) => $pool->timeout(30)->post($urls['zehef'], ['email' => $email]),

                    'holeheData' => fn($pool) => $pool->timeout(30)->post($urls['holehe'], ['email' => $email]),

                    'emailData' => fn($pool) => $pool->withHeaders([
                        'x-rapidapi-key' => env('EMAIL_API_KEY'),
                        'x-rapidapi-host' => env('EMAIL_API_HOST'),
                    ])->timeout(30)->get($urls['gmail']),

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
            // This catches validation errors, or any other unexpected errors outside the API calls
            Log::error('Global Email API Error (Caught outside API calls)', [
                'email' => $request->query('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Add trace for more detail
            ]);
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }
}
