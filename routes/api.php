<?php

use App\Http\Controllers\Admin\Api\AplikasiController;
use App\Http\Controllers\Admin\Api\ServiceController;
use App\Http\Controllers\Admin\Api\MonitoringController;
use App\Http\Controllers\Admin\Api\LogAnomaliController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\GuestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Api\UserController as ApiUserController;

Route::get('admin/login', function() {
    return redirect('/login');
});

Route::post('admin/login', [AdminController::class, 'login'])->name('login');

// Admin routes (protected by Sanctum auth)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AdminController::class, 'logout']);

    // Shared routes - accessible by both admin and user roles
    Route::get('dashboard-stats', [MonitoringController::class, 'dashboardStats']);
    Route::get('monitoring-logs', [MonitoringController::class, 'monitoringLogs']);
    Route::get('anomali-logs', [LogAnomaliController::class, 'index']);
    Route::get('aplikasi', [AplikasiController::class, 'index']);
    Route::get('aplikasi/{aplikasi}', [AplikasiController::class, 'show']);
    Route::get('services', [ServiceController::class, 'index']);
    Route::get('services/{service}', [ServiceController::class, 'show']);

    // Admin-only routes - require admin role
    Route::middleware('role:admin')->group(function () {
        // Aplikasi CRUD (create, update, delete)
        Route::post('aplikasi', [AplikasiController::class, 'store']);
        Route::put('aplikasi/{aplikasi}', [AplikasiController::class, 'update']);
        Route::delete('aplikasi/{aplikasi}', [AplikasiController::class, 'destroy']);

        // Services CRUD (create, update, delete)
        Route::post('services', [ServiceController::class, 'store']);
        Route::put('services/{service}', [ServiceController::class, 'update']);
        Route::delete('services/{service}', [ServiceController::class, 'destroy']);

        // User Management (admin only)
        Route::get('users', [ApiUserController::class, 'index']);
        Route::post('users', [ApiUserController::class, 'store']);
        Route::put('users/{id}', [ApiUserController::class, 'update']);
        Route::delete('users/{id}', [ApiUserController::class, 'destroy']);

        // Monitoring actions (admin only)
        Route::post('run-monitoring', [MonitoringController::class, 'runCheck']);

        // Guest access routes (admin only)
        Route::post('add-guest', [GuestController::class, 'addGuestAccess']);
        Route::delete('remove-guest', [GuestController::class, 'removeGuestAccess']);
        Route::get('guest-list', [GuestController::class, 'guestAccessList']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
