<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ArsipKitirController;
use App\Http\Controllers\RekapAbsensiController;
use App\Http\Controllers\AttendanceExportController;
use App\Http\Controllers\AttendancesController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
//Update User Details

Route::post('/logindah', [App\Http\Controllers\AuthController::class, 'logindah'])->name('logindah');

Route::middleware(['auth', 'role:pegawai'])->group(function () {
    Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');
    Route::get('/slip-gaji/{id}', [App\Http\Controllers\KitirGajiController::class, 'index'])->name('index.gaji');
    Route::get('/riwayat-absensi/{id}', [App\Http\Controllers\AttendancesController::class, 'indexAbsensi'])->name('riwayatnya');
    Route::get('/mari-absensi-hari-ini/{id}', [App\Http\Controllers\AttendancesController::class, 'indexHome'])->name('absendong');
    Route::get('/mau-cek-gaji/{id}', [App\Http\Controllers\KitirGajiController::class, 'index'])->name('gajikuh');
    Route::get('/profil-ptk/{id}', [App\Http\Controllers\UserController::class, 'indexProfil'])->name('index.profil');
    Route::get('/edit-profile-aku-ini/ptk/{id}', [App\Http\Controllers\UserController::class, 'editProfil'])->name('index.profil');
    Route::put('/apps-update/data-ptk-aku/{id}/ini-idnya', [App\Http\Controllers\UserController::class, 'updateProfileApps'])->name('admin-data-ptk.updateApps');
    Route::put('/apps-update/data-password-aku/{id}/ini-idnya', [App\Http\Controllers\UserController::class, 'updatePassword'])->name('update.password');
    Route::post('/absen/submit', [\App\Http\Controllers\AttendancesController::class, 'submitAbsenyadong'])->name('absen.submit');
});

Route::middleware(['auth', 'role:admin,operator'])->group(function () {
    // ===== Pengaturan Absensi (bisa diakses admin & operator sekolah) =====
    Route::post('/admin/pengaturan-tanggal/store', [App\Http\Controllers\AbsensiController::class, 'storePengaturanTanggal'])
        ->name('admin.pengaturan-tanggal.store');

    // Periode Libur
    Route::post('/admin/periode-libur/store', [App\Http\Controllers\AbsensiController::class, 'storePeriodeLibur'])
        ->name('admin.periode-libur.store');

    Route::delete('/admin/periode-libur/{id}', [App\Http\Controllers\AbsensiController::class, 'deletePeriodeLibur'])
        ->name('admin.periode-libur.delete');

    // Generate libur nasional dari config/hari_libur.php
    Route::post('/admin/periode-libur/nasional', [App\Http\Controllers\AbsensiController::class, 'generateLiburNasional'])
        ->name('admin.periode-libur.nasional');

    // Cek ketersediaan absensi
    Route::get('/admin/cek-absensi/{tanggal}', [App\Http\Controllers\AbsensiController::class, 'checkAbsensiAvailability']);
    Route::get('/admin-setting-absensi', [App\Http\Controllers\AbsensiController::class, 'index']);
    Route::post('/admin/handle-sunday', [App\Http\Controllers\AbsensiController::class, 'handleSundayAutomatically']);
    Route::delete('/admin/pengaturan-tanggal/{id}', [App\Http\Controllers\AbsensiController::class, 'deletePengaturanTanggal'])
        ->name('admin.pengaturan-tanggal.delete');

    Route::put('/admin-update/jadwal', [App\Http\Controllers\AbsensiController::class, 'updatejadwal'])->name('admin-data-jadwal.update');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    
    Route::prefix('absensi')->name('attendance.')->group(function () {
        // Route::get('/harian', [AttendancesController::class, 'index'])->name('harian');
        Route::post('/store-manual', [AttendancesController::class, 'storeManual'])->name('storeManual');
        // Route::put('/update-manual/{id}', [AttendancesController::class, 'updateManual'])->name('updateManual');
        Route::delete('/destroy/{id}', [AttendancesController::class, 'destroyAbsenku'])->name('destroy');
        // Route::get('/get-by-date', [AttendancesController::class, 'getByDate'])->name('getByDate');
        // Route::post('/check-existing', [AttendancesController::class, 'checkExisting'])->name('checkExisting');
    });

    Route::prefix('arsip-kitir')->middleware(['auth'])->group(function () {
        Route::get('/', [ArsipKitirController::class, 'index'])->name('arsip-kitir.index');
        Route::get('/export-excel', [ArsipKitirController::class, 'exportExcel'])->name('arsip-kitir.export-excel');
        Route::delete('/{id}', [ArsipKitirController::class, 'destroy'])->name('arsip-kitir.destroy');
    });
    // routes/web.php
    Route::get('/arsip-kitir/export-csv', [App\Http\Controllers\ArsipKitirController::class, 'exportCSV'])
        ->name('arsip-kitir.export-csv');
    
    Route::get('/arsip-kitir/export-slip-csv/{id}', [App\Http\Controllers\ArsipKitirController::class, 'exportSlipCSV'])
        ->name('arsip-kitir.export-slip-csv');
    Route::get(
        '/arsip-kitir/export-excel',
        [App\Http\Controllers\ArsipKitirController::class, 'exportExcel']
    )->name('arsip-kitir.export-excel');

    Route::get('/arsip-kitir', [App\Http\Controllers\ArsipKitirController::class, 'index'])->name('arsip-kitir.index');
    Route::get('/arsip-kitir/export-csv', [App\Http\Controllers\ArsipKitirController::class, 'exportCSV'])->name('arsip-kitir.export-csv');
    Route::get('/arsip-kitir/export-slip-csv/{id}', [App\Http\Controllers\ArsipKitirController::class, 'exportSlipCSV'])->name('arsip-kitir.export-slip-csv');
    Route::delete('/arsip-kitir/{id}', [App\Http\Controllers\ArsipKitirController::class, 'destroy'])->name('arsip-kitir.destroy');
    Route::get('/detail-gaji/{id}', [App\Http\Controllers\KitirGajiController::class, 'indexadmin'])->name('gajinya');
    Route::get('/profil-admin/{id}', [App\Http\Controllers\UserController::class, 'indexProfilAdmin'])->name('index.profil');
    Route::get('/edit-profile-aku-ini/admin/{id}', [App\Http\Controllers\UserController::class, 'editProfilAdmin'])->name('index.profil');
    Route::put('/admin-update/data-admin-aku/{id}/ini-idnya', [App\Http\Controllers\UserController::class, 'updateProfileAdmin'])->name('admin-data-admin.updateAdmin');
    Route::put('/admin-update/data-password-aku/{id}/ini-idnya', [App\Http\Controllers\UserController::class, 'updatePasswordAdmin'])->name('update.passwordAdmin');
    Route::post('/tunjangan/update', [App\Http\Controllers\TunjanganController::class, 'updatewoy'])->name('tunjangan.update');
    Route::post('/attendance/update/{id}', [App\Http\Controllers\AttendancesController::class, 'updateManual'])->name('attendance.updateManual');

    Route::put('/skala-gaji-guruPersentase/update/{id}', [App\Http\Controllers\SkalaGajiGuruController::class, 'updateManualPersentase'])->name('skala-persentase-gaji-guru.update');
    Route::put('/skala-gaji-guruPokok/update/{id}', [App\Http\Controllers\SkalaGajiGuruController::class, 'updateManualPokok'])->name('skala-pokok-gaji-guru.update');
    Route::put('/rp-gaji-guruPokok/update', [App\Http\Controllers\SkalaGajiGuruController::class, 'updateManualRp'])->name('rp-gaji-guru.update');
    Route::put('/skala-gaji-AdminPersentase/update/{id}', [App\Http\Controllers\SkalaGajiAdminController::class, 'updateManualPersentase'])->name('skala-persentase-gaji-Admin.update');
    Route::put('/skala-gaji-AdminPokok/update/{id}', [App\Http\Controllers\SkalaGajiAdminController::class, 'updateManualPokok'])->name('skala-pokok-gaji-Admin.update');
    Route::post('/admin-post/data-ptk', [App\Http\Controllers\UserController::class, 'store'])->name('admin-data-ptk.post');
    Route::put('/admin-update/data-ptk/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('admin-data-ptk.update');
    Route::get('/admin-data-ptk', [App\Http\Controllers\UserController::class, 'get'])->name('admin-data-pegawai.get');
    Route::get('/admin-edit/data-ptk/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('admin-data-ptk.edit');
    Route::delete('/admin/data-ptk/remove/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('admin-data-ptk.remove');
    Route::put('/admin-update/lokasi', [App\Http\Controllers\AbsensiController::class, 'updatelokasi'])->name('admin-data-lokasi.update');
    Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('admin.dashboard');
    Route::get('/admin-kitir-gajis', [App\Http\Controllers\KitirGajiController::class, 'updateKitirGaji'])->name('admin-data-kitir.edit');
    Route::post('/admin-post/kasbon', [App\Http\Controllers\KasbonController::class, 'store'])->name('admin-data-kasbon.post');
    Route::delete('/kasbon/{kasbon}', [App\Http\Controllers\KasbonController::class, 'destroy'])->name('kasbon.destroy');
    Route::put('/kitir-gaji-update-guru/{id}', [App\Http\Controllers\KitirGajiController::class, 'updatekitir'])->name('admin-data-kitir.update');
    Route::put('/publish-gaji', [App\Http\Controllers\KitirGajiController::class, 'publishGaji'])->name('publish.gaji');
    
    
    Route::get('/arsip-rekap-absensi', [AttendanceExportController::class, 'indexArsip'])->name('rekap.absensi');
    
    // Route untuk export
    Route::get('/export/attendance', [AttendanceExportController::class, 'showForm'])->name('export.form');
    Route::get('/download-attendance/{bulan}/{tahun}', function ($bulan, $tahun) {
        return Excel::download(new \App\Exports\AttendanceMonthlyExport($bulan,$tahun),
            'Rekap_Absensi_'.$bulan.'_'.$tahun.'.xlsx');
    })->name('download.attendance');

    Route::get('/export-attendance/{bulan}/{tahun}', 
        [AttendanceExportController::class, 'export'])
        ->name('export.attendance');
    Route::get('/export/attendance/download/{fileName}', [AttendanceExportController::class, 'download'])->name('export.download');
});

Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->where('any', '.*')->name('index');