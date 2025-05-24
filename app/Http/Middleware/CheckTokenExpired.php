<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CheckTokenExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $expiresAt = $token->expires_at;
            if (Carbon::now()->greaterThan($expiresAt)) {
                return response()->json([
                    'message' => 'token expired',
                    'redirect' => route('login'),
                ], 401);
            }
        }

        return $next($request);
    }

}
