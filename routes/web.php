<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PetugasDashboardController;
use App\Http\Controllers\SupervisorDashboardController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\PenjadwalanController;
use App\Http\Controllers\ManajemenUserController;
use App\Http\Controllers\NasabahAdminController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\PetugasSetoranController;
use App\Http\Controllers\SupervisorLaporanController;
use App\Http\Controllers\BlokPasarController;
use App\Http\Controllers\QrCodeDownloadController;
use App\Http\Controllers\TitipSetoranController;
use App\Http\Controllers\PetugasProfileController;
use App\Http\Controllers\SupervisorRequestController;

/*
|--------------------------------------------------------------------------
| Default Redirect Based on Role
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (!auth()->check())
        return redirect('/login');

    return match (auth()->user()->role) {
        'petugas' => redirect('/petugas/dashboard'),
        'supervisor' => redirect('/supervisor/dashboard'),
        default => abort(403),
    };
});

Route::get('/dashboard', fn() => redirect('/'))->name('dashboard');

Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

/*
|--------------------------------------------------------------------------
| Authenticated User Profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Routes for PETUGAS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:petugas'])->group(function () {
    Route::redirect('/petugas', '/petugas/dashboard');

    // Dashboard
    Route::get('/petugas/dashboard', [PetugasDashboardController::class, 'index'])
        ->name('petugas.dashboard');

    // Profile khusus petugas
    Route::get('/petugas/profile', [PetugasProfileController::class, 'edit'])->name('petugas.profile.edit');
    Route::patch('/petugas/profile', [PetugasProfileController::class, 'update'])->name('petugas.profile.update');
    Route::delete('/petugas/profile', [PetugasProfileController::class, 'destroy'])->name('petugas.profile.destroy');

    // Nasabah & Setoran
    Route::get('/nasabah', [NasabahController::class, 'index'])
        ->name('petugas.nasabah.index');
    Route::post('/nasabah/{nasabah}/setoran', [NasabahController::class, 'storeSetoran'])
        ->name('petugas.nasabah.setoran');

    // Setoran manual
    Route::get('/petugas/setoran', [PetugasSetoranController::class, 'index'])
        ->name('petugas.setoran.index');
    Route::post('/petugas/setoran', [PetugasSetoranController::class, 'store'])
        ->name('petugas.setoran.store');

    // Detail Nasabah (via Session)
    Route::post('/petugas/nasabah/detail', [PetugasSetoranController::class, 'storeToSession'])
        ->name('petugas.nasabah.storeToSession');

    Route::get('/petugas/nasabah/detail', [PetugasSetoranController::class, 'show'])
        ->name('petugas.nasabah.detail');

    Route::post('/petugas/setoran/prepare-cetak', [PetugasSetoranController::class, 'prepareCetak'])
        ->name('petugas.setoran.prepareCetak');

    Route::get('/petugas/setoran/cetak-gabungan', [PetugasSetoranController::class, 'cetakGabungan'])
        ->name('petugas.setoran.cetakGabungan');

    // Preview cetak bukti setoran gabungan (langsung tampilkan halaman print)
    Route::get('/petugas/setoran/preview-thermal', [PetugasSetoranController::class, 'previewThermal'])
        ->name('petugas.setoran.previewThermal');

    // Cek Session Cetak (untuk auto-close tab)
    Route::get('/petugas/setoran/check-session', [PetugasSetoranController::class, 'checkSessionActive'])
        ->name('petugas.setoran.checkSession');

    // Scan QR Code
    Route::get('/petugas/nasabah/by-qr/{token}', [PetugasSetoranController::class, 'findByQr'])
        ->name('petugas.nasabah.byQr');

    // ---------- Batal setoran ----------
    // Setoran reguler
    Route::delete('/petugas/setoran/{id}', [PetugasSetoranController::class, 'destroy'])
        ->name('petugas.setoran.destroy');

    // ---------- Edit setoran Reguler----------
    // Ajukan Edit Nominal Setoran
    Route::post('/petugas/setoran/ajukan-edit', [PetugasSetoranController::class, 'ajukanEdit'])
        ->name('petugas.setoran.ajukanEdit');

    // Update Nominal Setoran
    Route::put('/petugas/setoran/{id}', [PetugasSetoranController::class, 'update'])
        ->name('petugas.setoran.update');

    Route::prefix('petugas')->middleware(['auth', 'role:petugas'])->group(function () {
        // ---------- Titip Setoran ----------
        Route::match(['get', 'post'], '/titip-setoran', [TitipSetoranController::class, 'index'])
            ->name('petugas.titipsetoran.index');

        // Store titip setoran
        Route::post('/titip-setoran', [TitipSetoranController::class, 'store'])
            ->name('petugas.titipsetoran.store');

        // Titip Setoran: simpan ID nasabah ke session
        Route::post('/titip-setoran/nasabah/detail', [TitipSetoranController::class, 'storeToSession'])
            ->name('petugas.titipsetoran.storeToSession');

        // Titip Setoran: detail nasabah dari session
        Route::get('/titip-setoran/nasabah/detail', [TitipSetoranController::class, 'show'])
            ->name('petugas.titipsetoran.detail');

        // Update titip setoran (setelah approve koreksi)
        Route::put('/titip-setoran/{titipsetoran}', [TitipSetoranController::class, 'update'])
            ->name('petugas.titipsetoran.update');

        // Batal titip setoran
        Route::delete('/titip-setoran/{id}', [TitipSetoranController::class, 'destroy'])
            ->name('petugas.titipsetoran.destroy');

        // Ajukan edit nominal titip setoran
        Route::post('/titip-setoran/ajukan-edit', [TitipSetoranController::class, 'ajukanEdit'])
            ->name('petugas.titipsetoran.ajukanEdit');

        // Cetak titip setoran
        Route::post('/titip-setoran/prepare-cetak', [TitipSetoranController::class, 'prepareCetak'])
            ->name('petugas.titipsetoran.prepareCetak');
        Route::get('/titip-setoran/cetak-gabungan', [TitipSetoranController::class, 'cetakGabungan'])
            ->name('petugas.titipsetoran.cetakGabungan');

        // Preview Thermal
        Route::get('/titip-setoran/preview-thermal', [TitipSetoranController::class, 'previewThermal'])
            ->name('petugas.titipsetoran.previewThermal');

        // Cek Session Cetak (auto close tab)
        Route::get('/titip-setoran/check-session', [TitipSetoranController::class, 'checkSessionActive'])
            ->name('petugas.titipsetoran.checkSession');

        // Scan QR Code (khusus titip setoran)
        Route::get('/titip-setoran/scan/{token}', [TitipSetoranController::class, 'findByQr'])
            ->name('petugas.titipsetoran.byQr');
    });
});

/*
|--------------------------------------------------------------------------
| Routes for SUPERVISOR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:supervisor', 'check.ip:supervisor'])->group(function () {
    // Dashboard
    Route::get('/supervisor', fn() => redirect('/supervisor/dashboard'));

    Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index']);

    // Petugas & Penjadwalan
    Route::get('/supervisor/petugas', [ManajemenUserController::class, 'index'])->name('supervisor.PenugasanIndex');
    Route::post('/supervisor/petugas', [ManajemenUserController::class, 'store'])->name('supervisor.petugas.store');
    Route::put('/supervisor/petugas/{user}', [ManajemenUserController::class, 'update'])->name('supervisor.petugas.update');
    Route::delete('/supervisor/petugas/{user}', [ManajemenUserController::class, 'destroy'])->name('supervisor.petugas.destroy');

    Route::get('/supervisor/penugasan', [PenjadwalanController::class, 'index'])->name('supervisor.penugasan');
    Route::post('/supervisor/jadwal', [PenjadwalanController::class, 'store'])->name('supervisor.jadwal.store');

    // List nasabah
    Route::match(['get', 'post'], '/supervisor/nasabah', [NasabahAdminController::class, 'index'])
        ->name('supervisor.nasabah.index');

    // Create, update, delete, import
    Route::post('/supervisor/nasabah/create', [NasabahAdminController::class, 'store'])->name('supervisor.nasabah.store');
    Route::put('/supervisor/nasabah/{nasabah}', [NasabahAdminController::class, 'update'])->name('supervisor.nasabah.update');
    Route::delete('/supervisor/nasabah/{nasabah}', [NasabahAdminController::class, 'destroy'])->name('supervisor.nasabah.destroy');
    Route::post('/supervisor/nasabah/import', [NasabahAdminController::class, 'import']);

    // Set session nasabah lalu redirect ke detail
    Route::post('/supervisor/nasabah/set-session', [NasabahAdminController::class, 'setSession'])
        ->name('supervisor.nasabah.setSession');

    // Detail nasabah (URL bersih, tanpa id)
    Route::get('/supervisor/nasabah/nasabah-detail', [NasabahAdminController::class, 'show'])
        ->name('supervisor.nasabah.show');

    // Blok Pasar
    Route::get('/supervisor/blok', [BlokPasarController::class, 'index'])->name('supervisor.blok.index');
    Route::post('/supervisor/blok', [BlokPasarController::class, 'store'])->name('supervisor.blok.store');
    Route::put('/supervisor/blok/{blok}', [BlokPasarController::class, 'update'])->name('supervisor.blok.update');
    Route::delete('/supervisor/blok/{blok}', [BlokPasarController::class, 'destroy'])->name('supervisor.blok.destroy');

    // Rekap & Laporan
    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/pdf', [RekapController::class, 'exportPdf'])->name('rekap.export.pdf');
    Route::get('/laporan-harian', [SupervisorLaporanController::class, 'harian'])->name('laporan.harian');

    // Laporan Dinamis (dengan filter & export)
    // GET untuk render laporan (baca filter dari session, paginate GET bawaan laravel)
    Route::get('/supervisor/laporan', [SupervisorLaporanController::class, 'index'])
        ->name('supervisor.laporan.index');
    // POST untuk simpan filter ke session lalu redirect ke index
    Route::post('/supervisor/laporan/filter', [SupervisorLaporanController::class, 'filter'])
        ->name('supervisor.laporan.filter');
    Route::get('/supervisor/laporan/export', [SupervisorLaporanController::class, 'export'])
        ->name('supervisor.laporan.export');
    Route::get('/supervisor/download-qr', [QrCodeDownloadController::class, 'downloadAll'])
        ->name('supervisor.download.qr');

    // Setoran Requests (Batal / Update)
    Route::get('/supervisor/setoran-requests', [SupervisorRequestController::class, 'index'])
        ->name('supervisor.setoran-requests.index');

    // Route untuk filter (POST)
    Route::get('/setoran-requests', [SupervisorRequestController::class, 'index'])
        ->name('setoran-requests.index'); // untuk get pending/history page

    Route::match(['get', 'post'], '/setoran-requests/filter', [SupervisorRequestController::class, 'filter'])
        ->name('setoran-requests.filter');

    Route::post('/supervisor/setoran-requests/{id}/approve', [SupervisorRequestController::class, 'approve'])
        ->name('supervisor.setoran-requests.approve');

    Route::post('/supervisor/setoran-requests/{id}/reject', [SupervisorRequestController::class, 'reject'])
        ->name('supervisor.setoran-requests.reject');

});

Route::get('/unauthorized-ip', function () {
    return Inertia::render('UnauthorizedIpModal', [
        'ip' => request()->ip(),
        'role' => 'supervisor', // bisa dinamis kalau mau
    ]);
})->name('unauthorized-ip')
    ->withoutMiddleware(['handleInertiaRequest']); // supaya Inertia intercept tetap bisa
/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';


// Route::get('/test-forbidden', function () {
//     abort(503); // ini akan render resources/views/errors/403.blade.php
// });
