<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratTugas;
use App\Models\TemplateSurat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule; // Import Rule for unique validation ignoring current ID

class SekdirController extends Controller
{
    /**
     * Menampilkan dashboard Sekdir.
     */
    public function dashboard() {
        $menungguPenomoran = SuratTugas::where('status_surat', 'pending_sekdir_numbering')->count();
        // Anda bisa menambahkan statistik lain di sini jika dibutuhkan
        // Contoh: $sudahDiproses = SuratTugas::where('sekdir_processor_id', Auth::id())->count();

        return view('layouts.sekdir.dashboard', compact('menungguPenomoran'));
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
    public function reviewSurat($id) {
        // Load relasi yang dibutuhkan untuk menampilkan detail lengkap di preview surat
        $suratTugas = SuratTugas::with(['pengusul', 'detailPelaksanaTugas.personable', 'wadirApprover'])
                                ->findOrFail($id);

        $suratSettings = TemplateSurat::find(1);
        if (!$suratSettings) {
            $suratSettings = new TemplateSurat();
            $suratSettings->nama_kementerian = '';
            $suratSettings->nama_direktur = '';
            $suratSettings->nip_direktur = '';
            $suratSettings->tembusan_default = [];
        }

        // Validasi status: pastikan hanya surat yang pending di Sekdir yang bisa di-review
        if ($suratTugas->status_surat !== 'pending_sekdir_numbering') {
            return redirect()->route('sekdir.nomorSurat')->with('error', 'Surat ini tidak lagi dalam antrean penomoran Anda.');
        }

        // =================== AWAL LOGIKA BARU ===================
        $tahunSekarang = now()->year;
        $tahunList = [$tahunSekarang, $tahunSekarang - 1];

        // Ambil nomor urut tertinggi dari SEMUA surat tugas yang sudah punya nomor resmi di tahun ini.
        // Kita menggunakan raw query untuk mengekstrak bagian nomor urut dari string.
        $lastSurat = SuratTugas::whereNotNull('nomor_surat_tugas_resmi')
            ->where('tahun_nomor_surat', $tahunSekarang) // Asumsi kita akan menyimpan tahun juga
            ->orderBy('nomor_urutan_surat', 'desc') // Asumsi kita akan menyimpan nomor urut juga
            ->first();

        $saranNomorUrut = $lastSurat ? ((int) $lastSurat->nomor_urutan_surat + 1) : 1;
        // =================== AKHIR LOGIKA BARU ===================

        // Kirim data baru ke view
        return view('layouts.sekdir.review', compact('suratTugas', 'suratSettings', 'saranNomorUrut', 'tahunList'));
    }

    /**
     * Menyimpan nomor surat tugas resmi (final) dan meneruskan ke Direktur untuk TTD.
     */
    public function assignFinalNumber(Request $request, $id) {
        // Validasi nomor surat resmi: harus unik dan tidak boleh kosong
        // =================== AWAL LOGIKA BARU ===================
        // Validasi untuk setiap bagian nomor surat
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
        $isUnique = !SuratTugas::where('nomor_surat_tugas_resmi', $nomorSuratResmi)->where('surat_tugas_id', '!=', $id)->exists();
        if (!$isUnique) {
            return back()->withErrors(['nomor_urutan_surat' => 'Kombinasi nomor surat ini sudah terpakai.'])->withInput();
        }
        // =================== AKHIR LOGIKA BARU ===================

        $suratTugas = SuratTugas::findOrFail($id);

        // Verifikasi ulang status untuk keamanan (misalnya jika ada double submit atau diakses tab lain)
        if ($suratTugas->status_surat !== 'pending_sekdir_numbering') {
            return redirect()->route('sekdir.nomorSurat')->with('error', 'Status surat sudah berubah atau tidak valid.');
        }
        
        // Simpan nomor surat resmi (final)
        $suratTugas->nomor_surat_tugas_resmi = $request->nomor_surat_tugas_resmi; 
        
        // Catat tanggal dan siapa yang memproses di Sekdir
        $suratTugas->tanggal_penomoran_sekdir = Carbon::now();
        $suratTugas->sekdir_processor_id = Auth::id();

        // Ganti status untuk diteruskan ke antrean review Direktur
        $suratTugas->status_surat = 'pending_direktur_review'; 
        $suratTugas->save();
//
        return redirect()->route('sekdir.nomorSurat')->with('success_message', 'Nomor surat resmi berhasil disimpan dan surat telah diteruskan ke Direktur untuk review dan Tanda Tangan.');
    }
}