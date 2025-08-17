<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratTugas;
use App\Models\TemplateSurat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule; // Import Rule for unique validation ignoring current ID
use Illuminate\Support\Facades\DB;

class SekdirController extends Controller
{
    /**
     * Menampilkan dashboard Sekdir.
     */
    public function dashboard(Request $request)
    {
        $search = $request->input('search');

        // --- STATISTIK ---
        // Lingkup Sekdir: Semua surat yang sudah melewati Wadir
        $scopeSekdir = SuratTugas::whereNotIn('status_surat', [
            'draft', 'pending_wadir_review', 'reverted_by_wadir', 'rejected_by_wadir'
        ]);

        // 1. Total Pengusulan (yang relevan untuk Sekdir)
        $totalUsulan = (clone $scopeSekdir)->count();

        // 2. Usulan Baru (yang perlu penomoran oleh Sekdir)
        $usulanBaru = (clone $scopeSekdir)->where('status_surat', 'pending_sekdir_numbering')->count();

        // 3. Bertugas (status 'diterbitkan' dan dalam rentang tanggal pelaksanaan)
        $sedangBertugas = SuratTugas::where('status_surat', 'diterbitkan')
                                    ->whereDate('tanggal_berangkat', '<=', Carbon::today())
                                    ->whereDate('tanggal_kembali', '>=', Carbon::today())
                                    ->count();

        // --- DATA TABEL DETAIL ---
        // Ambil data untuk tabel dari lingkup Sekdir
        $detailList = (clone $scopeSekdir)
            ->when($search, function ($query, $search) {
                // Logika pencarian
                $query->where('perihal_tugas', 'like', "%{$search}%")
                      ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$search}%")
                      ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$search}%")
                      ->orWhere('sumber_dana', 'like', "%{$search}%");
            })
            ->orderBy('updated_at', 'desc') // Tampilkan yang terbaru di atas
            ->paginate(10)
            ->appends($request->query());

        // Kirim semua data yang dibutuhkan ke view
        return view('layouts.sekdir.dashboard', compact(
            'totalUsulan',
            'usulanBaru',
            'sedangBertugas',
            'detailList',
            'search'
        ));
    }

    /**
     * Menampilkan daftar surat yang menunggu penomoran final oleh Sekdir.
     */
    public function daftarPenomoran(Request $request) {
        $search = $request->input('search'); // Tambahkan variabel search untuk filter
        $perPage = $request->input('per_page', 10);

        $daftarSurat = SuratTugas::where('status_surat', 'pending_sekdir_numbering')
                                 ->with('pengusul', 'wadirApprover') // Load relasi yang mungkin dibutuhkan di tabel
                                 ->when($search, function ($query) use ($search) {
                                     // Filter berdasarkan perihal_tugas atau nomor_surat_usulan_jurusan
                                     $query->where('perihal_tugas', 'like', "%{$search}%")
                                           ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$search}%");
                                 })
                                 ->orderBy('tanggal_paraf_wadir', 'asc') // Urutkan berdasarkan waktu paraf Wadir
                                 ->paginate($perPage);
                                 
        return view('layouts.sekdir.nomor_surat', compact('daftarSurat'));
    }

    /**
     * Menampilkan halaman review untuk satu surat tugas, tempat Sekdir menginput nomor final.
     */
    public function reviewSurat($id)
    {
        $suratTugas = SuratTugas::with(['pengusul', 'detailPelaksanaTugas.personable', 'wadirApprover'])
                                ->findOrFail($id);
        $suratSettings = TemplateSurat::find(1) ?? new TemplateSurat();
        if ($suratTugas->status_surat !== 'pending_sekdir_numbering') {
            return redirect()->route('sekdir.nomorSurat')->with('error', 'Surat ini tidak lagi dalam antrean penomoran Anda.');
        }

        // ===================================================================
        // <-- AWAL BLOK KODE YANG DIPERBAIKI (VERSI FINAL) -->
        // ===================================================================

        $tahunSekarang = now()->year;
    $tahunList = [$tahunSekarang, $tahunSekarang - 1];
    
    // HANYA CARI DARI KOLOM STRING NOMOR RESMI
    // 1. Ambil SEMUA nomor resmi yang tidak kosong
    $semuaNomorResmi = SuratTugas::whereNotNull('nomor_surat_tugas_resmi')->pluck('nomor_surat_tugas_resmi');

    // 2. Proses di PHP untuk menemukan nomor tertinggi
    $lastNomorUrut = 0;
    foreach ($semuaNomorResmi as $nomor) {
        $parts = explode('/', $nomor);
        // Cek jika formatnya benar dan tahunnya cocok
        if (count($parts) > 0 && is_numeric($parts[0])) {
            $nomorUrutSaatIni = (int)$parts[0];
            if ($nomorUrutSaatIni > $lastNomorUrut) {
                // Cek apakah tahunnya sama dengan tahun ini (opsional tapi bagus)
                $tahunDiNomor = end($parts);
                if ($tahunDiNomor == $tahunSekarang) {
                    $lastNomorUrut = $nomorUrutSaatIni;
                }
            }
        }
    }

    $saranNomorUrut = $lastNomorUrut + 1;
    $nomorTerakhir = $lastNomorUrut > 0 ? $lastNomorUrut : 'Belum ada';


            // ===================================================================
            // <-- AKHIR BLOK KODE YANG DIPERBAIKI -->
            // ===================================================================

            return view('layouts.sekdir.review', compact('suratTugas', 'suratSettings', 'saranNomorUrut', 'nomorTerakhir', 'tahunList'));
        }

    /**
     * Menyimpan nomor surat tugas resmi (final) dan meneruskan ke Direktur untuk TTD.
     */
    public function assignFinalNumber(Request $request, $id)
    {
        // Validasi input terpisah dari modal
        $request->validate([
            'nomor_urutan_surat' => 'required|numeric',
            'kode_unit_kerja' => 'required|string',
            'kode_perihal' => 'required|string',
            'tahun_nomor_surat' => 'required|digits:4',
        ]);

        // Gabungkan kembali menjadi satu nomor surat
        $nomorSuratResmi = $request->nomor_urutan_surat . '/' .
                        $request->kode_unit_kerja . '/' .
                        $request->kode_perihal . '/' .
                        $request->tahun_nomor_surat;

        // Validasi keunikan nomor yang sudah digabung
        $isUnique = !SuratTugas::where('nomor_surat_tugas_resmi', $nomorSuratResmi)
                                ->where('surat_tugas_id', '!=', $id)
                                ->exists();
        if (!$isUnique) {
            return back()->withErrors(['nomor_urutan_surat' => 'Kombinasi nomor surat ini sudah terpakai.'])->withInput();
        }

        $suratTugas = SuratTugas::findOrFail($id);
        if ($suratTugas->status_surat !== 'pending_sekdir_numbering') {
            return redirect()->route('sekdir.nomorSurat')->with('error', 'Status surat sudah berubah atau tidak valid.');
        }

        // ===================================================================
        // <-- AWAL BLOK KODE YANG DIPERBAIKI -->
        // ===================================================================
        
        // Simpan nomor surat resmi (string lengkap)
        $suratTugas->nomor_surat_tugas_resmi = $nomorSuratResmi;
        
        // PENTING: Simpan juga komponen-komponennya ke kolom masing-masing!
        $suratTugas->nomor_urutan_surat = $request->nomor_urutan_surat;
        $suratTugas->kode_unit_kerja = $request->kode_unit_kerja;
        $suratTugas->kode_perihal = $request->kode_perihal;
        $suratTugas->tahun_nomor_surat = $request->tahun_nomor_surat;

        // ===================================================================
        // <-- AKHIR BLOK KODE YANG DIPERBAIKI -->
        // ===================================================================

        // Catat tanggal dan siapa yang memproses di Sekdir
        $suratTugas->tanggal_penomoran_sekdir = Carbon::now();
        $suratTugas->sekdir_processor_id = Auth::id();
        $suratTugas->status_surat = 'pending_direktur_review';
        $suratTugas->save();

        return redirect()->route('sekdir.nomorSurat')->with('success_message', 'Nomor surat resmi berhasil disimpan dan surat telah diteruskan ke Direktur.');
    }
}