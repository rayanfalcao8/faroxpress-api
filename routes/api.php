<?php

use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Backoffice\PingController;
use App\Http\Controllers\Api\Transfer\CancelTransferController;
use App\Http\Controllers\Api\Transfer\ListTransfersController;
use App\Http\Controllers\Api\Transfer\ShowTransferController;
use App\Http\Controllers\Api\Transfer\StoreTransferController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);
    Route::post('/forgot-password', ForgotPasswordController::class);
    Route::post('/reset-password', ResetPasswordController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', MeController::class);
        Route::post('/logout', LogoutController::class);
    });
});

Route::prefix('backoffice')
    ->middleware(['auth:sanctum', 'role:OPERATOR,ADMIN'])
    ->group(function () {
        Route::get('/ping', PingController::class);
    });

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transfers', StoreTransferController::class);
    Route::get('/transfers', ListTransfersController::class);
    Route::get('/transfers/{transfer}', ShowTransferController::class);
    Route::post('/transfers/{transfer}/cancel', CancelTransferController::class);
});
