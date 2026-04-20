<?php

use App\Http\Controllers\Admin\Api\AplikasiController;
use App\Http\Controllers\Admin\Api\ServiceController;
use App\Http\Controllers\Admin\Api\MonitoringController;
use App\Http\Controllers\Admin\AdminController;
use App\Models\WebGuest;
use App\Http\Controllers\GuestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('admin/login', function() {
    return redirect('/login');
});

Route::post('admin/login', [AdminController::class, 'login'])->name('login');
//admin routes (EDIT WITH CAUTION)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AdminController::class, 'logout']);
    Route::apiResource('aplikasi', AplikasiController::class);
    Route::apiResource('services', ServiceController::class);
    
    //Monitoring routes
    Route::get('dashboard-stats', [MonitoringController::class, 'dashboardStats']);
    Route::get('monitoring-logs', [MonitoringController::class, 'monitoringLogs']);
    Route::post('run-monitoring', [MonitoringController::class, 'runCheck']);

    //Adding guest access routes
    Route::put('add-guest/{id}', [GuestController::class, 'addGuestAccess']);
    Route::delete('remove-guest/{id}', [GuestController::class, 'removeGuestAccess']);
});

Route::get('admin/guest-list', [GuestController::class, 'guestAccessList']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
