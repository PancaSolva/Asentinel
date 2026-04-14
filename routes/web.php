<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Api\UserController as ApiUserController;
use App\Http\Controllers\Admin\Api\AplikasiController;
use App\Http\Controllers\Admin\Api\ServiceController;
use App\Http\Controllers\Admin\Api\LogMonitorController;
use App\Http\Controllers\Admin\Api\LogAnomaliController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminController::class, 'login']);
Route::get('/logout', [AdminController::class, 'logout'])->name('logout');

Route::middleware([AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);

    // API Endpoints for User Management
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/users', [ApiUserController::class, 'index'])->name('users.index');
        Route::post('/users', [ApiUserController::class, 'store'])->name('users.store'); // Create/Add
        Route::put('/users/{id}', [ApiUserController::class, 'update'])->name('users.update'); // Edit
        Route::delete('/users/{id}', [ApiUserController::class, 'destroy'])->name('users.destroy'); // Delete

        // Aplikasi CRUD
        Route::apiResource('aplikasi', AplikasiController::class);
        // Service CRUD
        Route::apiResource('services', ServiceController::class);
        // Log Monitor CRUD
        Route::apiResource('log-monitor', LogMonitorController::class);
        // Log Anomali CRUD
        Route::apiResource('log-anomali', LogAnomaliController::class);
    });
});
