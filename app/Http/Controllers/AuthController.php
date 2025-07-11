<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

        // Delete old tokens (optional)
        $user->tokens()->delete();

        // Create token
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;

        // Calculate expiration (e.g., 60 mins from now)
        $expiresAt = now()->addMinutes(60);

        // Update token's created_at manually if needed
        $tokenResult->accessToken->created_at = now();
        $tokenResult->accessToken->save();

        return response()->json([
            'token' => $token,
            'user' => $user,
            'expires_at' => $expiresAt->toDateTimeString(),
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
}
