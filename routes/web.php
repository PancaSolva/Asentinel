<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Api\AplikasiController;
use App\Http\Controllers\Admin\Api\ServiceController;
use App\Http\Controllers\Admin\Api\LogMonitorController;
use App\Http\Controllers\Admin\Api\LogAnomaliController;
use App\Http\Controllers\Admin\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\PinController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('/pin', [PinController::class, 'index']);
    Route::post('/pin', [PinController::class, 'store']);
    Route::get('/pin/{id}', [PinController::class, 'show']);
    Route::put('/pin/{id}', [PinController::class, 'update']);
    Route::delete('/pin/{id}', [PinController::class, 'destroy']);
});

Route::post('/login', [AdminController::class, 'login']);
Route::get('/logout', [AdminController::class, 'logout'])->name('logout');

// Admin web routes (session-based, for Blade views if any)
Route::middleware([AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    Route::prefix('api')->name('api.')->group(function () {
        Route::apiResource('aplikasi', AplikasiController::class);
        Route::apiResource('services', ServiceController::class);
        Route::apiResource('log-monitor', LogMonitorController::class);
        Route::apiResource('log-anomali', LogAnomaliController::class);

        // API Endpoints for User Management (used by SPA via session)
        Route::get('/users', [ApiUserController::class, 'index'])->name('users.index');
        Route::post('/users', [ApiUserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [ApiUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [ApiUserController::class, 'destroy'])->name('users.destroy');
    });
});

// Let React SPA handle all other routes
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!admin|api|logout|health|sanctum|storage).*$');
