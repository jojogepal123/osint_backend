<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        if (!config('auth.registration_enabled')) {
            return response()->json([
                'message' => 'Registration is currently disabled.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'email_verified_at' => now(),
        ]);



        return response()->json([
            'user' => $user,
            'message' => 'Registration successful. Please Login to continue.'
        ], 201);
    }



    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }


        if (!$user->email_verified_at || ($user->last_otp_verified_at?->diffInHours(now()) ?? 13) >= 12) {

            if (!$user->otp || now()->gt($user->otp_expires_at)) {
                try {
                    $otp = rand(100000, 999999);
                    $user->otp = $otp;
                    $user->otp_expires_at = now()->addMinutes(5);
                    $user->save();

                    Mail::to($user->email)->send(new SendOtpMail($otp));
                    Log::info("OTP sent to " . $user->email);
                } catch (Exception $e) {
                    Log::error("Error in sending email to " . $user->email . $e);
                }
            }
            return response()->json([
                'message' => 'You need to verify your email before logging in.',
                'otp_required' => true,
                'email' => $user->email,
                'otp_expires_at' => $user->otp_expires_at,
            ], 403);
        }

        // Optional: Revoke old tokens
        $user->tokens()->delete();

        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;

        $expiresAt = now()->addMinutes(60);

        // Save updated creation time
        $tokenResult->accessToken->created_at = now();
        $tokenResult->accessToken->save();

        return response()->json([
            'token' => $token,
            'user' => $user,
            'expires_at' => $expiresAt->toISOString(),
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        Mail::to($user->email)->send(new SendOtpMail($otp));

        return response()->json([
            'message' => 'OTP resent sent to ' . $request->email,
            'otp_expires_at' => $user->otp_expires_at,
        ]);
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!$user->otp || $user->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 422); // important
        }

        if (now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired.'], 403);
        }

        $user->email_verified_at = now();
        $user->last_otp_verified_at = now();
        $user->save();

        // Optional: Revoke old tokens
        $user->tokens()->delete();
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;
        $expiresAt = now()->addMinutes(60);
        // Save updated creation time
        $tokenResult->accessToken->created_at = now();
        $tokenResult->accessToken->save();

        return response()->json([
            'token' => $token,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    }


}
