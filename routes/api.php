<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UploadfileController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('upload-avatar', [UserController::class, 'uploadMyAvatar']);
    Route::delete('delete-avatar', [UserController::class, 'deleteMyAvatar']);

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('names', [UserController::class, 'names']);
        Route::get('{user}', [UserController::class, 'show'])->whereUuid('user');
        Route::post('/', [UserController::class, 'store']);
        Route::post('{user}', [UserController::class, 'update'])->whereUuid('user');
        Route::delete('{user}', [UserController::class, 'destroy'])->whereUuid('user');
        Route::post('{user}/upload-avatar', [UserController::class, 'uploadAvatar'])->whereUuid('user');
        Route::delete('{user}/delete-avatar', [UserController::class, 'deleteAvatar'])->whereUuid('user');
    });

    Route::prefix('uploadfiles')->group(function () {
        Route::get('temporary-upload-url', [UploadfileController::class, 'temporaryUploadUrl']);
        Route::post('/', [UploadfileController::class, 'store']);
    });
});
