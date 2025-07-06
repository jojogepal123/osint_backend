<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class ExpireSanctumToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken) {
                $minutesToExpire = 60;
                $secondsToExpire = $minutesToExpire * 60;

                // Log token creation and current time for debugging
                Log::info('Sanctum Token Check', [
                    'token_id' => $accessToken->id,
                    'created_at' => $accessToken->created_at->toDateTimeString(),
                    'now' => now()->toDateTimeString(),
                    'diff_seconds' => $accessToken->created_at->diffInSeconds(now()),
                    'expires_in_seconds' => $secondsToExpire,
                ]);

                if ($accessToken->created_at->diffInSeconds(now()) >= $secondsToExpire) {
                    Log::warning('Sanctum token expired and deleted', [
                        'token_id' => $accessToken->id,
                    ]);

                    $accessToken->delete(); // revoke expired token
                    return response()->json(['message' => 'Token expired'], 401);
                }
            } else {
                Log::warning('Sanctum token not found in DB or invalid');
            }
        }

        return $next($request);
    }
}
