<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SistemAbsenController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\DepartementController;
use App\Http\Controllers\Api\AuthSistemController;


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

Route::prefix('absensi')->group(function () {
    Route::get('/search-perusahaan', [SistemAbsenController::class, 'searchPerusahaan']);
    Route::get('/search-employee', [SistemAbsenController::class, 'searchEmployee']);
    Route::post('/absen/masuk', [SistemAbsenController::class, 'clockIn']);
    Route::post('/absen/keluar', [SistemAbsenController::class, 'clockOut']);
    Route::get('/absen/status', [SistemAbsenController::class, 'getAttendanceStatus']);
});
Route::post('/register', [AuthSistemController::class, 'register']);
Route::post('/login', [AuthSistemController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthSistemController::class, 'logout']);
    Route::get('/dashboard', [AuthSistemController::class, 'dashboard']);
    Route::get('/profile', [AuthSistemController::class, 'Profile']);
    Route::post('/profile/update', [AuthSistemController::class, 'ProfileUpdate']);

    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::get('/{id}', [EmployeeController::class, 'show']);
        Route::post('/store', [EmployeeController::class, 'store']);
        Route::put('/update/{id}', [EmployeeController::class, 'update']);
        Route::delete('/delete/{id}', [EmployeeController::class, 'destroy']);
    });

    Route::prefix('departements')->group(function () {
        Route::get('/', [DepartementController::class, 'index']);
        Route::get('/{id}', [DepartementController::class, 'show']);
        Route::post('/store', [DepartementController::class, 'store']);
        Route::put('/update/{id}', [DepartementController::class, 'update']);
        Route::delete('/delete/{id}', [DepartementController::class, 'destroy']);
    });

});