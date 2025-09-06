<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
