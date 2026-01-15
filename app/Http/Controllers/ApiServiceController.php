<?php

namespace App\Http\Controllers;

// use Google\Service\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use HlrLookup\HLRLookupClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Http\Client\ConnectionException;
use GuzzleHttpException\RequestException; // Import this
use Illuminate\Support\Facades\Storage;
use Exception;
use Throwable;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;


class ApiServiceController extends Controller
{
    private function sanitizePhoneNumber($number)
    {
        return preg_replace('/\D/', '', $number);
    }

    private function fetchOsPhoneData(string $number): ?array
    {
        try {
            $response = Http::withHeaders([
                'api-key' => env('OSINT_API_KEY'),
                'accept' => 'application/json',
            ])
                ->withOptions([
                        'connect_timeout' => 8,   // fail quickly if we can't connect
                    ])
                ->timeout(20)                // ❗ hard limit: 20 seconds, well below 60
                ->get(env('OSINT_PHONE', 'https://api.osint.industries/v2/request'), [
                    'type' => 'phone',
                    'query' => $number,
                    'timeout' => 20,          // OSINT internal timeout, <= 80
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('osPhoneData failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (ConnectionException $e) {
            // This is where cURL error 28 will land
            Log::warning('osPhoneData connection/timeout', [
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            Log::error('osPhoneData unexpected error', [
                'message' => $e->getMessage(),
            ]);
        }

        return null; // no data, but no fatal error
    }



    public function getTelData(Request $request)
    {
        // Make sure this request itself is allowed to run longer than 60s (if needed)
        // ini_set('max_execution_time', '180'); // 180 seconds
        // set_time_limit(180);                  // some SAPIs respect this better
        // Log::info('PHP max_execution_time', [
        //     'value' => ini_get('max_execution_time'),
        // ]);
        try {
            $request->validate([
                'number' => ['required', 'string', 'max:15'],
            ]);

            Log::info($request->query('number'));

            $raw = $this->sanitizePhoneNumber($request->query('number'));
            $raw = ltrim($raw, '+');

            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                // parse with null region so numbers starting with country code are understood
                $proto = $phoneUtil->parse($raw, "IN");

                // Validate
                if (!$phoneUtil->isValidNumber($proto)) {
                    Log::warning('libphonenumber: invalid number', ['raw' => $raw]);
                    return response()->json(['error' => 'Invalid phone number.'], 422);
                }

                $countryCode = (string) $proto->getCountryCode();
                $localNumber = (string) $proto->getNationalNumber();

                Log::info('Parsed phone via libphonenumber', ['raw' => $raw, 'code' => $countryCode, 'local' => $localNumber]);
            } catch (NumberParseException $ex) {
                Log::warning('libphonenumber parse failed', ['raw' => $raw, 'error' => $ex->getMessage()]);
                return response()->json(['error' => 'Unable to parse phone number.'], 422);
            }

            $number = $raw;
            $user = auth()->user();
            $urls = [
                'osint' => env('OSINTDATA_URL'),
                'truecaller' => env('TRUECALLERDATA_URL'),
                // 'viewcaller' => env('VIEW_CALLER'),
                // 'sync' => env('SYNCDATA'),
                'whatsapp' => env('WHATSAPPDATA_URL'),
                'telegram' => env('TELEGRAMDATA_URL'),
                // 'allmobile' => env('ALLMOBILEDATA_URL'),
                'callerapi' => env('CALL_PRES_URL'),
                // 'socialmedia' => env('SOCIALMEDIADATA_URL'),
                'spkyc' => env('SPKYC_URL'),
                'spupi' => env('SPUPI_URL'),
                // 'spbank' => env('SPBANK_URL'),
                'sprc' => env('SPRC_URL'),
                // 'osphone' => env('OSINT_PHONE'),

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
                    ])->timeout(40)->get($urls['truecaller'] . "/{$number}"),


                    // 'osPhoneData' => fn($pool) => $pool->withHeaders([
                    //     'api-key' => env('OSINT_API_KEY'),
                    //     'accept' => 'application/json',
                    // ])->timeout(40)->get($urls['osphone'], [
                    //             'type' => 'phone',
                    //             'query' => $number,
                    //             'timeout' => 60,
                    //         ]),
                    // VIEWCALLER: send query params properly (not string concat)
                    // 'vcData' => fn($pool) => $pool->withHeaders([
                    //     'x-rapidapi-key' => env('VIEWCALLER_API_KEY'),
                    //     'x-rapidapi-host' => env('VIEWCALLER_API_HOST'),
                    // ])->timeout(40)->get($urls['viewcaller'], [
                    //             'code' => $countryCode,
                    //             'number' => $localNumber,
                    //         ]),

                    // // SYNC: query params as array (note code param order doesn't matter)
                    // 'syncData' => fn($pool) => $pool->withHeaders([
                    //     'x-rapidapi-key' => env('SYNC_API_KEY'),
                    //     'x-rapidapi-host' => env('SYNC_API_HOST'),
                    // ])->timeout(40)->get($urls['sync'], [
                    //             'number' => $localNumber,
                    //             'code' => $countryCode,
                    //         ]),
                    'wpData' => fn($pool) => $pool->withHeaders([
                        'x-rapidapi-key' => env('TEL_API_KEY'),
                        'x-rapidapi-host' => env('TEL_API_HOST'),
                    ])->timeout(40)->get($urls['whatsapp'] . "/{$number}"),


                    // 'telData' => fn($pool) => $pool->withHeaders([
                    //     'Content-Type' => 'application/json',
                    // ])->retry(3, 300)->timeout(30)->post($urls['telegram'], [
                    //             'phone' => '+' . $number,
                    //         ]),



                    // 'allData' => fn($pool) => $pool->withHeaders([
                    //     'x-rapidapi-host' => env('ALL_MOBILE_API_HOST'),
                    //     'x-rapidapi-key' => env('ALL_MOBILE_API_KEY'),
                    // ])->timeout(30)->get($urls['allmobile'] . "/{$number}"),

                    // 'smData' => fn($pool) => $pool->withHeaders([
                    //     'x-rapidapi-key' => env('SOCIAL_MEDIA_API_KEY'),
                    //     'x-rapidapi-host' => env('SOCIAL_MEDIA_API_HOST'),
                    // ])->timeout(30)->get($urls['socialmedia'] . "/?phone={$number}"),

                    'sKData' => fn($pool) => $pool->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    ])->timeout(40)->post($urls['spkyc'], [
                                'mobile' => $localNumber,
                            ]),
                    'suData' => fn($pool) => $pool->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    ])->timeout(40)->post($urls['spupi'], [
                                'mobile_number' => $localNumber,
                            ]),

                    // 'sbData' => fn($pool) => $pool->withHeaders([
                    //     'Content-Type' => 'application/json',
                    //     'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    // ])->timeout(30)->post($urls['spbank'], [
                    //             'mobile_no' => $localNumber,
                    //         ]),
                    'srData' => fn($pool) => $pool->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                    ])->timeout(40)->post($urls['sprc'], [
                                'mobile_number' => $localNumber,
                            ]),

                ];
                $responses = Http::pool(fn($pool) => array_map(fn($req) => $req($pool), $requests));
            } catch (Exception $e) {
                Log::error('API Pool Request Error', [
                    'number' => $number,
                    'message' => $e->getMessage(),
                ]);
            }

            $data = [];
            $anySuccessful = false;
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
                    throw new Exception("HLR API HTTP Status: {$hlrResponse->httpStatusCode}");
                }
            } catch (Throwable $th) {
                Log::error('HLR API Error', [
                    'number' => $number,
                    'error' => $th->getMessage(),
                ]);
            }

            $data['telData'] = null;

            try {
                $telResponse = Http::timeout(120)
                    ->connectTimeout(5)
                    ->post($urls['telegram'], [
                            'phone' => '+' . $number,
                        ]);

                if ($telResponse->successful()) {
                    $data['telData'] = $telResponse->json();
                } else {
                    Log::warning('[telData] API Failed', [
                        'status' => $telResponse->status(),
                        'body' => $telResponse->body(),
                    ]);
                }
            } catch (Throwable $e) {
                Log::error('[telData] API Exception', [
                    'message' => $e->getMessage(),
                ]);
            }

            foreach (array_keys($requests) as $index => $key) {
                $response = $responses[$index];

                if ($response instanceof Throwable) {
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
                        $anySuccessful = true;

                    } else {
                        Log::warning("[$key] API Failed", [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                        $data[$key] = null;
                    }
                } catch (Throwable $e) {
                    Log::error("[$key] Unexpected error", ['message' => $e->getMessage()]);
                    $data[$key] = null;
                }
            }
            if ($anySuccessful) {
                $deduction = env('TEL_COST');

                if ($user->credits >= $deduction) {
                    $user->credits -= $deduction;
                    $user->save();
                } else {
                    return response()->json([
                        'message' => 'Insufficient credits.',
                        'credits' => $user->credits,
                    ], 402);
                }
            }
            // $osPhoneJson = $this->fetchOsPhoneData($number);
            // $data['osPhoneData'] = $osPhoneJson;
            // if ($osPhoneJson) {
            //     $anySuccessful = true; // if you want it to count for credits
            // }

            Log::info($data);


            return response()->json([
                ...$data,
                'credits' => $user->credits,
            ]);
        } catch (Exception $e) {
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
            $encodedEmail = urlencode($email);
            $user = auth()->user();

            $urls = [
                'osint' => env('OSINTDATA_URL'),
                'zehef' => env('ZEHEFDATA_URL'),
                'holehe' => env('HOLEHEDATA_URL'),
                'gmail' => env('EMAILDATA_URL'),
                'socialscan' => env('SOCIALSCAN_URL'),
                'hibp' => env('HIBPDATA_URL') . "/{$email}",
                'getuser' => env('GETUSER_API_BASE') . "?email={$encodedEmail}&apikey=" . env('GETUSER_API_KEY'),
                // 'osEmail' => env('OSINT_EMAIL'),
            ];

            $requests = [
                'osintData' => fn($pool) => $pool->withHeaders([
                    'x-api-key' => env('X_API_KEY'),
                ])->timeout(30)->get($urls['osint'], ['email' => $email, 'per_page' => 50]),

                'zehefData' => fn($pool) => $pool->timeout(60)->post($urls['zehef'], ['email' => $email]),
                // 'osEmailData' => fn($pool) => $pool->withHeaders([
                //     'api-key' => env('OSINT_API_KEY'),
                //     'accept' => 'application/json',
                // ])->timeout(30)->get($urls['osEmail'], [
                //             'query' => $email,
                //             'timeout' => 60
                //         ]),
                'holeheData' => fn($pool) => $pool->timeout(30)->post($urls['holehe'], ['email' => $email]),
                'socialScanData' => fn($pool) => $pool->timeout(30)->asJson()->post($urls['socialscan'], ['email' => $email]),
                'emailData' => fn($pool) => $pool->timeout(30)->asJson()->post($urls['gmail'], ['email' => $email]),
                'getuserData' => fn($pool) => $pool->timeout(30)->get($urls['getuser']),
                'hibpData' => fn($pool) => $pool->withHeaders([
                    'hibp-api-key' => env('HIBP_API_KEY'),
                    'User-Agent' => 'LaravelApp/1.0',
                ])->timeout(30)->get($urls['hibp'], ['truncateResponse' => 'false']),
            ];

            $responses = Http::pool(fn($pool) => array_map(fn($req) => $req($pool), $requests));
            $data = [];
            $anySuccessful = false;

            foreach (array_keys($requests) as $index => $key) {
                $response = $responses[$index];

                if ($response instanceof Throwable) {
                    Log::error("[$key] API Exception", ['type' => get_class($response), 'message' => $response->getMessage()]);
                    $data[$key] = null;
                    continue;
                }

                try {
                    if ($response->successful()) {
                        $json = $response->json();

                        if ($key === 'emailData' && isset($json['data']) && is_array($json['data'])) {
                            $data[$key] = array_merge($json['data'], Arr::except($json, ['data']));
                        } else {
                            $data[$key] = $json;
                        }

                        $anySuccessful = true;
                    } else {
                        Log::warning("[$key] API Failed", ['status' => $response->status(), 'body' => $response->body()]);
                        $data[$key] = null;
                    }
                } catch (Throwable $e) {
                    Log::error("[$key] Unexpected error", ['message' => $e->getMessage()]);
                    $data[$key] = null;
                }
            }

            // Deduct credits only if any response was successful
            if ($anySuccessful) {
                $deduction = env('EMAIL_COST');

                if ($user->credits >= $deduction) {
                    $user->credits -= $deduction;
                    $user->save();
                } else {
                    return response()->json([
                        'message' => 'Insufficient credits.',
                        'credits' => $user->credits,
                    ], 402); // Payment Required
                }
            }

            return response()->json([
                'data' => $data,
                'credits' => $user->credits,
            ]);
        } catch (Exception $e) {
            Log::error('Global Email API Error', [
                'email' => $request->query('email'),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Server error occurred.'], 500);
        }
    }
    public function getUpiFullDetails(Request $request)
    {
        $request->validate([
            'upi_id' => ['required', 'string'],
        ]);

        $upiId = $request->input('upi_id');


        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('SPUPI_FULL_URL'), [
                        'upi_id' => $upiId,
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                $deduction = env('UPI_COST');
                $user = auth()->user();

                // Check if user has sufficient credits
                if ($user->credits >= $deduction) {
                    $user->credits -= $deduction;
                    $user->save();

                    return response()->json([
                        'data' => $data,
                        'credits' => $user->credits,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Insufficient credits.',
                        'credits' => $user->credits,
                    ], 402);
                }
            } else {
                return response()->json([
                    'error' => 'Failed to fetch UPI details',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], $response->status());
            }
        } catch (Throwable $e) {
            Log::error('Surepass UPI Full Lookup Error', [
                'error' => $e->getMessage(),
                'upi_id' => $upiId,
            ]);
            return response()->json(['error' => 'Server error occurred.'], 500);
        }
    }

    public function getRcFullDetails(Request $request)
    {
        $request->validate([
            'id_number' => ['required', 'string'],
        ]);

        $idNumber = $request->input('id_number');


        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('SPRCTXT_URL'), [
                        'id_number' => $idNumber,
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                $deduction = env('RC_COST');
                $user = auth()->user();

                // Check if user has sufficient credits
                if ($user->credits >= $deduction) {
                    $user->credits -= $deduction;
                    $user->save();

                    return response()->json([
                        'data' => $data,
                        'credits' => $user->credits,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Insufficient credits.',
                        'credits' => $user->credits,
                    ], 402);
                }
            } else {
                return response()->json([
                    'error' => 'Failed to fetch RC details',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], $response->status());
            }
        } catch (Throwable $e) {
            Log::error('Surepass RC Full Lookup Error', [
                'error' => $e->getMessage(),
                'id_number' => $idNumber,
            ]);
            return response()->json(['error' => 'Server error occurred.'], 500);
        }
    }

    public function getRcChallanDetails(Request $request)
    {
        $request->validate([
            'rc_number' => ['required', 'string'],
        ]);

        $rcNumber = $request->input('rc_number');


        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('SPRCCHALLAN_URL'), [
                        'rc_number' => $rcNumber,
                    ]);

            if ($response->successful()) {
                // Deduct 7.00 credits from authenticated user
                $user = auth()->user();
                if ($user->credits >= 7) {
                    $user->credits -= env('RC_CHALLAN_COST');
                    $user->save();
                } else {
                    return response()->json([
                        'message' => 'Insufficient credits',
                        'credits' => $user->credits
                    ], 402);
                }

                return response()->json([
                    'data' => $response->json(),
                    'credits' => $user->credits,
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to fetch RC Challan details',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], $response->status());
            }
        } catch (Throwable $e) {
            Log::error('Surepass RC Challan Lookup Error', [
                'error' => $e->getMessage(),
                'rc_number' => $rcNumber,
            ]);
            return response()->json(['error' => 'Server error occurred.'], 500);
        }
    }

    public function leakDataFinder(Request $request)
    {
        $data = $request->input('fields');
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);
        $anySuccessful = false;
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
                $anySuccessful = true;
                if ($anySuccessful) {
                    $deduction = env('LEAKDATA_COST');
                    $user = auth()->user();
                    if ($user->credits >= $deduction) {
                        $user->credits -= $deduction;
                        $user->save();
                    } else {
                        return response()->json([
                            'message' => 'Insufficient credits.',
                            'credits' => $user->credits,
                        ], 402);
                    }
                }


                return response()->json([
                    ...$data,
                    'credits' => $user->credits,
                ]);
            } else {
                return response()->json(['error' => 'Failed to fetch data from API'], 500);
            }
        } catch (Exception $e) {
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
        $idNumber = $data['id_number'] ?? null;
        if (!$idNumber) {
            return response()->json(['error' => 'ID number is required'], 400);
        }
        $idNumber = strtoupper(trim($idNumber));
        if (!preg_match('/^[A-Z0-9]+$/', $idNumber)) {
            return response()->json(['error' => 'Invalid ID number format'], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('CORPORATE_GSTIN_URL'), [
                        'id_number' => $idNumber,
                    ]);
            return $this->deductUserCredits($response, env('CORPORATE_GSTIN_COST'));

            // return $this->handleSurepassResponse($response);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    private function handleCreditReport($data)
    {
        $required = ['mobile', 'pan', 'name', 'gender', 'consent'];
        $mobile = $data['mobile'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return response()->json(['error' => "Missing required field: {$field}"], 422);
            }
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('CORPORATE_CREDIT_URL'), [
                        'mobile' => $data['mobile'],
                        'pan' => $data['pan'],
                        'name' => $data['name'],
                        'gender' => $data['gender'],
                        'consent' => $data['consent'],
                    ]);

            if ($response->successful()) {
                $json = $response->json();
                $pdfUrl = $json['data']['credit_report_link'] ?? null;
                if (!$pdfUrl) {
                    return response()->json(['error' => 'Credit report not found for this search'], 422);
                }
                $pdfResponse = Http::timeout(30)->get($pdfUrl);
                if (!$pdfResponse->successful()) {
                    return response()->json(['error' => 'Failed to download credit report'], 500);
                }
                // Save the PDF to storage
                Storage::makeDirectory('cibil_reports');
                $filename = 'cibil_report_' . $mobile . '_' . now()->format('Ymd_His') . '.pdf';
                $filePath = "cibil_reports/{$filename}";
                Storage::put($filePath, $pdfResponse->body());

                // return response($pdfResponse->body(), 200, [
                //     'Content-Type' => 'application/pdf',
                //     'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                // ]);
                // Deduct ₹150.00 and return PDF if credits are sufficient
                return $this->deductUserCreditsForPdf($pdfResponse->body(), env('CREDIT_REPORT_COST'), $filename);
            }

            return $this->handleSurepassResponse($response);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
    private function deductUserCreditsForPdf($pdfContent, float $deduction, $filename)
    {
        $user = auth()->user();

        if ($user && $user->credits >= $deduction) {
            $user->credits -= $deduction;
            $user->save();

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'X-Remaining-Credits' => $user->credits,
            ]);
        } else {
            return response()->json([
                'message' => 'Insufficient credits.',
                'credits' => $user->credits ?? 0,
            ], 402);
        }
    }

    private function handleCorporateCin($data)
    {
        $cin = $data['id_number'] ?? null;
        if (!$cin) {
            return response()->json(['error' => 'CIN is required'], 400);
        }
        $cin = strtoupper(trim($cin));

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('CORPORATE_CIN_URL'), [
                        'id_number' => $cin,
                    ]);
            return $this->deductUserCredits($response, env('CORPORATE_CIN_COST'));

            // return $this->handleSurepassResponse($response);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    private function handleGstIntel($data)
    {
        $gst = $data['id_number'] ?? null;
        if (!$gst) {
            return response()->json(['error' => 'GST number is required'], 400);
        }
        $gst = strtoupper(trim($gst));
        if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}$/', $gst)) {
            return response()->json(['error' => 'Invalid GST number format'], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('GST_INTEL_URL'), [
                        'id_number' => $gst,
                    ]);
            return $this->deductUserCredits($response, env('GST_INTEL_COST'));

            // return $this->handleSurepassResponse($response);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    private function handleEmploymentHistory($data)
    {
        $idNumber = $data['id_number'] ?? null;
        if (!$idNumber) {
            return response()->json(['error' => 'ID number is required'], 400);
        }
        $idNumber = strtoupper(trim($idNumber));

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('EMPLOYMENT_HISTORY_URL'), [
                        'id_number' => $idNumber,
                    ]);
            return $this->deductUserCredits($response, env('EMPLOYEMENT_HISTORY'));

            // return $this->handleSurepassResponse($response);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    private function handleFindUan($data)
    {
        $mobile = $this->sanitizePhoneNumber($data['mobile_number'] ?? '');
        if (strlen($mobile) < 10 || strlen($mobile) > 15) {
            return response()->json(['error' => 'Invalid mobile number'], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('FIND_UAN_URL'), [
                        'mobile_number' => $mobile,
                    ]);
            return $this->deductUserCredits($response, env('FINDUAN_COST'));

            // return $this->handleSurepassResponse($response);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    private function handlePanToUan($data)
    {
        $pan = strtoupper(trim($data['pan_number'] ?? ''));
        if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
            return response()->json(['error' => 'Invalid PAN number format'], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(env('PAN_TO_UAN_URL'), [
                        'pan_number' => $pan,
                    ]);
            return $this->deductUserCredits($response, env('PAN_TO_UAN_COST'));

            // return $this->handleSurepassResponse($response);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
    private function deductUserCredits($response, float $deduction)
    {
        if ($response->successful()) {
            $data = $response->json();
            $user = auth()->user();

            if ($user && $user->credits >= $deduction) {
                $user->credits -= $deduction;
                $user->save();

                return response()->json([
                    'data' => $data,
                    'credits' => $user->credits,
                ]);
            } else {
                return response()->json([
                    'message' => 'Insufficient credits.',
                    'credits' => $user->credits,
                ], 402);
            }
        }

        return $this->handleSurepassResponse($response); // fallback for non-200 responses
    }
    private function handleSurepassResponse($response)
    {
        if ($response->successful()) {
            return response()->json($response->json());
        }

        Log::error('Surepass API error', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if ($response->status() === 422) {
            return response()->json([
                'error' => 'No data found',
                'details' => $response->json(),
            ], 422);
        }

        return response()->json([
            'error' => 'Surepass API error',
            'details' => $response->json(),
        ], $response->status());
    }

    private function handleException($e)
    {
        Log::error('Surepass Exception', ['message' => $e->getMessage()]);
        return response()->json([
            'error' => 'Server error',
            'message' => $e->getMessage(),
        ], 500);
    }

    public function verificationIdData(Request $request)
    {

        $request->validate([
            'type' => 'required|string',
            'data' => 'required|array',
        ]);
        $type = $request->input('type');
        $data = $request->input('data');

        if (!$type || !$data) {
            Log::error('Missing type or data in request');
            return response()->json(['error' => 'Invalid search request'], 400);
        }

        switch ($type) {
            case 'pan':
                return $this->handlePanVerification($data);
            case 'voter_id':
                return $this->handleVoterIdVerification($data);
            case 'employment':
                return $this->handleEmploymentVerification($data);
            case 'bank_account':
                return $this->handleBankAccountVerification($data);
            case 'passport':
                return $this->handlePassportVerification($data);
            case 'vehicle_rc':
                return $this->handleVehicleRcVerification($data);
            case 'ifsc':
                return $this->handleIfscVerification($data);
            case 'driving_license':
                return $this->handleDrivingLicenseVerification($data);
            default:
                return response()->json(['error' => 'Invalid verification type'], 400);
        }
    }

    private function generateSignature()
    {
        try {
            $clientId = env('CASHFREE_CLIENT_ID');
            $timestamp = time();
            $data = $clientId . '.' . $timestamp;

            $publicKeyPath = storage_path('app/cashfree_public_key.pem');

            if (!file_exists($publicKeyPath)) {
                Log::error('Public key file not found');
                return null;
            }

            $publicKeyContent = file_get_contents($publicKeyPath);
            $publicKey = openssl_pkey_get_public($publicKeyContent);

            if (!$publicKey) {
                Log::error('Invalid public key format');
                return null;
            }

            if (openssl_public_encrypt($data, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING)) {
                return base64_encode($encrypted);
            }

            return null;
        } catch (Exception $e) {
            Log::error('Signature generation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function getHeaders()
    {
        $signature = $this->generateSignature();

        if (!$signature) {
            // If signature fails, you MUST whitelist IP
            Log::error('Signature generation failed - IP whitelisting required');
            throw new Exception('Authentication failed: Either whitelist IP or fix signature generation');
        }

        return [
            'Content-Type' => 'application/json',
            'x-client-id' => env('CASHFREE_CLIENT_ID'),
            'x-client-secret' => env('CASHFREE_CLIENT_SECRET'),
            'x-cf-signature' => $signature,
        ];
    }
    private function handleBankAccountVerification($data)
    {

        $accountNumber = $data['account_number'] ?? null;
        $ifsc = $data['ifsc'] ?? null;
        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;

        if (!$accountNumber || !$ifsc) {
            return response()->json(['error' => 'Account number and IFSC are required'], 400);
        }

        $payload = [
            'bank_account' => $accountNumber,
            'ifsc' => $ifsc,
        ];
        if (!empty($name)) {
            $payload['name'] = $name;
        }
        if (!empty($phone)) {
            $payload['phone'] = $phone;
        }


        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post(env('BANK_VERIFICATION_URL'), $payload);


            return $this->deductUserBankCredits($response, env('BANK_ACCOUNT_VERIFY_COST'));
            // if ($response->successful()) {
            //     return response()->json([
            //         'success' => true,
            //         'data' => $response->json()
            //     ]);
            // }

            // return response()->json([
            //     'success' => false,
            //     'message' => 'Bank verification failed',
            //     'error' => $response->json(),
            //     'status' => $response->status()
            // ], $response->status());

        } catch (Exception $e) {
            Log::error('Bank verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function deductUserBankCredits($response, float $deduction = 1.00)
    {
        if ($response->successful()) {
            $data = $response->json();
            $user = auth()->user();

            if ($user && $user->credits >= $deduction) {
                $user->credits -= $deduction;
                $user->save();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'credits' => $user->credits,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient credits.',
                    'credits' => $user->credits ?? 0,
                ], 402);
            }
        }

        return $this->handleResponse($response); // fallback
    }

    private function handlePanVerification($data)
    {
        $pan = strtoupper(trim($data['pan'] ?? ''));
        if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
            Log::error('Invalid PAN format', ['pan' => $pan]);
            return response()->json(['error' => 'Invalid PAN number format'], 400);
        }

        $payload = [
            'pan' => $pan,
            'verification_id' => 'PAN360_' . uniqid() . '_' . time(),
        ];

        if (!empty($data['name'])) {
            $payload['name'] = $data['name'];
        }



        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post(env('PAN_VERIFICATION_URL'), $payload);


            return $this->deductUserVerifyCredits($response, env('PAN_VERIFY_COST'));
            // return $this->handleResponse($response);
        } catch (Exception $e) {
            Log::error('PAN360 verification exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->handleVerificationException($e);
        }
    }

    private function handleVoterIdVerification($data)
    {
        $voterId = strtoupper(trim($data['epic_number'] ?? ''));
        if (empty($voterId)) {
            return response()->json(['error' => 'Voter ID is required'], 400);
        }

        $payload = [
            'epic_number' => $voterId,
            'verification_id' => 'VOTER_' . uniqid() . '_' . time(),
        ];
        if (!empty($data['name'])) {
            $payload['name'] = $data['name'];
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post(env('VOTER_ID_VERIFICATION_URL'), $payload);
            return $this->deductUserVerifyCredits($response, env('VOTER_ID_VERIFY_COST'));
            // return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleVerificationException($e);
        }
    }

    private function handleEmploymentVerification($data)
    {
        $validCombos = [
            ["phone"],
            ["uan"],
            ["phone", "pan"],
            ["phone", "dob", "employer_name"],
            ["phone", "employee_name", "employer_name"],
            ["phone", "dob", "employee_name", "employer_name"],
            ["phone", "pan", "employee_name", "employer_name"],
            ["phone", "dob", "pan", "employee_name", "employer_name"],
            ["uan", "employee_name", "employer_name"],
            ["uan", "employee_name"],
            ["dob", "employee_name"],
            ["dob", "employee_name", "employer_name"],
        ];

        $isValid = false;
        foreach ($validCombos as $combo) {
            $allPresent = true;
            foreach ($combo as $field) {
                if (empty($data[$field])) {
                    $allPresent = false;
                    break;
                }
            }
            if ($allPresent) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            return response()->json([
                'error' => 'Please fill all fields for at least one valid combination (e.g. phone + pan, uan + name, etc).'
            ], 400);
        }

        $payload = [];
        foreach (['phone', 'pan', 'uan', 'dob', 'employee_name', 'employer_name'] as $field) {
            if (!empty($data[$field])) {
                $payload[$field] = $data[$field];
            }
        }
        $payload['verification_id'] = 'EMP_' . uniqid() . '_' . time();

        if (count($payload) <= 1) { // Only verification_id
            return response()->json(['error' => 'At least one employment field is required'], 400);
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post(env('EMPLOYMENT_VERIFICATION_URL'), $payload);
            return $this->deductUserVerifyCredits($response, env('EMPLOYEMENT_VERIFY_COST'));
            // return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleVerificationException($e);
        }
    }

    private function handlePassportVerification($data)
    {
        $passportNumber = strtoupper(trim($data['file_number'] ?? ''));
        if (empty($passportNumber) || empty($data['dob'])) {
            return response()->json(['error' => 'Passport/File number and Date of Birth are required'], 400);
        }


        $payload = [
            'file_number' => $passportNumber,
            'dob' => $data['dob'],
            'name' => $data['name'] ?? null,
            'verification_id' => 'PASS_' . uniqid() . '_' . time(),
        ];


        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post(env('PASSPORT_VERIFICATION_URL'), $payload);

            return $this->deductUserVerifyCredits($response, env('PASSPORT_VERIFY_COST'));
        } catch (Exception $e) {
            return $this->handleVerificationException($e);
        }
    }

    private function handleVehicleRcVerification($data)
    {
        $rcNumber = strtoupper(trim($data['vehicle_number'] ?? ''));
        if (empty($rcNumber)) {
            return response()->json(['error' => 'RC number is required'], 400);
        }

        $payload = [
            'vehicle_number' => $rcNumber,
            'verification_id' => 'VRC_' . uniqid() . '_' . time(),
        ];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post(env('VEHICLE_RC_VERIFICATION_URL'), $payload);
            return $this->deductUserVerifyCredits($response, env('VEHICLERC_VERIFY_COST'));
            // return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleVerificationException($e);
        }
    }

    private function handleIfscVerification($data)
    {
        $ifscCode = strtoupper(trim($data['ifsc'] ?? ''));
        if (empty($ifscCode)) {
            return response()->json(['error' => 'IFSC code is required'], 400);
        }

        $payload = [
            'ifsc' => $ifscCode,
            'verification_id' => 'IFSC_' . uniqid() . '_' . time(),
        ];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post(env('IFSC_VERIFICATION_URL'), $payload);
            return $this->deductUserVerifyCredits($response, env('IFSC_VERIFY_COST'));
            // return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleVerificationException($e);
        }
    }

    private function handleDrivingLicenseVerification($data)
    {
        $licenseNumber = strtoupper(trim($data['dl_number'] ?? ''));
        $dob = $data['dob'];
        if (empty($licenseNumber) || empty($dob)) {
            return response()->json(['error' => 'Driving license number and date of birth both are required'], 400);
        }
        try {
            $dobFormatted = (new \DateTime($dob))->format('Y-m-d');
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid date of birth format. Use YYYY-MM-DD.'], 400);
        }

        $payload = [
            'dl_number' => $licenseNumber,
            'verification_id' => 'DL_' . uniqid() . '_' . time(),
            'dob' => $dobFormatted,
        ];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post(env('DRIVING_LICENSE_VERIFICATION_URL'), $payload);
            return $this->deductUserVerifyCredits($response, env('DRIVING_LICENSE_VERIFY_COST'));
            // return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleVerificationException($e);
        }
    }

    private function handleResponse($response)
    {
        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'data' => $response->json()
            ]);
        }

        $data = $response->json();
        return response()->json([
            'success' => false,
            'message' => 'Verification failed',
            'error' => $data,
            'credits' => $data['credits'] ?? null,
            'status' => $response->status()
        ], $response->status());
    }

    private function handleVerificationException(Exception $e)
    {
        Log::error('Verification error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Server error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
    private function deductUserVerifyCredits($response, float $deduction)
    {
        if ($response->successful()) {
            $data = $response->json();
            $user = auth()->user();

            if ($user && $user->credits >= $deduction) {
                $user->credits -= $deduction;
                $user->save();

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'credits' => $user->credits,
                ]);
            } else {
                return response()->json([
                    'message' => 'Insufficient credits.',
                    'credits' => $user->credits,
                ], 402);
            }
        }

        return $this->handleResponse($response); // fallback for non-200 responses
    }
}
