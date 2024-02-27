<?php

namespace Js\Authenticator\Controllers;

use Illuminate\Http\Request;
use Js\Authenticator\Services\AuthService;
use Exception;
use Log;
class AuthController
{
    public function __construct(
        private AuthService $AuthService
    ){}

    /**
     * 登入驗證
     * 
     * @param Request $request
     * @return Response
     */
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
                    'token' => $login_data['token'],
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

    /**
     * 取得登入資訊
     * 
     * @param Request $request
     * @return Response
     */
    public function login_info(int $id,Request $request)
    {
        try {
            $token = $request->bearerToken();
            $system_id = $id;
            $request = new Request();
            $request->replace([
                'token' => $token,
                'system_id' => $system_id
            ]);

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

    /**
     * 登出
     * 
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $logout = $this->AuthService->logout($token);

            return response()->json(
                [
                    'status_code' => 200,
                    'message' => 'ok'
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