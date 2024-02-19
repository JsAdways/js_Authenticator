<?php

namespace Js\Authenticator\Controllers;

use Illuminate\Http\Request;
use Js\Authenticator\Services\AuthService;
use Exception;

class AuthController
{
    public function __construct(
        private AuthService $AuthService
    ){}

    public function login(Request $request)
    {
        try {
            ['account' => $account, 'password' => $password] = $request->validate([
                'account' => 'required|string',
                'password' => 'required|string',
            ]);

            $login_data = $this->AuthService->login($account, $password);

            return response()->json(
                [
                    'status_code' => 200,
                    'data' => $login_data,
                ],
                200
            );
        } catch (Exception $e) {
            Log::notice('js-auth: '.$e->getMessage());
            return response()->json(
                [
                    'status_code' => 400,
                    'message' => $e->getMessage()
                ],
                400
            );
        }
    }

    public function login_info(Request $request)
    {
        try {
            ['token' => $token, 'system_id' => $system_id] = $request->validate([
                'token' => 'required|string',
                'system_id' => 'required|int',
            ]);

            $login_data = $this->AuthService->get_permission($token, $system_id);

            return response()->json(
                [
                    'status_code' => 200,
                    'data' => $login_data,
                ],
                200
            );
        } catch (Exception $e) {
            Log::notice('js-auth: '.$e->getMessage());
            return response()->json(
                [
                    'status_code' => 400,
                    'message' => $e->getMessage()
                ],
                400
            );
        }
    }
}