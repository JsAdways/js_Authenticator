<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Js\Authenticator\Controllers\AuthController;

Route::group(['prefix' => 'api', 'middleware' => 'api'], function() {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/permission', [AuthController::class, 'login_info']);
});