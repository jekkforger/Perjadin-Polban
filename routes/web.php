<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuPengusulanController; // Anda mungkin tidak menggunakan ini jika pengusulanController sudah direname/digabung
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PengusulController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DataPegawaiController;
use App\Http\Controllers\DataMahasiswaController;
use App\Http\Controllers\WadirController;
use App\Http\Controllers\DirekturController;
use App\Http\Controllers\PelaksanaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BkuController;
use App\Http\Controllers\SekdirController;
use App\Http\Controllers\ParafController;
use App\Http\Controllers\HistoryController;

// Halaman awal untuk memilih role (akan diakses di root URL: '/')
Route::get('/', [LoginController::class, 'showSelectRoleForm'])->name('login.select-role');

// Halaman form login (misal: /login?role=pengusul)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');

// Proses POST data login
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

// Proses Logout (akan dipanggil dari tombol logout di navbar)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/change-password', [UserController::class, 'changePassword'])->name('user.change-password');

// --- Rute untuk Role Pengusul ---
Route::prefix('pengusul')->name('pengusul.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [PengusulController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengusulan', [PengusulController::class, 'pengusulan'])->name('new.pengusulan');
    Route::post('/pengusulan', [PengusulController::class, 'storePengusulan'])->name('store.pengusulan');
    Route::put('/pengusulan/{draft_id?}', [PengusulController::class, 'storePengusulan'])->name('update.pengusulan');
    Route::get('/status', [PengusulController::class, 'status'])->name('status');
    Route::get('/draft', [PengusulController::class, 'draft'])->name('draft');
    Route::post('/pengusul/draft/delete-session', [PengusulController::class, 'deleteDraftSession'])->name('pengusul.draft.delete.session');
    Route::delete('/draft/{id}', [PengusulController::class, 'deleteDraft'])->name('draft.delete');
    Route::get('/pengusul/pengusulan/{draft_id}', [PengusulController::class, 'editDraft'])->name('pengusulan');
    Route::get('/history', [PengusulController::class, 'history'])->name('history');
    Route::get('/pilihpengusul', [PengusulController::class, 'pilih'])->name('pilih');
    Route::post('/surat-tugas/storeStep1', [PengusulController::class, 'storeStep1'])->name('surat-tugas.storeStep1');
    Route::get('/surat-tugas/pelaksana-tugas', [PengusulController::class, 'selectPelaksanaTugas'])->name('pelaksana-tugas');
    Route::post('/surat-tugas/storeStep2', [PengusulController::class, 'storeStep2'])->name('surat-tugas.storeStep2');
    Route::get('/surat-tugas/preview', [PengusulController::class, 'preview'])->name('surat-tugas.preview');
    Route::get('/select-template', [PengusulController::class, 'showTemplateSelection'])->name('select-template');
    // ======================= AWAL REVISI =======================
    // Rute untuk mengecek ketersediaan nomor surat secara real-time
    Route::get('/check-nomor-surat', [PengusulController::class, 'checkNomorSurat'])->name('checkNomorSurat');
    
    // Rute untuk mendapatkan daftar nomor yang sudah digunakan
    Route::get('/used-nomor-surat', [PengusulController::class, 'getUsedNomorSurat'])->name('getUsedNomorSurat');
    Route::get('/get-latest-nomor', [PengusulController::class, 'getLatestNomorUrut'])->name('getLatestNomor');
    // ======================= AKHIR REVISI =======================
    Route::post('/generate-surat', [PengusulController::class, 'templateSuratTugas'])->name('generate-surat');
    Route::get('/surat-tugas/{id}/download', [PengusulController::class, 'downloadPdf'])->name('download.pdf'); // Perbaiki nama route
    Route::get('/history/{id}', [PengusulController::class, 'show'])->name('surat-tugas.show');
});

    Route::prefix('pelaksana')->name('pelaksana.')->group(function () {
        // ->middleware('role:pelaksana')
        Route::get('/dashboard', [PelaksanaController::class, 'dashboard'])->name('dashboard');
        Route::get('/bukti', [PelaksanaController::class, 'bukti'])->name('bukti');
        Route::get('/laporan', [PelaksanaController::class, 'laporan'])->name('laporan');
        Route::get('/dokumen', [PelaksanaController::class, 'dokumen'])->name('dokumen');
        Route::get('/dokumen/{id}/download', [PelaksanaController::class, 'downloadPdf'])->name('download_pdf');
        Route::get('/status-laporan', [PelaksanaController::class, 'statusLaporan'])->name('statusLaporan');
        Route::get('/bukti/{id}/lihat-bukti', [PelaksanaController::class, 'lihatBukti'])->name('lihatBukti');
        Route::post('/pelaksana/buat-laporan', [PelaksanaController::class, 'buatLaporan'])->name('buatLaporan');
        Route::post('/pelaksana/upload-lampiran', [PelaksanaController::class, 'uploadLampiran'])->name('uploadLampiran');        
        Route::delete('/bukti/{laporan_id}/lampiran/{dokumen_lampiran_id}', [PelaksanaController::class, 'destroyLampiran'])->name('bukti.destroyLampiran');
        // Route::get('/bukti/{laporan_id}/lampiran/{lampiran_id}', [PelaksanaController::class, 'showLampiran'])->name('bukti.showLampiran');
        // Route::get('/bukti/{laporan_id}/lampiran/{lampiran_id}/edit', [PelaksanaController::class, 'editLampiran'])->name('bukti.editLampiran');
        // Route::put('/bukti/{laporan_id}/lampiran/{lampiran_id}', [PelaksanaController::class, 'updateLampiran'])->name('bukti.updateLampiran');
    });


Route::prefix('bku')->name('bku.')->group(function () {
    Route::get('/dashboard', [BkuController::class, 'dashboard'])->name('dashboard');
    Route::get('/bukti', [BkuController::class, 'bukti'])->name('bukti');
    Route::get('/laporan', [BkuController::class, 'laporan'])->name('laporan');
    Route::get('/laporan/{laporan_id}', [BkuController::class, 'showLaporan'])->name('laporan.show');
    Route::get('/bukti/{id}/lihat-laporan', [BkuController::class, 'lihatLaporan'])->name('lihatLaporan');
    Route::delete('/bukti/{laporan_id}/lampiran/{dokumen_lampiran_id}', [BkuController::class, 'destroyLampiran'])->name('destroyLampiran');
    Route::post('/bku/laporan/{id}/terima', [BkuController::class, 'terimaLaporan'])->name('terimaLaporan');
    Route::post('/bku/laporan/{id}/kembalikan', [BkuController::class, 'kembalikanLaporan'])->name('kembalikanLaporan');
});

// --- Rute untuk Wakil Direktur (I-IV) ---
Route::prefix('wadir')->name('wadir.')->group(function () {
    Route::get('/dashboard', [WadirController::class, 'dashboard'])->name('dashboard');

    // Rute baru untuk daftar Persetujuan
    Route::get('/persetujuan', [WadirController::class, 'persetujuan'])->name('persetujuan');
    // Rute untuk detail review surat tugas
    Route::get('/surat-tugas/{id}/review', [WadirController::class, 'reviewSuratTugas'])->name('review.surat_tugas');
    // Rute untuk memproses keputusan Wadir
    Route::post('/surat-tugas/{id}/process-review', [WadirController::class, 'processSuratTugasReview'])->name('process.review.surat_tugas');
    Route::get('/paraf', [ParafController::class, 'index'])->name('paraf');
    Route::post('/paraf/upload', [ParafController::class, 'upload'])->name('paraf.upload'); // Menggunakan 'upload' sebagai nama method di controller
    Route::delete('/paraf/{id}', [ParafController::class, 'delete'])->name('paraf.delete'); // Menggunakan 'delete' sebagai nama method di controller
});

Route::prefix('direktur')->name('direktur.')->group(function () {
    // ->middleware('role:direktur')
    Route::get('/dashboard', [DirekturController::class, 'dashboard'])->name('dashboard');
    Route::get('/persetujuan', [DirekturController::class, 'persetujuan'])->name('persetujuan');
    Route::get('/paraf', [DirekturController::class, 'paraf'])->name('paraf'); // Untuk upload TTD
    Route::post('/paraf/upload', [DirekturController::class, 'uploadParaf'])->name('paraf.upload'); // Untuk proses upload TTD
    Route::delete('/paraf/delete', [DirekturController::class, 'deleteParaf'])->name('paraf.delete'); // Untuk hapus TTD
    Route::get('/surat-tugas/{id}/review', [DirekturController::class, 'reviewSuratTugas'])->name('review.surat_tugas');
    Route::post('/surat-tugas/{id}/process-review', [DirekturController::class, 'processSuratTugasReview'])->name('process.review.surat_tugas');
});

// routes/web.php
Route::prefix('sekdir')->name('sekdir.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [SekdirController::class, 'dashboard'])->name('dashboard');
    Route::get('/penomoran-surat', [SekdirController::class, 'daftarPenomoran'])->name('nomorSurat');
    Route::get('/penomoran-surat/{id}/review', [SekdirController::class, 'reviewSurat'])->name('nomorSurat.review');
    // Ganti nama metode di rute ini
    Route::post('/penomoran-surat/{id}/assign', [SekdirController::class, 'assignFinalNumber'])->name('nomorSurat.assign');
});

// --- Rute untuk Admin ---
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/pegawai', [AdminController::class, 'pegawai'])->name('pegawai');
    Route::get('/mahasiswa', [AdminController::class, 'mahasiswa'])->name('mahasiswa');
    Route::get('/akun', [AdminController::class, 'akun'])->name('akun');
    Route::get('/akun/create', [AdminController::class, 'create'])->name('create');
    Route::post('/akun', [AdminController::class, 'store'])->name('store');
    Route::delete('/akun/{id}', [AdminController::class, 'destroyAkun'])->name('akun.destroyAkun');
    Route::get('/cek-email', [AdminController::class, 'cekEmail'])->name('cek.email');
    Route::post('/pegawai/{id}/toggle-aktif', [AdminController::class, 'toggleStatus']);
    Route::get('/template-surat', [AdminController::class, 'editTemplate'])->name('edit');
    Route::put('/template-surat', [AdminController::class, 'updateTemplate'])->name('update');
    Route::post('/pegawai/import', [AdminController::class, 'importPegawai'])->name('pegawai.import');
});

// Anda bisa membuat Controller terpisah untuk User Profile/Settings
Route::middleware('auth')->group(function () {
    // Rute untuk halaman Ganti Password
    Route::get('/user/change-password', function () {
        return view('user.change-password'); // Nanti Anda perlu membuat view ini
    })->name('user.change-password.form');

    // Contoh rute lain untuk profile jika ada
    Route::get('/user/profile', function () {
        return view('user.profile');
    })->name('user.profile');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/surat-tugas/{id}/detail', [HistoryController::class, 'show'])->name('history.show');
    Route::get('/surat-tugas/{id}/download-pdf', [PengusulController::class, 'downloadPdf'])->name('history.download_pdf');
});