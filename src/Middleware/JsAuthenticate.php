<?php

namespace Js\Authenticator\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Js\Authenticator\Services\AuthService;
use Exception;
use Log;

class JsAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $AuthService = new AuthService();
            $token = $request->bearerToken();
            $id = $AuthService->verify_token($token);
            $request->merge(['user_id' => $id]);
            return $next($request);
        } catch(Exception $e) {
            Log::notice('js-auth: '.$e->getMessage());
            return response()->json(
                [
                    'status_code' => 401,
                    'message' => 'Unauthorized'
                ],
                401
            );
        }
    }
}
