<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VerificationController;
use App\Models\Task;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh')->middleware('auth:api');
});

Route::apiResource('tasks', TaskController::class);
Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
Route::get('tasks/{task}/file', [TaskController::class, 'downloadFile'])->name('tasks.file');

Route::apiResource('users', UserController::class)->except(['store']);

Route::prefix('email')->group(function () {
    Route::get('verify/{id}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::get('resend', [VerificationController::class, 'resend'])->name('verification.resend');
});
