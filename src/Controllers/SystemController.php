<?php

namespace Js\Authenticator\Controllers;

use Illuminate\Http\Request;
use Js\Authenticator\Services\PermissionService;
use Exception;
use Log;

class SystemController
{
    public function __construct(
        private PermissionService $PermissionService
    ){}

    /**
     * 取得系統需控管權限
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

    /**
     * 儲存前端路由
     * 
     * @param Request $request
     * @return Response
     */
    public function set_data(Request $request)
    {
        try {
            $data = $request->validate([
                'data' => 'required|string'
            ]);

            if (!$this->PermissionService->set_data($data['data'])) {
                throw new Exception('save forestage to cache is fail.');
            }

            return response()->json(
                [
                    'status_code' => 200,
                    'message' => 'ok',
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