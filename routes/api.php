<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\AttendancesController;
use App\Http\Controllers\KasbonController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/locations', [LocationsController::class, 'indexAndroid']);
    Route::get('/user', [UserController::class, 'profile']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::post('/profile/update', [UserController::class, 'updateProfile']);

    // Jadwal & lokasi hanya untuk user yang sudah login
    Route::get('/Jadwal/mobile', [AttendancesController::class, 'jadwal']);
    Route::get('/Lokasi/mobile', [AttendancesController::class, 'lokasiAbsennya']);

    // Absensi: user_id diambil dari token, bukan dari body.
    Route::post('/absen/masuk', [AttendancesController::class, 'absenMasuk']);
    Route::post('/absen/pulang', [AttendancesController::class, 'absenPulang']);
    Route::get('/absensi/today', [AttendancesController::class, 'getAbsensiHariIni']);

    Route::get('/absen/riwayat', [AttendancesController::class, 'riwayat']);
    Route::get('/absen/{tanggal}', [AttendancesController::class, 'detail']);

    Route::get('/kasbon', [KasbonController::class, 'indexMobile']);
    Route::post('/kasbon', [KasbonController::class, 'storeMobile']);

    Route::post('/send-notif', [\App\Http\Controllers\Api\NotificationController::class, 'sendNotification']);
});
