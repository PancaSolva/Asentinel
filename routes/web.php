<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Api\AplikasiController;
use App\Http\Controllers\Admin\Api\ServiceController;
use App\Http\Controllers\Admin\Api\LogMonitorController;
use App\Http\Controllers\Admin\Api\LogAnomaliController;
use App\Http\Controllers\Api\api as PinApiController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('/pin', [PinApiController::class, 'index']);
    Route::post('/pin', [PinApiController::class, 'store']);
    Route::get('/pin/{id}', [PinApiController::class, 'show']);
    Route::put('/pin/{id}', [PinApiController::class, 'update']);
    Route::delete('/pin/{id}', [PinApiController::class, 'destroy']);
});

Route::post('/login', [AdminController::class, 'login']);
Route::get('/logout', [AdminController::class, 'logout'])->name('logout');

Route::middleware([AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    Route::prefix('api')->name('api.')->group(function () {
        Route::apiResource('aplikasi', AplikasiController::class);
        Route::apiResource('services', ServiceController::class);
        Route::apiResource('log-monitor', LogMonitorController::class);
        Route::apiResource('log-anomali', LogAnomaliController::class);
    });
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!admin|api|logout|health|sanctum|storage).*$');
