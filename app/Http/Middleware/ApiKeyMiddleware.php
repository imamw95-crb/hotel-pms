<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request using API Key authentication.
     *
     * API Key dikirim via header: X-API-Key
     * atau via query parameter: ?api_key=xxx
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (! $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API Key tidak disertakan. Kirim via header X-API-Key atau query parameter ?api_key=',
            ], 401);
        }

        // Sanctum token format: {id}|{secret}
        // The stored hash is only for the {secret} part
        // Support both plain secret (legacy) and id|secret format
        if (str_contains($apiKey, '|')) {
            $parts = explode('|', $apiKey, 2);
            $secret = $parts[1];
        } else {
            $secret = $apiKey;
        }

        // Cari user yang punya token dengan nama 'api-key' dan token cocok
        $user = User::whereHas('tokens', function ($q) use ($secret) {
            $q->where('name', 'api-key')
                ->where('token', hash('sha256', $secret));
        })->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'API Key tidak valid.',
            ], 401);
        }

        // Login user untuk request ini
        auth()->setUser($user);

        return $next($request);
    }
}
