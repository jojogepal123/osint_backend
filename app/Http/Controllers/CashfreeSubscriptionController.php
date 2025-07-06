<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CashfreeSubscriptionController extends Controller
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $webhookSecret;

    public function __construct()
    {
        $this->clientId = config('services.cashfree.client_id');
        $this->clientSecret = config('services.cashfree.client_secret');
        $this->webhookSecret = config('services.cashfree.webhook_secret');
        $this->baseUrl = config('services.cashfree.environment') === 'production'
            ? 'https://api.cashfree.com'
            : 'https://sandbox.cashfree.com';
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $orderId = 'order_' . uniqid();

        $response = Http::withHeaders([
            'x-client-id' => $this->clientId,
            'x-client-secret' => $this->clientSecret,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/pg/orders", [
                    'order_id' => $orderId,
                    'order_amount' => $request->amount,
                    'order_currency' => 'INR',
                    'customer_details' => [
                        'customer_id' => $user->id,
                        'customer_email' => $user->email,
                        'customer_phone' => $user->phone ?? '9999999999',
                    ],
                    'order_meta' => [
                        'return_url' => url('/payment-success') . '?order_id={order_id}',
                        'notify_url' => url('/api/cashfree/webhook'),
                    ]
                ]);

        if ($response->successful()) {
            return response()->json([
                'order_id' => $orderId,
                'payment_session_id' => $response['payment_session_id'],
            ]);
        }

        return response()->json([
            'message' => 'Cashfree order creation failed',
            'details' => $response->json(),
        ], $response->status());
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('x-webhook-signature');
        $secret = config('services.cashfree.webhook_secret'); // from .env
        $payload = $request->getContent();

        $expected = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expected, $signature)) {
            Log::warning('Invalid webhook signature.', ['signature' => $signature]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // ✅ Signature valid, process the webhook
        $data = $request->all();
        Log::info('Cashfree Webhook Received:', $data);

        // Example: activate subscription if ORDER.PAID
        if (($data['event'] ?? '') === 'ORDER.PAID') {
            // Update order status in DB
        }

        return response()->json(['status' => 'Webhook processed']);
    }

}
