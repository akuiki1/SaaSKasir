<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransaksiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Login berbasis token (Bearer) untuk klien di luar browser (mis. app
// mobile/PWA mendatang). Beda dari login web (session, Fortify).
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', fn (Request $request) => $request->user());

    Route::post('transaksi', [TransaksiController::class, 'store']);
});
