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
use App\Http\Controllers\Admin\GajiMassalController;
use App\Http\Controllers\Pimpinan\DashboardController as PimpinanDashboard;
use App\Http\Controllers\Pimpinan\ApprovalController;
use App\Http\Controllers\Pimpinan\MonitoringKehadiranController;
use App\Http\Controllers\Pimpinan\MonitoringGajiController;
use App\Http\Controllers\Karyawan\DashboardController as KaryawanDashboard;
use App\Http\Controllers\Karyawan\AbsensiController;
use App\Http\Controllers\Karyawan\PerizinanController;
use App\Http\Controllers\Karyawan\LemburController;
use App\Http\Controllers\Karyawan\DinasLuarController;
use App\Http\Controllers\Karyawan\SlipGajiController;

Route::get('/', fn () => redirect()->route('login'));

// ── AUTH ──────────────────────────────────────────────────────────────
Route::get ('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',  [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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

    // Komponen Gaji
    Route::get ('komponen-gaji',                 [KomponenGajiController::class,'index'])        ->name('komponen-gaji.index');
    Route::get ('komponen-gaji/{karyawan}/edit', [KomponenGajiController::class,'edit'])         ->name('komponen-gaji.edit');
    Route::put ('komponen-gaji/{karyawan}',      [KomponenGajiController::class,'update'])       ->name('komponen-gaji.update');
    Route::post('komponen-gaji/bulk-bpjs',       [KomponenGajiController::class,'updateBulkBpjs'])->name('komponen-gaji.bulk-bpjs');

    // Gaji Massal (Auto-fill)
    Route::get ('gaji-massal',                 [GajiMassalController::class,'index'])           ->name('gaji-massal.index');
    Route::post('gaji-massal/update-umr',       [GajiMassalController::class,'updateUmr'])         ->name('gaji-massal.update-umr');
    Route::post('gaji-massal/update-spesialis', [GajiMassalController::class,'updateSpesialis'])   ->name('gaji-massal.update-spesialis');

    // Penggajian — PENTING: route statis (proses) harus SEBELUM route dinamis ({periodeGaji})
    Route::get ('penggajian',              [PenggajianController::class,'index'])     ->name('penggajian.index');
    Route::get ('penggajian/proses',       [PenggajianController::class,'create'])    ->name('penggajian.create');
    Route::post('penggajian/proses',       [PenggajianController::class,'proses'])    ->name('penggajian.proses');
    Route::get ('penggajian/slip/{slipGaji}', [PenggajianController::class,'detailSlip'])->name('penggajian.slip');
    Route::get ('penggajian/{periodeGaji}',[PenggajianController::class,'show'])      ->name('penggajian.show');

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get ('absensi',        [LaporanAbsensiController::class, 'index']) ->name('absensi.index');
        Route::get ('absensi/export', [LaporanAbsensiController::class, 'export'])->name('absensi.export');
        Route::get ('gaji',           [LaporanGajiController::class, 'index'])    ->name('gaji.index');
        Route::get ('gaji/export',    [LaporanGajiController::class, 'export'])   ->name('gaji.export');
    });
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

    // ── Monitoring Gaji ──────────────────────────────────────────────
    Route::prefix('monitoring-gaji')->name('monitoring-gaji.')->group(function () {
        Route::get('/',       [MonitoringGajiController::class, 'index'])  ->name('index');
        Route::get('/export', [MonitoringGajiController::class, 'export']) ->name('export');
    });

    Route::get  ('approval',                              [ApprovalController::class,'index'])           ->name('approval.index');
    Route::get  ('approval/perizinan/{perizinan}',        [ApprovalController::class,'showPerizinan'])   ->name('approval.perizinan.show');
    Route::patch('approval/perizinan/{perizinan}/setuju', [ApprovalController::class,'approvePerizinan'])->name('approval.perizinan.setuju');
    Route::patch('approval/perizinan/{perizinan}/tolak',  [ApprovalController::class,'tolakPerizinan'])  ->name('approval.perizinan.tolak');
    Route::patch('approval/lembur/{lembur}/setuju',       [ApprovalController::class,'approveLembur'])   ->name('approval.lembur.setuju');
    Route::patch('approval/lembur/{lembur}/tolak',        [ApprovalController::class,'tolakLembur'])     ->name('approval.lembur.tolak');
    Route::patch('approval/dinas_luar/{dinasLuar}/setuju', [ApprovalController::class,'approveDinas'])->name('approval.dinas_luar.setuju');
    Route::patch('approval/dinas_luar/{dinasLuar}/tolak',  [ApprovalController::class,'tolakDinas'])->name('approval.dinas_luar.tolak');
});

// ── KARYAWAN ──────────────────────────────────────────────────────────
Route::prefix('karyawan')->name('karyawan.')->middleware(['auth','role:karyawan_tetap,karyawan_kontrak'])->group(function () {

    Route::get('dashboard', [KaryawanDashboard::class,'index'])->name('dashboard');

    // Absensi
    Route::get ('absensi',         [AbsensiController::class,'index'])      ->name('absensi.index');
    Route::post('absensi/masuk',   [AbsensiController::class,'absenMasuk']) ->name('absensi.masuk');
    Route::post('absensi/pulang',  [AbsensiController::class,'absenPulang'])->name('absensi.pulang');
    Route::get ('absensi/riwayat', [AbsensiController::class,'riwayat'])    ->name('absensi.riwayat');

    // Perizinan
    Route::get   ('perizinan',             [PerizinanController::class,'index'])  ->name('perizinan.index');
    Route::get   ('perizinan/ajukan',      [PerizinanController::class,'create']) ->name('perizinan.create');
    Route::post  ('perizinan',             [PerizinanController::class,'store'])  ->name('perizinan.store');
    Route::get   ('perizinan/{perizinan}', [PerizinanController::class,'show'])   ->name('perizinan.show');
    Route::delete('perizinan/{perizinan}', [PerizinanController::class,'destroy'])->name('perizinan.destroy');

    // Lembur
    Route::get   ('lembur',          [LemburController::class,'index'])  ->name('lembur.index');
    Route::get   ('lembur/ajukan',   [LemburController::class,'create']) ->name('lembur.create');
    Route::post  ('lembur',          [LemburController::class,'store'])  ->name('lembur.store');
    Route::get   ('lembur/{lembur}', [LemburController::class,'show'])   ->name('lembur.show');
    Route::delete('lembur/{lembur}', [LemburController::class,'destroy'])->name('lembur.destroy');

    // Dinas Luar
    Route::get   ('dinas-luar',             [DinasLuarController::class,'index'])  ->name('dinas-luar.index');
    Route::get   ('dinas-luar/ajukan',      [DinasLuarController::class,'create']) ->name('dinas-luar.create');
    Route::post  ('dinas-luar',             [DinasLuarController::class,'store'])  ->name('dinas-luar.store');
    Route::get   ('dinas-luar/{dinasLuar}', [DinasLuarController::class,'show'])   ->name('dinas-luar.show');
    Route::delete('dinas-luar/{dinasLuar}', [DinasLuarController::class,'destroy'])->name('dinas-luar.destroy');

    // Slip Gaji — statis harus SEBELUM dinamis
    Route::get('slip-gaji',            [SlipGajiController::class,'index'])->name('slip-gaji.index');
    Route::get('slip-gaji/{slipGaji}', [SlipGajiController::class,'show']) ->name('slip-gaji.show');
});