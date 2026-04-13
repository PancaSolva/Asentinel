<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Models\User;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return view('welcome');
});

// Dummy Admin Login
Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminController::class, 'login']);
Route::get('/logout', [AdminController::class, 'logout']);

// Admin routes - protected by middleware later
Route::middleware('admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard']);
    Route::resource('/admin/users', AdminController::class);
});

