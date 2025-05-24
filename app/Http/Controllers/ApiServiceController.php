<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HlrLookup\HLRLookupClient;
use Illuminate\Support\Facades\Log;

class ApiServiceController extends Controller
{
    private function sanitizePhoneNumber($number)
    {
        return preg_replace('/\D/', '', $number);
    }

    // public function getTelData(Request $request)
    // {
    //     // Validate the 'number' query parameter
    //     $request->validate([
    //         'number' => ['required', 'regex:/^\d{10,15}$/'],
    //     ]);

    //     $number = $request->query('number');
    //     // Sanitize the phone number
    //     $fullPhoneNumber = preg_replace('/\D/', '', $number);

    //     // Remove country code (assuming it's a fixed length like 91 for India)
    //     $localPhoneNumber = preg_replace('/^91/', '', $fullPhoneNumber);

    //     // Initialize response data
    //     $data = [
    //         'whatsappData' => null,
    //         'hlrData' => null,
    //         'truecallerData' => null,
    //         'allMobileData' => null,
    //         'socialMediaData' => null,
    //         'osintData' => null,
    //         // 'surepassKyc' => null, // Surepass KYC API
    //         // 'surepassUpi' => null, // Surepass UPI API
    //         // 'surepassBank' => null, // Surepass Bank API
    //         'errors' => [],
    //     ];

    // // Surepass KYC API (prefill-by-mobile)
    // try {
    //     $surepassKycResponse = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
    //     ])->post('https://kyc-api.surepass.io/api/v1/prefill/prefill-by-mobile', [
    //                 'mobile' => $localPhoneNumber,
    //             ]);

    //     if ($surepassKycResponse->successful()) {
    //         $data['surepassKyc'] = $surepassKycResponse->json();
    //     } else {
    //         $msg = "Surepass KYC API Error: HTTP Status {$surepassKycResponse->status()}";
    //         Log::error($msg, ['number' => $localPhoneNumber]);
    //         $data['errors']['surepassKyc'] = $msg;
    //     }
    // } catch (\Exception $e) {
    //     Log::error("Surepass KYC API Exception: " . $e->getMessage(), ['number' => $localPhoneNumber]);
    //     $data['errors']['surepassKyc'] = "Surepass KYC API Exception: " . $e->getMessage();
    // }

    // // Surepass UPI API (mobile-to-multiple-upi)
    // try {
    //     $surepassUpiResponse = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
    //     ])->post('https://kyc-api.surepass.io/api/v1/bank-verification/mobile-to-multiple-upi', [
    //                 'mobile_number' => $localPhoneNumber,
    //             ]);

    //     if ($surepassUpiResponse->successful()) {
    //         $data['surepassUpi'] = $surepassUpiResponse->json();
    //     } else {
    //         $msg = "Surepass UPI API Error: HTTP Status {$surepassUpiResponse->status()}";
    //         Log::error($msg, ['number' => $localPhoneNumber]);
    //         $data['errors']['surepassUpi'] = $msg;
    //     }
    // } catch (\Exception $e) {
    //     Log::error("Surepass UPI API Exception: " . $e->getMessage(), ['number' => $localPhoneNumber]);
    //     $data['errors']['surepassUpi'] = "Surepass UPI API Exception: " . $e->getMessage();
    // }

    // // Surepass Mobile to Bank Details API
    // try {
    //     $surepassBankResponse = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
    //     ])->post('https://kyc-api.surepass.io/api/v1/mobile-to-bank-details/verification', [
    //                 'mobile_no' => $localPhoneNumber,
    //             ]);

    //     if ($surepassBankResponse->successful()) {
    //         $data['surepassBank'] = $surepassBankResponse->json();
    //     } else {
    //         $msg = "Surepass Bank API Error: HTTP Status {$surepassBankResponse->status()}";
    //         Log::error($msg, ['number' => $localPhoneNumber]);
    //         $data['errors']['surepassBank'] = $msg;
    //     }
    // } catch (\Exception $e) {
    //     Log::error("Surepass Bank API Exception: " . $e->getMessage(), ['number' => $localPhoneNumber]);
    //     $data['errors']['surepassBank'] = "Surepass Bank API Exception: " . $e->getMessage();
    // }

    //     // Fetch Osint data
    //     try {
    //         $osintResponse = Http::withHeaders([
    //             'x-api-key' => env('X_API_KEY')
    //         ])->get('http://127.0.0.1:5000/api/search/', [
    //                     'phone' => $fullPhoneNumber,
    //                     'per_page' => 50
    //                 ]);

    //         if ($osintResponse->successful()) {
    //             $data['osintData'] = $osintResponse->json();
    //         } else {
    //             $msg = "Osint API Error: HTTP Status {$osintResponse->status()}";
    //             Log::error($msg, ['number' => $fullPhoneNumber]);
    //             $data['errors'][] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("Osint API Exception: " . $e->getMessage(), ['number' => $fullPhoneNumber]);
    //         $data['errors'][] = "Osint API Exception: " . $e->getMessage();
    //     }

    //     // Fetch WhatsApp data
    //     try {
    //         $whatsappResponse = Http::withHeaders([
    //             'x-rapidapi-key' => env('TEL_API_KEY'),
    //             'x-rapidapi-host' => env('TEL_API_HOST'),
    //         ])->get("https://whatsapp-data1.p.rapidapi.com/number/{$fullPhoneNumber}");

    //         if ($whatsappResponse->successful()) {
    //             $data['whatsappData'] = $whatsappResponse->json();
    //         } else {
    //             $msg = "WhatsApp API Error: HTTP Status {$whatsappResponse->status()}";
    //             Log::error($msg, ['number' => $fullPhoneNumber]);
    //             $data['errors']['whatsapp'] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("WhatsApp API Exception: " . $e->getMessage(), ['number' => $fullPhoneNumber]);
    //         $data['errors']['whatsapp'] = $e->getMessage();
    //     }

    //     // Fetch HLR data using the SDK
    //     try {
    //         $hlrClient = new HLRLookupClient(
    //             env('HLR_API_KEY'),
    //             env('HLR_API_SECRET'),
    //             storage_path('logs/hlr-lookups.log') // Optional log file
    //         );

    //         $hlrResponse = $hlrClient->post('/hlr-lookup', [
    //             'msisdn' => $fullPhoneNumber,
    //         ]);

    //         if ($hlrResponse->httpStatusCode === 200) {
    //             $data['hlrData'] = $hlrResponse->responseBody;
    //         } else {
    //             $msg = "HLR API Error: HTTP Status {$hlrResponse->httpStatusCode}";
    //             Log::error($msg, ['number' => $fullPhoneNumber]);
    //             $data['errors']['hlr'] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("HLR API Exception: " . $e->getMessage(), ['number' => $fullPhoneNumber]);
    //         $data['errors']['hlr'] = "Exception: " . $e->getMessage();
    //     }

    //     // Fetch Truecaller data
    //     try {
    //         $truecallerResponse = Http::withHeaders([
    //             'x-rapidapi-key' => env('TRUECALLER_API_KEY'),
    //             'x-rapidapi-host' => env('TRUECALLER_API_HOST'),
    //         ])->get("https://truecaller-data2.p.rapidapi.com/search/{$fullPhoneNumber}");

    //         if ($truecallerResponse->successful()) {
    //             $data['truecallerData'] = $truecallerResponse->json();
    //         } else {
    //             $msg = "Truecaller API Error: HTTP Status {$truecallerResponse->status()}";
    //             Log::error($msg, ['number' => $fullPhoneNumber]);
    //             $data['errors']['truecaller'] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("Truecaller API Exception: " . $e->getMessage(), ['number' => $fullPhoneNumber]);
    //         $data['errors']['truecaller'] = $e->getMessage();
    //     }

    //     // Fetch all Mobile Data (Callerapi,eyecon,truecaller,viewcaller etc)
    //     try {
    //         $allMobileDataResponse = Http::withHeaders([
    //             'x-rapidapi-host' => env('ALL_MOBILE_API_HOST'),
    //             'x-rapidapi-key' => env('ALL_MOBILE_API_KEY'),
    //         ])->get("https://caller-id-api1.p.rapidapi.com/api/phone/info/{$fullPhoneNumber}");

    //         if ($allMobileDataResponse->successful()) {
    //             $data['allMobileData'] = $allMobileDataResponse->json();
    //         } else {
    //             $msg = "All Mobile Data API Error: HTTP Status {$allMobileDataResponse->status()}";
    //             Log::error($msg, ['number' => $fullPhoneNumber]);
    //             $data['errors']['allMobile'] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("All Mobile Data API Exception: " . $e->getMessage(), ['number' => $fullPhoneNumber]);
    //         $data['errors']['allMobile'] = "All Mobile Data API Exception: " . $e->getMessage();
    //     }

    //     // Fetch social media data
    //     try {
    //         $socialMediaResponse = Http::withHeaders([
    //             'x-rapidapi-key' => env('SOCIAL_MEDIA_API_KEY'),
    //             'x-rapidapi-host' => env('SOCIAL_MEDIA_API_HOST'),
    //         ])->get("https://caller-id-social-search-eyecon.p.rapidapi.com/?phone={$fullPhoneNumber}");

    //         if ($socialMediaResponse->successful()) {
    //             $data['socialMediaData'] = $socialMediaResponse->json();
    //         } else {
    //             $msg = "Social Media Data API Error: HTTP Status {$socialMediaResponse->status()}";
    //             Log::error($msg, ['number' => $fullPhoneNumber]);
    //             $data['errors']['socialMedia'] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("Social Media Data API Exception: " . $e->getMessage(), ['number' => $fullPhoneNumber]);
    //         $data['errors']['socialMedia'] = "Social Media Data API Exception: " . $e->getMessage();
    //     }

    //     return response()->json($data);
    // }


    public function getTelData(Request $request)
    {
        // Validate the 'number' query parameter
        $request->validate([
            'number' => ['required', 'regex:/^\d{10,15}$/'],
        ]);

        $number = preg_replace('/\D/', '', $request->query('number'));
        $localNumber = preg_replace('/^91/', '', $number); // for India

        $data = [
            'whatsappData' => null,
            'hlrData' => null,
            'truecallerData' => null,
            'allMobileData' => null,
            'socialMediaData' => null,
            // 'surepassKyc' => null, // Surepass KYC API
            // 'surepassUpi' => null, // Surepass UPI API
            // 'surepassBank' => null, // Surepass Bank API
            'osintData' => null,
        ];

        // 🔹 OSINT Data
        $data['osintData'] = $this->callApiWithCatch(function () use ($number) {
            return Http::withHeaders([
                'x-api-key' => env('X_API_KEY'),
            ])->get('http://127.0.0.1:5000/api/search/', [
                        'phone' => $number,
                        'per_page' => 50,
                    ])->json();
        }, 'osint', $number);

        // 🔹 WhatsApp Data
        $data['whatsappData'] = $this->callApiWithCatch(function () use ($number) {
            return Http::withHeaders([
                'x-rapidapi-key' => env('TEL_API_KEY'),
                'x-rapidapi-host' => env('TEL_API_HOST'),
            ])->get("https://whatsapp-data1.p.rapidapi.com/number/{$number}")->json();
        }, 'whatsapp', $number);

        // 🔹 HLR Lookup
        $data['hlrData'] = $this->callApiWithCatch(function () use ($number) {
            $client = new HLRLookupClient(
                env('HLR_API_KEY'),
                env('HLR_API_SECRET'),
                storage_path('logs/hlr-lookups.log')
            );

            $response = $client->post('/hlr-lookup', ['msisdn' => $number]);

            if ($response->httpStatusCode === 200) {
                return $response->responseBody;
            }

            throw new \Exception("HLR API HTTP Status: {$response->httpStatusCode}");
        }, 'hlr', $number);

        // 🔹 Truecaller Data
        $data['truecallerData'] = $this->callApiWithCatch(function () use ($number) {
            return Http::withHeaders([
                'x-rapidapi-key' => env('TRUECALLER_API_KEY'),
                'x-rapidapi-host' => env('TRUECALLER_API_HOST'),
            ])->get("https://truecaller-data2.p.rapidapi.com/search/{$number}")->json();
        }, 'truecaller', $number);

        // 🔹 All Mobile Data
        $data['allMobileData'] = $this->callApiWithCatch(function () use ($number) {
            return Http::withHeaders([
                'x-rapidapi-host' => env('ALL_MOBILE_API_HOST'),
                'x-rapidapi-key' => env('ALL_MOBILE_API_KEY'),
            ])->get("https://caller-id-api1.p.rapidapi.com/api/phone/info/{$number}")->json();
        }, 'allMobile', $number);

        // 🔹 Social Media Data
        $data['socialMediaData'] = $this->callApiWithCatch(function () use ($number) {
            return Http::withHeaders([
                'x-rapidapi-key' => env('SOCIAL_MEDIA_API_KEY'),
                'x-rapidapi-host' => env('SOCIAL_MEDIA_API_HOST'),
            ])->get("https://caller-id-social-search-eyecon.p.rapidapi.com/?phone={$number}")->json();
        }, 'socialMedia', $number);

        // // 🔹 Surepass KYC API
        // $data['surepassKyc'] = $this->callApiWithCatch(function () use ($localNumber) {
        //     return Http::withHeaders([
        //         'Content-Type' => 'application/json',
        //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
        //     ])->post('https://kyc-api.surepass.io/api/v1/prefill/prefill-by-mobile', [
        //                 'mobile' => $localNumber,
        //             ])->json();
        // }, 'surepassKyc', $localNumber);

        // // 🔹 Surepass UPI API
        // $data['surepassUpi'] = $this->callApiWithCatch(function () use ($localNumber) {
        //     return Http::withHeaders([
        //         'Content-Type' => 'application/json',
        //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
        //     ])->post('https://kyc-api.surepass.io/api/v1/bank-verification/mobile-to-multiple-upi', [
        //                 'mobile_number' => $localNumber,
        //             ])->json();
        // }, 'surepassUpi', $localNumber);

        // // 🔹 Surepass Bank Details API
        // $data['surepassBank'] = $this->callApiWithCatch(function () use ($localNumber) {
        //     return Http::withHeaders([
        //         'Content-Type' => 'application/json',
        //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
        //     ])->post('https://kyc-api.surepass.io/api/v1/mobile-to-bank-details/verification', [
        //                 'mobile_no' => $localNumber,
        //             ])->json();
        // }, 'surepassBank', $localNumber);

        return response()->json($data);
    }


    // public function getTelData(Request $request)
    // {
    //     $request->validate([
    //         'number' => ['required', 'regex:/^\d{10,15}$/'],
    //     ]);

    //     $number = preg_replace('/\D/', '', $request->query('number'));
    //     $localNumber = preg_replace('/^91/', '', $number); // for India

    //     $data = [
    //         'whatsappData' => null,
    //         'hlrData' => null,
    //         'truecallerData' => null,
    //         'allMobileData' => null,
    //         'socialMediaData' => null,
    //         'surepassKyc' => null,
    //         'surepassUpi' => null,
    //         'surepassBank' => null,
    //         'osintData' => null,
    //     ];

    //     // 🔹 OSINT Data
    //     $data['osintData'] = $this->callApiWithCatch(function () use ($number) {
    //         return Http::withHeaders([
    //             'x-api-key' => env('X_API_KEY'),
    //         ])->get('http://127.0.0.1:5000/api/search/', [
    //                     'phone' => $number,
    //                     'per_page' => 50,
    //                 ])->json();
    //     }, 'osint', $number);

    //     // 🔹 WhatsApp Data
    //     $data['whatsappData'] = $this->callApiWithCatch(function () use ($number) {
    //         return Http::withHeaders([
    //             'x-rapidapi-key' => env('TEL_API_KEY'),
    //             'x-rapidapi-host' => env('TEL_API_HOST'),
    //         ])->get("https://whatsapp-data1.p.rapidapi.com/number/{$number}")->json();
    //     }, 'whatsapp', $number);

    //     // 🔹 HLR Lookup
    //     $data['hlrData'] = $this->callApiWithCatch(function () use ($number) {
    //         $client = new HLRLookupClient(
    //             env('HLR_API_KEY'),
    //             env('HLR_API_SECRET'),
    //             storage_path('logs/hlr-lookups.log')
    //         );
    //         $response = $client->post('/hlr-lookup', ['msisdn' => $number]);
    //         if ($response->httpStatusCode === 200) {
    //             return $response->responseBody;
    //         }
    //         throw new \Exception("HLR API HTTP Status: {$response->httpStatusCode}");
    //     }, 'hlr', $number);

    //     // 🔹 Truecaller Data
    //     $data['truecallerData'] = $this->callApiWithCatch(function () use ($number) {
    //         return Http::withHeaders([
    //             'x-rapidapi-key' => env('TRUECALLER_API_KEY'),
    //             'x-rapidapi-host' => env('TRUECALLER_API_HOST'),
    //         ])->get("https://truecaller-data2.p.rapidapi.com/search/{$number}")->json();
    //     }, 'truecaller', $number);

    //     // 🔹 All Mobile Data
    //     $data['allMobileData'] = $this->callApiWithCatch(function () use ($number) {
    //         return Http::withHeaders([
    //             'x-rapidapi-host' => env('ALL_MOBILE_API_HOST'),
    //             'x-rapidapi-key' => env('ALL_MOBILE_API_KEY'),
    //         ])->get("https://caller-id-api1.p.rapidapi.com/api/phone/info/{$number}")->json();
    //     }, 'allMobile', $number);

    //     // 🔹 Social Media Data
    //     $data['socialMediaData'] = $this->callApiWithCatch(function () use ($number) {
    //         return Http::withHeaders([
    //             'x-rapidapi-key' => env('SOCIAL_MEDIA_API_KEY'),
    //             'x-rapidapi-host' => env('SOCIAL_MEDIA_API_HOST'),
    //         ])->get("https://caller-id-social-search-eyecon.p.rapidapi.com/?phone={$number}")->json();
    //     }, 'socialMedia', $number);

    // // 🔹 Surepass KYC API
    // $data['surepassKyc'] = $this->callApiWithCatch(function () use ($localNumber) {
    //     return Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
    //     ])->post('https://kyc-api.surepass.io/api/v1/prefill/prefill-by-mobile', [
    //                 'mobile' => $localNumber,
    //             ])->json();
    // }, 'surepassKyc', $localNumber);

    // // 🔹 Surepass UPI API
    // $data['surepassUpi'] = $this->callApiWithCatch(function () use ($localNumber) {
    //     return Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
    //     ])->post('https://kyc-api.surepass.io/api/v1/bank-verification/mobile-to-multiple-upi', [
    //                 'mobile_number' => $localNumber,
    //             ])->json();
    // }, 'surepassUpi', $localNumber);

    // // 🔹 Surepass Bank Details API
    // $data['surepassBank'] = $this->callApiWithCatch(function () use ($localNumber) {
    //     return Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
    //     ])->post('https://kyc-api.surepass.io/api/v1/mobile-to-bank-details/verification', [
    //                 'mobile_no' => $localNumber,
    //             ])->json();
    // }, 'surepassBank', $localNumber);

    //     return response()->json($data);
    // }

    // public function getEmailData(Request $request)
    // {
    //     // Validate the 'email' query parameter using Laravel's validator
    //     $request->validate([
    //         'email' => ['required', 'email', 'max:255'],
    //     ]);

    //     $email = $request->query('email');

    //     $responses = [
    //         'emailData' => null,
    //         'hibpData' => null,
    //         'zehefData' => null,
    //         'osintData' => null,
    //         'errors' => []
    //     ];

    //     // 🔹 Fetch Osint Data
    //     try {
    //         $osintResponse = Http::withHeaders([
    //             'x-api-key' => env('X_API_KEY'),
    //         ])->timeout(10)->get("http://127.0.0.1:5000/api/search/", [
    //             'email' => $email,
    //             'per_page' => 50
    //         ]);

    //         if ($osintResponse->successful()) {
    //             $responses['osintData'] = $osintResponse->json();
    //         } else {
    //             $msg = "Custom Search API Error: HTTP Status {$osintResponse->status()}";
    //             Log::error($msg, ['email' => $email]);
    //             $responses['errors'][] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("Custom Search API Exception: " . $e->getMessage(), ['email' => $email]);
    //         $responses['errors'][] = "Custom Search API Exception: " . $e->getMessage();
    //     }

    //     // 🔹 Fetch zehef Data
    //     try {
    //         $zehefResponse = Http::post('http://127.0.0.1:8080/search/zehef/', [
    //             'query' => $email
    //         ]);

    //         if ($zehefResponse->successful()) {
    //             $responses['zehefData'] = $zehefResponse->json();
    //         } else {
    //             $msg = "Zehef API Error: HTTP Status {$zehefResponse->status()}";
    //             Log::error($msg, ['email' => $email]);
    //             $responses['errors'][] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("Zehef API Exception: " . $e->getMessage(), ['email' => $email]);
    //         $responses['errors'][] = "Zehef API Exception: " . $e->getMessage();
    //     }

    //     // 🔹 Fetch Google Email Data
    //     try {
    //         $googleResponse = Http::withHeaders([
    //             'x-rapidapi-key' => env('EMAIL_API_KEY'),
    //             'x-rapidapi-host' => env('EMAIL_API_HOST'),
    //         ])->timeout(10)->get("https://google-data.p.rapidapi.com/email/{$email}");

    //         if ($googleResponse->successful()) {
    //             $responses['emailData'] = $googleResponse->json();
    //         } else {
    //             $msg = 'Google API failed';
    //             Log::error($msg, ['email' => $email, 'status' => $googleResponse->status()]);
    //             $responses['errors'][] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("Google API Exception: " . $e->getMessage(), ['email' => $email]);
    //         $responses['errors'][] = 'Google API error';
    //     }

    //     // 🔹 Fetch HIBP Data
    //     try {
    //         $hibpResponse = Http::withHeaders([
    //             'hibp-api-key' => env('HIBP_API_KEY'),
    //             'User-Agent' => 'LaravelApp/1.0',
    //         ])->timeout(10)->get("https://haveibeenpwned.com/api/v3/breachedaccount/{$email}?truncateResponse=false");

    //         if ($hibpResponse->successful()) {
    //             $responses['hibpData'] = $hibpResponse->json();
    //         } else {
    //             $msg = 'HIBP API failed';
    //             Log::error($msg, ['email' => $email, 'status' => $hibpResponse->status()]);
    //             $responses['errors'][] = $msg;
    //         }
    //     } catch (\Exception $e) {
    //         Log::error("HIBP API Exception: " . $e->getMessage(), ['email' => $email]);
    //         $responses['errors'][] = 'HIBP API error';
    //     }

    //     return response()->json($responses);
    // }

    public function getEmailData(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = $request->query('email');

        $data = [
            'emailData' => null,
            'hibpData' => null,
            'zehefData' => null,
            'osintData' => null,
        ];

        // 🔹 Osint API
        $data['osintData'] = $this->callApiWithCatch(function () use ($email) {
            return Http::withHeaders([
                'x-api-key' => env('X_API_KEY'),
            ])->timeout(10)->get("http://127.0.0.1:5000/api/search/", [
                        'email' => $email,
                        'per_page' => 50,
                    ])->json();
        }, 'osint', $email);

        // 🔹 Zehef API
        $data['zehefData'] = $this->callApiWithCatch(function () use ($email) {
            return Http::post('http://127.0.0.1:8080/search/zehef/', [
                'query' => $email,
            ])->json();
        }, 'zehef', $email);

        // 🔹 Google Email API
        $data['emailData'] = $this->callApiWithCatch(function () use ($email) {
            return Http::withHeaders([
                'x-rapidapi-key' => env('EMAIL_API_KEY'),
                'x-rapidapi-host' => env('EMAIL_API_HOST'),
            ])->timeout(10)->get("https://google-data.p.rapidapi.com/email/{$email}")->json();
        }, 'google', $email);

        // 🔹 HIBP API
        $data['hibpData'] = $this->callApiWithCatch(function () use ($email) {
            return Http::withHeaders([
                'hibp-api-key' => env('HIBP_API_KEY'),
                'User-Agent' => 'LaravelApp/1.0',
            ])->timeout(10)->get("https://haveibeenpwned.com/api/v3/breachedaccount/{$email}?truncateResponse=false")->json();
        }, 'hibp', $email);

        return response()->json($data);
    }


    private function callApiWithCatch(\Closure $callback, string $label, string $input)
    {
        try {
            $response = $callback();

            if (is_array($response) && isset($response['message'])) {
                Log::warning(strtoupper($label) . " API Response Warning: " . $response['message'], [
                    'input' => $input,
                ]);
                return null;
            }

            return $response;
        } catch (\Exception $e) {
            Log::error(strtoupper($label) . " API Exception: " . $e->getMessage(), [
                'input' => $input,
            ]);
            return null;
        }
    }
}
