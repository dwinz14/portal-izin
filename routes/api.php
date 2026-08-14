<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\LeaveApiController;

Route::prefix('v1')->group(function () {
    Route::get('/cuti', [LeaveApiController::class, 'index']);
    Route::get('/cuti/{leave}', [LeaveApiController::class, 'show']);
    Route::get('/cuti/user/{userId}', [LeaveApiController::class, 'byUser']);
});
