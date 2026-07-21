<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BearerTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');
        
        if (!preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return response()->json(['message' => 'Unauthenticated. Bearer token missing.'], 401);
        }

        $token = $matches[1];
        $tokenModel = ApiToken::where('token', hash('sha256', $token))->first();

        if (!$tokenModel || ($tokenModel->expires_at && $tokenModel->expires_at->isPast())) {
            return response()->json(['message' => 'Unauthenticated. Invalid or expired token.'], 401);
        }

        // Authenticate user for the current request context
        Auth::loginUsingId($tokenModel->user_id);
        
        $tokenModel->update(['last_used_at' => now()]);

        return $next($request);
    }
}
