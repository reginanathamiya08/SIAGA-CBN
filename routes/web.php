<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\MitraController;
use App\Http\Controllers\Admin\PenempatanController;
use App\Http\Controllers\Admin\KomponenGajiController;
use App\Http\Controllers\Admin\PenggajianController;
use App\Http\Controllers\Admin\LaporanAbsensiController;
use App\Http\Controllers\Admin\LaporanGajiController;
use App\Http\Controllers\Admin\LaporanLemburController;
use App\Http\Controllers\Admin\GajiMassalController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Pimpinan\DashboardController as PimpinanDashboard;
use App\Http\Controllers\Pimpinan\ApprovalController;
use App\Http\Controllers\Pimpinan\MonitoringKehadiranController;
use App\Http\Controllers\Pimpinan\MonitoringGajiController;
use App\Http\Controllers\Pimpinan\AdminManagementController;
use App\Http\Controllers\Karyawan\DashboardController as KaryawanDashboard;
use App\Http\Controllers\Karyawan\AbsensiController;
use App\Http\Controllers\Karyawan\PerizinanController;
use App\Http\Controllers\Karyawan\LemburController;
use App\Http\Controllers\Karyawan\SlipGajiController;

Route::get('/', fn () => view('landing'));

// ── AUTH ──────────────────────────────────────────────────────────────
Route::get ('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',  [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── FORGOT PASSWORD ──────────────────────────────────────────────────
Route::get ('/forgot-password',  [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password',  [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get ('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// ── SHARED AUTHENTICATED ROUTES ──────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('lembur/{lembur}/print', [\App\Http\Controllers\Karyawan\LemburController::class, 'printSlip'])->name('lembur.print');
});

// ── ADMIN ─────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth','role:admin'])->group(function () {

    Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Karyawan
    Route::get   ('karyawan',                           [KaryawanController::class,'index'])        ->name('karyawan.index');
    Route::get   ('karyawan/tambah',                    [KaryawanController::class,'create'])       ->name('karyawan.create');
    Route::post  ('karyawan',                           [KaryawanController::class,'store'])        ->name('karyawan.store');
    Route::get   ('karyawan/{karyawan}',                [KaryawanController::class,'show'])         ->name('karyawan.show');
    Route::get   ('karyawan/{karyawan}/edit',           [KaryawanController::class,'edit'])         ->name('karyawan.edit');
    Route::put   ('karyawan/{karyawan}',                [KaryawanController::class,'update'])       ->name('karyawan.update');
    Route::patch ('karyawan/{karyawan}/toggle-status',  [KaryawanController::class,'toggleStatus'])->name('karyawan.toggle-status');
    Route::patch ('karyawan/{karyawan}/reset-password', [KaryawanController::class,'resetPassword'])->name('karyawan.reset-password');
    Route::delete('karyawan/{karyawan}/dokumen/{dokumenId}',
                                                        [KaryawanController::class,'hapusDokumen']) ->name('karyawan.hapus-dokumen');

    // Mitra
    Route::get   ('mitra',              [MitraController::class,'index'])  ->name('mitra.index');
    Route::get   ('mitra/tambah',       [MitraController::class,'create']) ->name('mitra.create');
    Route::post  ('mitra',              [MitraController::class,'store'])  ->name('mitra.store');
    Route::get   ('mitra/{mitra}',      [MitraController::class,'show'])   ->name('mitra.show');
    Route::get   ('mitra/{mitra}/edit', [MitraController::class,'edit'])   ->name('mitra.edit');
    Route::put   ('mitra/{mitra}',      [MitraController::class,'update']) ->name('mitra.update');
    Route::delete('mitra/{mitra}',      [MitraController::class,'destroy'])->name('mitra.destroy');

    // Penempatan
    Route::get  ('penempatan',                      [PenempatanController::class,'index'])  ->name('penempatan.index');
    Route::get  ('penempatan/tambah',               [PenempatanController::class,'create']) ->name('penempatan.create');
    Route::post ('penempatan',                      [PenempatanController::class,'store'])  ->name('penempatan.store');
    Route::patch('penempatan/{penempatan}/selesai', [PenempatanController::class,'selesai'])->name('penempatan.selesai');

    // Komponen Gaji Karyawan
    Route::get ('komponen-gaji-karyawan',                 [\App\Http\Controllers\Admin\KomponenGajiKaryawanController::class,'index'])        ->name('komponen-gaji-karyawan.index');
    Route::get ('komponen-gaji-karyawan/{karyawan}/edit', [\App\Http\Controllers\Admin\KomponenGajiKaryawanController::class,'edit'])         ->name('komponen-gaji-karyawan.edit');
    Route::put ('komponen-gaji-karyawan/{karyawan}',      [\App\Http\Controllers\Admin\KomponenGajiKaryawanController::class,'update'])       ->name('komponen-gaji-karyawan.update');
    Route::post('komponen-gaji-karyawan/bulk-bpjs',       [\App\Http\Controllers\Admin\KomponenGajiKaryawanController::class,'updateBulkBpjs'])->name('komponen-gaji-karyawan.bulk-bpjs');

    // Komponen Gaji CRUD (renamed from Master Komponen Gaji)
    Route::get ('komponen-gaji',           [\App\Http\Controllers\Admin\KomponenGajiController::class, 'index'])->name('komponen-gaji.index');
    Route::get ('komponen-gaji/tambah',    [\App\Http\Controllers\Admin\KomponenGajiController::class, 'create'])->name('komponen-gaji.create');
    Route::post('komponen-gaji',           [\App\Http\Controllers\Admin\KomponenGajiController::class, 'store'])->name('komponen-gaji.store');
    Route::get ('komponen-gaji/{id}/edit', [\App\Http\Controllers\Admin\KomponenGajiController::class, 'edit'])->name('komponen-gaji.edit');
    Route::put ('komponen-gaji/{id}',      [\App\Http\Controllers\Admin\KomponenGajiController::class, 'update'])->name('komponen-gaji.update');
    Route::delete('komponen-gaji/{id}',    [\App\Http\Controllers\Admin\KomponenGajiController::class, 'destroy'])->name('komponen-gaji.destroy');

    // Gaji Massal (Auto-fill)
    Route::get ('gaji-massal',                    [GajiMassalController::class,'index'])              ->name('gaji-massal.index');
    Route::post('gaji-massal/update-tetap',       [GajiMassalController::class,'updateTetap'])         ->name('gaji-massal.update-tetap');
    Route::post('gaji-massal/update-umr',         [GajiMassalController::class,'updateUmr'])            ->name('gaji-massal.update-umr');
    Route::post('gaji-massal/update-spesialis',   [GajiMassalController::class,'updateSpesialis'])      ->name('gaji-massal.update-spesialis');
    Route::post('gaji-massal/update-kontrak-hc',  [GajiMassalController::class,'updateKontrakHc'])     ->name('gaji-massal.update-kontrak-hc');

    // Penggajian — PENTING: route statis (proses) harus SEBELUM route dinamis ({periodeGaji})
    Route::get ('penggajian',              [PenggajianController::class,'index'])     ->name('penggajian.index');
    Route::get ('penggajian/proses',       [PenggajianController::class,'create'])    ->name('penggajian.create');
    Route::post('penggajian/proses',       [PenggajianController::class,'store'])     ->name('penggajian.proses'); // keep name 'proses' or change to 'store' if needed, we'll map it to 'store'
    Route::post('penggajian/{periodeGaji}/hitung', [PenggajianController::class,'hitung'])->name('penggajian.hitung');
    Route::delete('penggajian/{periodeGaji}', [PenggajianController::class,'destroy'])->name('penggajian.destroy');
    Route::get ('penggajian/slip/{slipGaji}', [PenggajianController::class,'detailSlip'])->name('penggajian.slip');
    Route::get ('penggajian/slip/{slipGaji}/official', [PenggajianController::class,'officialSlip'])->name('penggajian.slip-official');
    Route::put ('penggajian/slip/{slipGaji}/update-absensi', [PenggajianController::class,'updateAbsensi'])->name('penggajian.slip.update-absensi');
    Route::get ('penggajian/{periodeGaji}',[PenggajianController::class,'show'])      ->name('penggajian.show');

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get ('absensi',                       [LaporanAbsensiController::class, 'index'])        ->name('absensi.index');
        Route::get ('absensi/export',                [LaporanAbsensiController::class, 'export'])       ->name('absensi.export');
        Route::post('absensi/store-manual',          [LaporanAbsensiController::class, 'storeManual'])  ->name('absensi.store-manual');
        Route::put ('absensi/{absensi}/update-manual', [LaporanAbsensiController::class, 'updateManual'])->name('absensi.update-manual');
        Route::get ('gaji',                          [LaporanGajiController::class, 'index'])           ->name('gaji.index');
        Route::get ('gaji/export',                   [LaporanGajiController::class, 'export'])          ->name('gaji.export');
        Route::get ('lembur',                        [LaporanLemburController::class, 'index'])          ->name('lembur.index');
        Route::get ('lembur/export',                 [LaporanLemburController::class, 'export'])         ->name('lembur.export');
    });
    
    // Konfigurasi Sistem
    Route::get ('konfigurasi', [ConfigurationController::class, 'index'])->name('konfigurasi.index');
    Route::post('konfigurasi', [ConfigurationController::class, 'update'])->name('konfigurasi.update');

    // Notifikasi Admin
    Route::get ('notifications/{id}/read', [\App\Http\Controllers\Karyawan\NotificationController::class, 'read'])      ->name('notifications.read');
    Route::post('notifications/mark-all',   [\App\Http\Controllers\Karyawan\NotificationController::class, 'markAllRead'])->name('notifications.mark-all');
});

// ── PIMPINAN ──────────────────────────────────────────────────────────
Route::prefix('pimpinan')->name('pimpinan.')->middleware(['auth','role:pimpinan'])->group(function () {

    Route::get('dashboard', [PimpinanDashboard::class,'index'])->name('dashboard');

    // ── Monitoring Kehadiran ───────────────────────────────────────────
    Route::prefix('monitoring-kehadiran')->name('monitoring.')->group(function () {
        Route::get('/',           [MonitoringKehadiranController::class, 'index'])     ->name('index');
        Route::get('/statistik',  [MonitoringKehadiranController::class, 'statistik']) ->name('statistik');
        Route::get('/per-mitra',  [MonitoringKehadiranController::class, 'perMitra'])  ->name('per-mitra');
        Route::get('/export',     [MonitoringKehadiranController::class, 'export'])    ->name('export');
        Route::get('/{karyawan}', [MonitoringKehadiranController::class, 'detail'])    ->name('detail');
    });

    Route::prefix('monitoring-gaji')->name('monitoring-gaji.')->group(function () {
        Route::get('/',       [MonitoringGajiController::class, 'index'])  ->name('index');
        Route::get('/export', [MonitoringGajiController::class, 'export']) ->name('export');
        Route::post('/{periode}/approve', [MonitoringGajiController::class, 'approve'])->name('approve');
        Route::post('/{periode}/reject', [MonitoringGajiController::class, 'reject'])->name('reject');
        Route::post('/{periode}/submit', [MonitoringGajiController::class, 'submit'])->name('submit');
    });

    Route::get  ('approval',                              [ApprovalController::class,'index'])           ->name('approval.index');
    Route::get  ('approval/perizinan/{perizinan}',        [ApprovalController::class,'showPerizinan'])   ->name('approval.perizinan.show');
    Route::patch('approval/perizinan/{perizinan}/setuju', [ApprovalController::class,'approvePerizinan'])->name('approval.perizinan.setuju');
    Route::patch('approval/perizinan/{perizinan}/tolak',  [ApprovalController::class,'tolakPerizinan'])  ->name('approval.perizinan.tolak');
    Route::patch('approval/lembur/{lembur}/setuju',       [ApprovalController::class,'approveLembur'])   ->name('approval.lembur.setuju');
    Route::patch('approval/lembur/{lembur}/tolak',        [ApprovalController::class,'tolakLembur'])     ->name('approval.lembur.tolak');

    // ── Kelola Admin ──────────────────────────────────────────────────
    Route::get  ('admin',               [AdminManagementController::class, 'index'])       ->name('admin.index');
    Route::post ('admin',               [AdminManagementController::class, 'store'])       ->name('admin.store');
    Route::put  ('admin/{user}',        [AdminManagementController::class, 'update'])      ->name('admin.update');
    Route::patch('admin/{user}/toggle', [AdminManagementController::class, 'toggleStatus'])->name('admin.toggle');
    Route::delete('admin/{user}',       [AdminManagementController::class, 'destroy'])     ->name('admin.destroy');

    // Notifikasi Pimpinan
    Route::get ('notifications/{id}/read', [\App\Http\Controllers\Karyawan\NotificationController::class, 'read'])      ->name('notifications.read');
    Route::post('notifications/mark-all',   [\App\Http\Controllers\Karyawan\NotificationController::class, 'markAllRead'])->name('notifications.mark-all');
});

// ── KARYAWAN (Personal Area for Employees Only) ──────────────────────
Route::prefix('karyawan')->name('karyawan.')->middleware(['auth','role:karyawan_tetap,karyawan_kontrak'])->group(function () {

    Route::get('dashboard', [KaryawanDashboard::class,'index'])->name('dashboard');

    // Absensi
    Route::get ('absensi',         [AbsensiController::class,'index'])      ->name('absensi.index');
    Route::post('absensi/masuk',   [AbsensiController::class,'absenMasuk']) ->name('absensi.masuk');
    Route::post('absensi/pulang',  [AbsensiController::class,'absenPulang'])->name('absensi.pulang');
    Route::get ('absensi/riwayat', [AbsensiController::class,'riwayat'])    ->name('absensi.riwayat');

    // Perizinan
    Route::get   ('perizinan-backup',                    [PerizinanController::class, 'backupIndex'])  ->name('perizinan.backup.index');
    Route::patch ('perizinan-backup/{perizinan}/setuju', [PerizinanController::class, 'backupApprove'])->name('perizinan.backup.setuju');
    Route::patch ('perizinan-backup/{perizinan}/tolak',  [PerizinanController::class, 'backupReject']) ->name('perizinan.backup.tolak');
    Route::get   ('perizinan',             [PerizinanController::class,'index'])  ->name('perizinan.index');
    Route::get   ('perizinan/ajukan',      [PerizinanController::class,'create']) ->name('perizinan.create');
    Route::post  ('perizinan',             [PerizinanController::class,'store'])  ->name('perizinan.store');
    Route::post  ('perizinan/{perizinan}/upload-mitra', [PerizinanController::class, 'uploadFormMitra'])->name('perizinan.upload-mitra');
    Route::get   ('perizinan/{perizinan}/print', [PerizinanController::class,'print'])  ->name('perizinan.print');
    Route::get   ('perizinan/{perizinan}', [PerizinanController::class,'show'])   ->name('perizinan.show');
    Route::delete('perizinan/{perizinan}', [PerizinanController::class,'destroy'])->name('perizinan.destroy');

    // Dinas Luar Kota
    Route::get   ('dinas-luar',        [PerizinanController::class, 'dinasLuarIndex']) ->name('dinas-luar.index');
    Route::get   ('dinas-luar/ajukan', [PerizinanController::class, 'dinasLuarCreate'])->name('dinas-luar.create');
    Route::post  ('dinas-luar',        [PerizinanController::class, 'dinasLuarStore']) ->name('dinas-luar.store');
    Route::get   ('dinas-luar/{perizinan}', [PerizinanController::class, 'dinasLuarShow'])->name('dinas-luar.show');

    // Lembur
    Route::get   ('lembur',          [LemburController::class,'index'])  ->name('lembur.index');
    Route::get   ('lembur/ajukan',   [LemburController::class,'create']) ->name('lembur.create');
    Route::post  ('lembur',          [LemburController::class,'store'])  ->name('lembur.store');
    Route::get   ('lembur/{lembur}/print', [LemburController::class,'printSlip'])->name('lembur.print');
    Route::get   ('lembur/{lembur}', [LemburController::class,'show'])   ->name('lembur.show');
    Route::delete('lembur/{lembur}', [LemburController::class,'destroy'])->name('lembur.destroy');

    // Slip Gaji — statis harus SEBELUM dinamis
    Route::get('slip-gaji',            [SlipGajiController::class,'index'])->name('slip-gaji.index');
    Route::get('slip-gaji/{slipGaji}', [SlipGajiController::class,'show']) ->name('slip-gaji.show');
    Route::get('slip-gaji/{slipGaji}/official', [SlipGajiController::class,'officialSlip'])->name('slip-gaji.official');

    // Pengaturan Profil
    Route::get('password', [\App\Http\Controllers\Karyawan\ProfileController::class, 'editPassword'])->name('password.edit');
    Route::put('password', [\App\Http\Controllers\Karyawan\ProfileController::class, 'updatePassword'])->name('password.update');

    // Notifikasi
    Route::get('notifications/{id}/read',   [\App\Http\Controllers\Karyawan\NotificationController::class, 'read'])       ->name('notifications.read');
    Route::post('notifications/mark-all',   [\App\Http\Controllers\Karyawan\NotificationController::class, 'markAllRead'])->name('notifications.mark-all');
});