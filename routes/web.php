<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;
use App\Http\Controllers\TransaksiFormController;
use App\Http\Controllers\TransaksiDetailController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MonitoringController;



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

    // 1. Rute untuk Pemohon (Membuat & Melihat)
    Route::prefix('list-permohonan')->group(function () {
        // GET /list-permohonan (Menampilkan daftar)
        Route::get('/', [TransaksiFormController::class, 'index'])->name('list-permohonan');

        // GET /list-permohonan/form-permohonan (Menampilkan form create)
        Route::get('/form-permohonan', [TransaksiFormController::class, 'create'])->name('form-permohonan');

        // POST /list-permohonan/form-permohonan (Menyimpan data dari form create)
        Route::post('/form-permohonan', [TransaksiFormController::class, 'store'])->name('form-permohonan.store');

        // GET /list-permohonan/detail/{transaksiForm} (Menampilkan halaman detail)
        Route::get('/detail/{transaksiForm}', [TransaksiFormController::class, 'show'])->name('permohonan.detail');
    });

    // 2. Rute untuk Item Rincian (AJAX)
    Route::prefix('permohonan-detail')->group(function () {
        // POST /permohonan-detail/store/{transaksiForm} (AJAX Tambah Item)
        Route::post('/store/{transaksiForm}', [TransaksiDetailController::class, 'store'])->name('permohonan.detail.store');

        // DELETE /permohonan-detail/destroy/{transaksiDetail} (AJAX Hapus Item)
        Route::delete('/destroy/{transaksiDetail}', [TransaksiDetailController::class, 'destroy'])->name('permohonan.detail.destroy');
    });

    // 3. Rute untuk Aksi Approval (PYB1, PYB2, BO) dan Submit Pemohon
    Route::prefix('permohonan-aksi')->group(function () {
        // POST /permohonan-aksi/submit/{transaksiForm} (Pemohon submit dari 'Draft')
        Route::post('/submit/{transaksiForm}', [ApprovalController::class, 'submit'])->name('permohonan.submit');

        // POST /permohonan-aksi/approve/{transaksiForm} (Approve oleh PYB/BO)
        Route::post('/approve/{transaksiForm}', [ApprovalController::class, 'approve'])->name('permohonan.approve');

        // POST /permohonan-aksi/reject/{transaksiForm} (Reject oleh PYB/BO)
        Route::post('/reject/{transaksiForm}', [ApprovalController::class, 'reject'])->name('permohonan.reject');
    });

    // 4. Rute untuk Manajemen Lampiran
    Route::prefix('lampiran')->group(function () {
        // POST /lampiran/store/{transaksiForm} (Upload lampiran baru)
        Route::post('/store/{transaksiForm}', [AttachmentController::class, 'store'])->name('lampiran.store');

        // GET /lampiran/download/{transaksiAttachment} (Download lampiran)
        Route::get('/download/{transaksiAttachment}', [AttachmentController::class, 'download'])->name('lampiran.download');

        // DELETE /lampiran/destroy/{transaksiAttachment} (Hapus lampiran)
        Route::delete('/destroy/{transaksiAttachment}', [AttachmentController::class, 'destroy'])->name('lampiran.destroy');
    });

    // 4. Rute untuk Manajemen Lampiran (AJAX)
    Route::prefix('permohonan-lampiran')->group(function () {
        // POST /permohonan-lampiran/store/{transaksiForm} (Upload lampiran baru)
        Route::post('/store/{transaksiForm}', [TransaksiAttachmentController::class, 'store'])->name('permohonan.lampiran.store');

        // GET /permohonan-lampiran/download/{transaksiAttachment} (Download lampiran)
        Route::get('/download/{transaksiAttachment}', [TransaksiAttachmentController::class, 'download'])->name('permohonan.lampiran.download');

        // DELETE /permohonan-lampiran/destroy/{transaksiAttachment} (Hapus lampiran)
        Route::delete('/destroy/{transaksiAttachment}', [TransaksiAttachmentController::class, 'destroy'])->name('permohonan.lampiran.destroy');
    });



    // === MODUL MONITORING (APPROVER & ADMIN) ===
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	// Route::get('/profile-static', [PageController::class, 'profile'])->name('profile-static');
	// Route::get('/sign-in-static', [PageController::class, 'signin'])->name('sign-in-static');
	// Route::get('/sign-up-static', [PageController::class, 'signup'])->name('sign-up-static');
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});
