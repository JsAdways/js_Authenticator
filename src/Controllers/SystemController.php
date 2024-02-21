<?php

namespace Js\Authenticator\Controllers;

use Illuminate\Http\Request;
use Js\Authenticator\Services\PermissionService;
use Exception;

class SystemController
{
    public function __construct(
        private PermissionService $PermissionService
    ){}

    /**
     * 取得登入資訊
     * 
     * @param Request $request
     * @return Response
     */
    public function get_permission(Request $request)
    {
        try {
            $permission = $this->PermissionService->get();

            return response()->json(
                [
                    'status_code' => 200,
                    'data' => $permission,
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