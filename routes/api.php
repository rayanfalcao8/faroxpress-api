<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\TransferPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:api')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/transfers', [TransferController::class, 'index']);
    Route::post('/transfers', [TransferController::class, 'store']);
    Route::get('/transfers/{transfer}', [TransferController::class, 'show']);

    Route::post('/transfers/{transfer}/mark-paid', [TransferPaymentController::class, 'markPaid'])
        ->middleware('role:admin,operator');

    Route::post('/transfers/{transfer}/retry', [TransferPaymentController::class, 'retry'])
        ->middleware('role:admin,operator');
});
