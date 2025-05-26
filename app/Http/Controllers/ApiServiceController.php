<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use HlrLookup\HLRLookupClient;
use Illuminate\Support\Facades\Log;

class ApiServiceController extends Controller
{
    private function sanitizePhoneNumber($number)
    {
        return preg_replace('/\D/', '', $number);
    }


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
            'telegramData' => null,
            'osintData' => null,
        ];
        // 🔹 Telegram Data
        $data['telegramData'] = $this->callApiWithCatch(function () use ($number) {
            return Http::post('http://127.0.0.1:8080/api/telegram/', [
                'phone' => $number
            ])->json();
        }, 'telegram', $number);

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

        // surepass apis
        // $data['surepassKyc'] = $this->callApiWithCatch(function () use ($localNumber) {
        //     return Http::withHeaders([
        //         'Content-Type' => 'application/json',
        //         // 'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
        //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
        //     ])->post('https://kyc-api.surepass.io/api/v1/prefill/prefill-by-mobile', [
        //         'mobile' => $localNumber,
        //     ])->json();
        // }, 'surepassKyc', $localNumber);


        // $data['surepassUpi'] = $this->callApiWithCatch(function () use ($localNumber) {
        //     return Http::withHeaders([
        //         'Content-Type' => 'application/json',
        //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
        //     ])->post('https://kyc-api.surepass.io/api/v1/bank-verification/mobile-to-multiple-upi', [
        //         'mobile_number' => $localNumber,
        //     ])->json();
        // }, 'surepassUpi', $localNumber);


        // $data['surepassBank'] = $this->callApiWithCatch(function () use ($localNumber) {
        //     return Http::withHeaders([
        //         'Content-Type' => 'application/json',
        //         'Authorization' => 'Bearer ' . env('SUREPASS_KYC_TOKEN'),
        //     ])->post('https://kyc-api.surepass.io/api/v1/mobile-to-bank-details/verification', [
        //         'mobile_no' => $localNumber,
        //     ])->json();
        // }, 'surepassBank', $localNumber);

        // log::info( $data['telegramData']);
        return response()->json($data);
    }


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
            'holeheData' => null,
        ];

        // 🔹 Osint API
        $data['osintData'] = $this->callApiWithCatch(function () use ($email) {
            return Http::withHeaders([
                'x-api-key' => env('X_API_KEY'),
            ])->timeout(60)->get("http://127.0.0.1:5000/api/search/", [
                'email' => $email,
                'per_page' => 50,
            ])->json();
        }, 'osint', $email);

        // 🔹 Zehef API
        $data['zehefData'] = $this->callApiWithCatch(function () use ($email) {
            return Http::timeout(60) // ⏱ Set BEFORE the request
                ->post('http://127.0.0.1:8080/api/zehef/', [
                    'email' => $email,
                ])
                ->json();
        }, 'zehef', $email);

        // 🔹 Holehe API
        $data['holeheData'] = $this->callApiWithCatch(function () use ($email) {
            return Http::timeout(60) // ⏱ Set BEFORE the request
                ->post('http://127.0.0.1:8080/api/holehe/', [
                    'email' => $email,
                ])
                ->json();
        }, 'holehe', $email);


        // 🔹 Google Email API
        $data['emailData'] = $this->callApiWithCatch(function () use ($email) {
            return Http::withHeaders([
                'x-rapidapi-key' => env('EMAIL_API_KEY'),
                'x-rapidapi-host' => env('EMAIL_API_HOST'),
            ])->timeout(60)->get("https://google-data.p.rapidapi.com/email/{$email}")->json();
        }, 'google', $email);

        // 🔹 HIBP API
        $data['hibpData'] = $this->callApiWithCatch(function () use ($email) {
            return Http::withHeaders([
                'hibp-api-key' => env('HIBP_API_KEY'),
                'User-Agent' => 'LaravelApp/1.0',
            ])->timeout(60)->get("https://haveibeenpwned.com/api/v3/breachedaccount/{$email}?truncateResponse=false")->json();
        }, 'hibp', $email);

        log::info($data['holeheData']);
        return response()->json($data);
    }


    private function callApiWithCatch(\Closure $callback, string $label, string $input)
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = $callback();

            // Check if the response is a Laravel HTTP client Response object
            if ($response instanceof \Illuminate\Http\Client\Response) {
                $status = $response->status();
                $url = $response->effectiveUri() ?? 'Unknown URL';

                if ($status >= 400) {
                    // Log error for non-successful HTTP codes
                    Log::error(strtoupper($label) . " API Error", [
                        'input' => $input,
                        'url' => $url,
                        'status' => $status,
                        'response' => $response->json(),
                    ]);

                    return [
                        'error' => true,
                        'source' => strtoupper($label),
                        'status' => $status,
                        'message' => $response->json()['message'] ?? 'API returned error',
                    ];
                }

                // Success — return parsed response body
                return $response->json();
            }

            // Fallback if it's already parsed array (not typical, but just in case)
            return $response;
        } catch (\Exception $e) {
            Log::error(strtoupper($label) . " API Exception", [
                'input' => $input,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => true,
                'source' => strtoupper($label),
                'message' => $e->getMessage(),
            ];
        }
    }
}
