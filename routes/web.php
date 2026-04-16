<?php

use Illuminate\Support\Facades\Route;

// Biarkan semua rute ditangani oleh React (SPA)
// Kecuali rute API yang sudah didefinisikan di routes/api.php
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
