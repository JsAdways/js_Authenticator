<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Js\Authenticator\Controllers\AuthController;
use Js\Authenticator\Controllers\SystemController;

Route::group(['prefix' => 'api/js_auth', 'middleware' => 'api'], function() {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/permission/{id}', [AuthController::class, 'login_info']);
    Route::post('/set_permission', [AuthController::class, 'set_permission']);
    Route::middleware(['js-authenticate-middleware-alias'])->group(function () {
        Route::delete('/permission', [AuthController::class, 'clear_permission_cache']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    Route::post('/system/permission', [SystemController::class, 'get_permission']);
    Route::post('/system/struct', [SystemController::class, 'set_data']);
});
