<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;
// Controller Utama Aplikasi
use App\Http\Controllers\TransaksiFormController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ===================================================================
// RUTE AUTENTIKASI (BAWAAN ARGON) - SUDAH DIKEMBALIKAN
// ===================================================================
Route::get('/', function () {return redirect('/dashboard');})->middleware('auth');
Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
Route::get('/dashboard', [HomeController::class, 'index'])->name('home')->middleware('auth');

Route::group(['middleware' => 'auth'], function () {

    // ===================================================================
    // RUTE UTAMA APLIKASI TRANSAKSI
    // ===================================================================

    // 1. Rute untuk Pemohon (CRUD)
    Route::prefix('permohonan')->name('permohonan.')->group(function () {
        // GET /permohonan (List Permohonan / "Daftar Tugas")
        Route::get('/', [TransaksiFormController::class, 'index'])->name('list');

        // GET /permohonan/buat (Menampilkan form create)
        Route::get('/buat', [TransaksiFormController::class, 'create'])->name('create');

        // POST /permohonan/buat (Menyimpan data dari form create)
        Route::post('/buat', [TransaksiFormController::class, 'store'])->name('store');

        // GET /permohonan/detail/{transaksiForm} (Menampilkan halaman detail)
        Route::get('/detail/{transaksiForm}', [TransaksiFormController::class, 'show'])->name('detail');

        // === RUTE BARU UNTUK EDIT/UPDATE/DELETE ===
        // GET /permohonan/edit/{transaksiForm} (Menampilkan form edit)
        Route::get('/edit/{transaksiForm}', [TransaksiFormController::class, 'edit'])->name('edit');

        // PUT /permohonan/update/{transaksiForm} (Menyimpan data dari form edit)
        Route::put('/update/{transaksiForm}', [TransaksiFormController::class, 'update'])->name('update');

        // DELETE /permohonan/destroy/{transaksiForm} (Menghapus 'Draft')
        Route::delete('/destroy/{transaksiForm}', [TransaksiFormController::class, 'destroy'])->name('destroy');
    });

    // 2. Rute untuk Aksi Approval & Submit
    Route::prefix('permohonan-aksi')->name('permohonan.')->group(function () {
        // PATCH /permohonan-aksi/submit/{transaksiForm} (Pemohon submit dari 'Draft')
        Route::patch('/submit/{transaksiForm}', [ApprovalController::class, 'submit'])->name('submit');

        // PATCH /permohonan-aksi/process/{transaksiForm} (Approve / Reject oleh atasan)
        Route::patch('/process/{transaksiForm}', [ApprovalController::class, 'process'])->name('process');

        // === RUTE BARU UNTUK REVISI ===
        // PATCH /permohonan-aksi/resubmit/{transaksiForm} (Pemohon ajukan ulang dari 'Ditolak')
        Route::patch('/resubmit/{transaksiForm}', [ApprovalController::class, 'resubmit'])->name('resubmit');
    });

    // 3. Rute untuk Manajemen Lampiran (AJAX)
    Route::prefix('permohonan-lampiran')->name('permohonan.attachment.')->group(function () {
        // POST /permohonan-lampiran/store/{transaksiForm} (Upload lampiran baru)
        Route::post('/store/{transaksiForm}', [AttachmentController::class, 'store'])->name('store');

        // GET /permohonan-lampiran/download/{transaksiAttachment} (Download lampiran)
        Route::get('/download/{transaksiAttachment}', [AttachmentController::class, 'download'])->name('download');

        // DELETE /permohonan-lampiran/destroy/{transaksiAttachment} (Hapus lampiran)
        Route::delete('/destroy/{transaksiAttachment}', [AttachmentController::class, 'destroy'])->name('destroy');
    });

    // 4. Rute Monitoring (Riwayat)
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

    // 5. Rute Notifikasi
    Route::prefix('notifikasi')->name('notifications.')->group(function () {
        Route::patch('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::patch('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
    });


    // ===================================================================
    // Rute Bawaan Argon (Profil, dll)
    // ===================================================================

    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});

