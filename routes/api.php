<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Js\Authenticator\Controllers\AuthController;
use Js\Authenticator\Controllers\SystemController;

Route::group(['prefix' => 'api', 'middleware' => 'api'], function() {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/permission', [AuthController::class, 'login_info']);
    Route::get('/system/permission', [SystemController::class, 'get_permission']);
});