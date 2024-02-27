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
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $AuthService = new AuthService();
            $token = $request->bearerToken();
            if (is_null($token)) {
                throw new Exception('token is null');
            }
            $AuthService->verify_token($token);
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
