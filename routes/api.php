<?php

use App\Http\Controllers\Admin\Api\AplikasiController;
use App\Http\Controllers\Admin\Api\ServiceController;
use App\Http\Controllers\Admin\Api\MonitoringController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('admin/login', [AdminController::class, 'login']);

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AdminController::class, 'logout']);
    Route::apiResource('aplikasi', AplikasiController::class);
    Route::apiResource('services', ServiceController::class);
    
    Route::get('dashboard-stats', [MonitoringController::class, 'dashboardStats']);
    Route::get('monitoring-logs', [MonitoringController::class, 'monitoringLogs']);
    Route::post('run-monitoring', [MonitoringController::class, 'runCheck']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
