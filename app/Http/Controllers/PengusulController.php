<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Mahasiswa;
use App\Models\SuratTugas;
use App\Models\TemplateSurat;
use App\Models\DetailPelaksanaTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;


class PengusulController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();
        $totalUsulan = SuratTugas::where('user_id', $userId)->count();
        $laporanSelesai = SuratTugas::where('user_id', $userId)
                                    ->where('status_surat', 'laporan_selesai')->count();
        $laporanBelumSelesai = SuratTugas::where('user_id', $userId)
                                         ->where('status_surat', 'approved_by_direktur')->count();
        $sedangBertugas = SuratTugas::where('user_id', $userId)
                                    ->where('status_surat', 'diterbitkan')
                                    ->whereDate('tanggal_berangkat', '<=', Carbon::today())
                                    ->whereDate('tanggal_kembali', '>=', Carbon::today())
                                    ->count();
        $dikembalikan = SuratTugas::where('user_id', $userId)
                                  ->whereIn('status_surat', ['rejected_by_wadir', 'reverted_by_wadir', 'rejected_by_direktur', 'reverted_by_direktur'])->count();
        $latestPengusulan = SuratTugas::where('user_id', $userId)
                                      ->orderBy('created_at', 'desc')
                                      ->take(5)
                                      ->get();

        return view('layouts.pengusul.dashboard', compact(
            'totalUsulan', 'laporanSelesai', 'laporanBelumSelesai', 'sedangBertugas', 'dikembalikan', 'latestPengusulan'
        ));
    }

    public function pilih(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $query = Pegawai::query();
        if ($search) {
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
        }
        $pegawais = $query->paginate($perPage)->withQueryString();
        return view('layouts.pengusul.dataPengusul', compact('pegawais'));
    }

    public function status(Request $request)
    {
        $userId = Auth::id();
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $statusPengusulan = SuratTugas::where('user_id', $userId)
                                    ->when($search, function ($query) use ($search) {
                                        return $query->where('perihal_tugas', 'like', "%{$search}%")
                                                     ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$search}%");
                                    })
                                    ->orderBy('created_at', 'desc')
                                    ->paginate($perPage)
                                    ->appends(['search' => $search, 'per_page' => $perPage]);
        return view('layouts.pengusul.status', compact('statusPengusulan', 'search'));
    }

    public function draft(Request $request)
    {
        $userId = Auth::id();
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $draftPengusulan = SuratTugas::where('user_id', $userId)
                                    ->where('status_surat', 'draft')
                                    ->when($search, function ($query) use ($search) {
                                        return $query->where('perihal_tugas', 'like', "%{$search}%")
                                                    ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$search}%");
                                    })
                                    ->orderBy('created_at', 'desc')
                                    ->paginate($perPage)
                                    ->appends(['search' => $search, 'per_page' => $perPage]);
        return view('layouts.pengusul.draft', compact('draftPengusulan', 'search'));
    }

    public function deleteDraft($id)
    {
        $userId = Auth::id();
        $draft = SuratTugas::where('surat_tugas_id', $id)
                        ->where('user_id', $userId)
                        ->where('status_surat', 'draft')
                        ->firstOrFail();
        \DB::beginTransaction();
        try {
            if ($draft->path_file_surat_usulan && Storage::disk('public')->exists($draft->path_file_surat_usulan)) {
                Storage::disk('public')->delete($draft->path_file_surat_usulan);
            }
            $draft->detailPelaksanaTugas()->delete();
            $draft->delete();
            \DB::commit();
            return redirect()->route('pengusul.draft')->with('success_message', 'Draft berhasil dihapus.');
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error menghapus draft Surat Tugas: ' . $e->getMessage(), [
                'id' => $id,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('pengusul.draft')->with('error', 'Terjadi kesalahan saat menghapus draft.');
        }
    }

    public function editDraft($draft_id)
{
    $draft = SuratTugas::with(['detailPelaksanaTugas.personable'])
        ->where('surat_tugas_id', $draft_id)
        ->where('user_id', Auth::id())
        ->whereIn('status_surat', ['draft', 'reverted_by_wadir', 'reverted_by_direktur'])
        ->firstOrFail();

    $pegawais = Pegawai::all();
    $mahasiswa = Mahasiswa::all();
    
    // <-- TAMBAHKAN BLOK KODE INI -->
    $suratSettings = TemplateSurat::find(1);
    if (!$suratSettings) {
        $suratSettings = new TemplateSurat(); // Buat objek kosong jika belum ada
    }
    // <-- AKHIR BLOK KODE TAMBAHAN -->

    $tahunSekarang = now()->year;
    $tahunList = [$tahunSekarang, $tahunSekarang - 1];
    $kodePerihal = 'RT.01.00';
    $kodePengusul = Auth::user()->kode_pengusul ?? '';

    $draftData = [
        'nama_kegiatan' => $draft->perihal_tugas,
        'tempat_kegiatan' => $draft->tempat_kegiatan,
        'diusulkan_kepada' => $draft->diusulkan_kepada,
        'surat_undangan' => $draft->path_file_surat_usulan ? asset('storage/' . $draft->path_file_surat_usulan) : null,
        'nama_penyelenggara' => $draft->nama_penyelenggara,
        'tanggal_pelaksanaan' => Carbon::parse($draft->tanggal_berangkat)->format('d/m/Y') . ' → ' . Carbon::parse($draft->tanggal_kembali)->format('d/m/Y'),
        'alamat_kegiatan' => $draft->alamat_kegiatan,
        'provinsi' => $draft->kota_tujuan,
        'nomor_surat_usulan' => $draft->nomor_surat_usulan_jurusan,
        'pembiayaan' => $draft->sumber_dana,
        'pagu_desentralisasi' => $draft->pagu_desentralisasi,
        'pagu_nominal' => $draft->pagu_nominal,
    ];

    $selectedPersonel = $draft->detailPelaksanaTugas->map(function ($detail) {
        return [
            'id' => $detail->personable_id,
            'type' => $detail->personable_type === Pegawai::class ? 'pegawai' : 'mahasiswa',
            'nama' => $detail->personable->nama,
            'nip' => $detail->personable_type === Pegawai::class ? ($detail->personable->nip ?? '-') : null,
            'pangkat' => $detail->personable_type === Pegawai::class ? ($detail->personable->pangkat ?? '-') : null,
            'golongan' => $detail->personable_type === Pegawai::class ? ($detail->personable->golongan ?? '-') : null,
            'jabatan' => $detail->personable_type === Pegawai::class ? ($detail->personable->jabatan ?? '-') : null,
            'nim' => $detail->personable_type === Mahasiswa::class ? ($detail->personable->nim ?? '-') : null,
            'jurusan' => $detail->personable_type === Mahasiswa::class ? ($detail->personable->jurusan ?? '-') : null,
            'prodi' => $detail->personable_type === Mahasiswa::class ? ($detail->personable->prodi ?? '-') : null,
        ];
    })->toArray();
    
    // <-- PERBAIKI JUGA BAGIAN compact() -->
    return view('layouts.pengusul.pengusulan', compact(
        'pegawais', 
        'mahasiswa', 
        'draftData', 
        'selectedPersonel', 
        'draft',
        'suratSettings', // <-- Pastikan ini ditambahkan
        'tahunList',
        'kodePerihal',
        'kodePengusul'
    ));
}

    public function history(Request $request)
{
    $userId = Auth::id();
    $search = $request->input('search');
    $perPage = $request->input('per_page', 10);
    $historyPengusulan = SuratTugas::where('user_id', $userId)
                                  ->whereIn('status_surat', ['approved_by_direktur', 'diterbitkan', 'rejected_by_direktur', 'laporan_selesai'])
                                  ->when($search, function ($query) use ($search) {
                                      return $query->where('perihal_tugas', 'like', "%{$search}%")
                                                   ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$search}%")
                                                   ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$search}%");
                                  })
                                  ->orderBy('updated_at', 'desc')
                                  ->paginate($perPage)
                                  ->appends(['search' => $search, 'per_page' => $perPage]);
    return view('layouts.pengusul.history', compact('historyPengusulan', 'search'));
}

    public function pengusulan()
    {
        $pegawais = Pegawai::all();
        $mahasiswa = Mahasiswa::all();

        $suratSettings = TemplateSurat::find(1);
        if (!$suratSettings) {
            $suratSettings = new TemplateSurat();
            $suratSettings->nama_kementerian = '';
            $suratSettings->nama_direktur = '';
            $suratSettings->nip_direktur = '';
            $suratSettings->tembusan_default = [];
        }
        
        $tahunSekarang = now()->year;
        $tahunList = [$tahunSekarang, $tahunSekarang - 1];
        $kodePerihal = 'RT.01.00';

        $kodePengusul = Auth::user()->kode_pengusul ?? ''; 

        return view('layouts.pengusul.pengusulan', compact('pegawais', 'mahasiswa', 'kodePengusul', 'tahunList', 'kodePerihal', 'suratSettings'));
    }

    public function storePengusulan(Request $request, $draft_id = null)
    {
        Log::info('Menerima data pengusulan:', $request->all());

        // 1. Menggabungkan komponen nomor surat untuk validasi
        $kodePerihal = 'RT.01.00'; // Kode Perihal statis
        $nomorSuratGabungan = $request->nomor_urutan_surat . '/'
            . $request->kode_pengusul . '/'
            . $kodePerihal . '/'
            . $request->tahun_nomor_surat;

        // Menambahkan nomor surat gabungan ke request untuk divalidasi
        $request->merge([
            'nomor_surat_usulan_jurusan' => $nomorSuratGabungan
        ]);

        // 2. Aturan Validasi
        // Rule unik akan mengabaikan draf saat ini jika sedang dalam mode edit
        $uniqueRule = Rule::unique('surat_tugas', 'nomor_surat_usulan_jurusan');
        if ($draft_id) {
            $uniqueRule->ignore($draft_id, 'surat_tugas_id');
        }

        $validator = Validator::make($request->all(), [
            'nama_kegiatan'                 => 'required|string|max:255',
            'diusulkan_kepada'              => 'required|string',
            'surat_undangan'                => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'nama_penyelenggara'            => 'nullable|string|max:255',
            'tanggal_pelaksanaan'           => 'required|string',
            'provinsi'                      => 'required|string',
            'pembiayaan'                    => 'required|string|in:Polban,Penyelenggara,Polban dan Penyelenggara',
            'pegawai_ids'                   => 'nullable|array',
            'pegawai_ids.*'                 => 'exists:pegawai,id',
            'mahasiswa_ids'                 => 'nullable|array',
            'mahasiswa_ids.*'               => 'exists:mahasiswa,id',
            'status_pengajuan'              => 'required|string|in:draft,diajukan',
            'nomor_urutan_surat'            => 'required|string|max:10',
            'kode_pengusul'                 => 'required|string|max:10',
            'tahun_nomor_surat'             => 'required|string|digits:4',
            'nomor_surat_usulan_jurusan'    => ['required', $uniqueRule], // Asumsi $uniqueRule sudah didefinisikan di atasnya
            
            // Aturan baru untuk multi-lokasi
            'lokasi'                        => 'required|array|min:1',
            'lokasi.*.tempat'               => 'required|string|max:255',
            'lokasi.*.alamat'               => 'required|string|max:255',

        ], [
            'nomor_surat_usulan_jurusan.unique' => 'Nomor surat usulan ini sudah pernah digunakan.',
            // Pesan error kustom untuk lokasi
            'lokasi.required' => 'Minimal harus ada satu lokasi kegiatan.',
            'lokasi.*.tempat.required' => 'Tempat kegiatan tidak boleh kosong.',
            'lokasi.*.alamat.required' => 'Alamat kegiatan tidak boleh kosong.',
        ]);

        if ($validator->fails()) {
            Log::error('Validasi Pengusulan Gagal:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        // 3. Penanganan Upload File
        $pathSuratUndangan = null;
        // Jika sedang mengedit draf, cek apakah ada file lama untuk dihapus
        if ($draft_id) {
            $existingDraft = SuratTugas::find($draft_id);
            if ($existingDraft) {
                $pathSuratUndangan = $existingDraft->path_file_surat_usulan; // Pertahankan file lama jika tidak ada upload baru
            }
        }

        if ($request->hasFile('surat_undangan')) {
            // Jika ada file baru diupload, hapus file lama (jika ada)
            if ($pathSuratUndangan && Storage::disk('public')->exists($pathSuratUndangan)) {
                Storage::disk('public')->delete($pathSuratUndangan);
            }
            // Simpan file baru
            $pathSuratUndangan = $request->file('surat_undangan')->store('surat_undangan', 'public');
        }

        // 4. Memulai Transaksi Database
        try {
            \DB::beginTransaction();

            // Parsing Tanggal Berangkat dan Kembali
            $tanggal_pelaksanaan = $request->tanggal_pelaksanaan;
            $tanggal_berangkat = null;
            $tanggal_kembali = null;

            if (strpos($tanggal_pelaksanaan, ' → ') !== false) {
                $tanggal_parts = explode(' → ', $tanggal_pelaksanaan);
                $tanggal_berangkat = Carbon::createFromFormat('d/m/Y', trim($tanggal_parts[0]))->format('Y-m-d');
                $tanggal_kembali = Carbon::createFromFormat('d/m/Y', trim($tanggal_parts[1]))->format('Y-m-d');
            } else {
                $tanggal_berangkat = Carbon::createFromFormat('d/m/Y', trim($tanggal_pelaksanaan))->format('Y-m-d');
                $tanggal_kembali = $tanggal_berangkat; // Jika hanya satu tanggal
            }

            // 5. Menyiapkan Data untuk Disimpan
            $suratTugasData = [
                'user_id'                    => auth()->id(),
                'nomor_surat_usulan_jurusan' => $nomorSuratGabungan,
                'diusulkan_kepada'           => $request->diusulkan_kepada,
                'perihal_tugas'              => $request->nama_kegiatan,
                'lokasi_kegiatan'            => $request->lokasi, // <-- Ini yang benar
                'kota_tujuan'                => $request->provinsi,
                'tanggal_berangkat'          => $tanggal_berangkat,
                'tanggal_kembali'            => $tanggal_kembali,
                'path_file_surat_usulan'     => $pathSuratUndangan,
                'sumber_dana'                => $request->pembiayaan,
                'pagu_desentralisasi'        => $request->boolean('pagu_desentralisasi'),
                'pagu_nominal'               => $request->input('pagu_nominal') ?? null,
                'nama_penyelenggara'         => $request->input('nama_penyelenggara'),
                'status_surat'               => $request->input('status_pengajuan') === 'diajukan' ? 'pending_wadir_review' : 'draft',
                'nomor_urutan_surat'         => $request->nomor_urutan_surat,
                'kode_perihal'               => $kodePerihal, // Asumsi $kodePerihal sudah ada
                'tahun_nomor_surat'          => $request->tahun_nomor_surat,
            ];

            // 6. Menyimpan atau Memperbarui Data Surat Tugas
            if ($draft_id) {
                // Mode Update Draft
                $suratTugas = SuratTugas::where('surat_tugas_id', $draft_id)
                                        ->where('user_id', auth()->id())
                                        ->where('status_surat', 'draft')
                                        ->firstOrFail();
                $suratTugas->update($suratTugasData);
                // Hapus detail pelaksana lama untuk diganti dengan yang baru
                $suratTugas->detailPelaksanaTugas()->delete();
            } else {
                // Mode Create Baru
                $suratTugas = SuratTugas::create($suratTugasData);
            }

            // 7. Menyimpan Detail Pelaksana Tugas
            $pegawai_ids = $request->input('pegawai_ids', []);
            $mahasiswa_ids = $request->input('mahasiswa_ids', []);
            
            foreach ($pegawai_ids as $pegawai_id) {
                DetailPelaksanaTugas::create([
                    'surat_tugas_id'  => $suratTugas->surat_tugas_id,
                    'personable_id'   => $pegawai_id,
                    'personable_type' => Pegawai::class,
                    'status_sebagai'  => 'Peserta', // Anda bisa membuat ini dinamis jika perlu
                ]);
            }

            foreach ($mahasiswa_ids as $mahasiswa_id) {
                DetailPelaksanaTugas::create([
                    'surat_tugas_id'  => $suratTugas->surat_tugas_id,
                    'personable_id'   => $mahasiswa_id,
                    'personable_type' => Mahasiswa::class,
                    'status_sebagai'  => 'Peserta', // Anda bisa membuat ini dinamis jika perlu
                ]);
            }

            // 8. Commit Transaksi dan Kirim Respon
            \DB::commit();
            Log::info('Pengusulan berhasil diproses. Status: ' . $request->input('status_pengajuan') . ', Surat Tugas ID: ' . $suratTugas->surat_tugas_id);
            
            $message = 'Pengusulan berhasil ' . ($request->input('status_pengajuan') === 'draft' ? 'disimpan sebagai draf' : 'diajukan dan menunggu review Wakil Direktur') . '!';

            return response()->json(['success' => true, 'message' => $message], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            // Hapus file yang terupload jika terjadi error database
            if ($request->hasFile('surat_undangan') && isset($pathSuratUndangan) && Storage::disk('public')->exists($pathSuratUndangan)) {
                Storage::disk('public')->delete($pathSuratUndangan);
            }
            
            Log::error('Error menyimpan pengusulan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal saat menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX untuk mendapatkan daftar nomor surat yang sudah digunakan.
     */
    public function getUsedNomorSurat()
    {
        $usedNumbers = SuratTugas::where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subDays(30)) // Ambil 30 hari terakhir
            ->orderBy('created_at', 'desc')
            ->take(10) // Ambil 10 terbaru
            ->pluck('nomor_surat_usulan_jurusan');

        return response()->json(['used_numbers' => $usedNumbers]);
    }

    /**
     * Endpoint AJAX untuk memeriksa ketersediaan nomor surat usulan.
     */
    public function checkNomorSurat(Request $request)
    {
        $nomorUrutan = $request->query('nomor_urutan_surat');
        $kodePengusul = $request->query('kode_pengusul');
        $kodePerihal = $request->query('kode_perihal');
        $tahun = $request->query('tahun_nomor_surat');

        $fullNomorSurat = $nomorUrutan . '/' . $kodePengusul . '/' . $kodePerihal . '/' . $tahun;
        
        $isUsed = SuratTugas::where('nomor_surat_usulan_jurusan', $fullNomorSurat)->exists();

        return response()->json(['is_used' => $isUsed]);
    }

    /**
     * Endpoint AJAX untuk mengambil nomor urut terakhir yang digunakan oleh pengusul.
     */
    public function getLatestNomorUrut(Request $request)
    {
        $request->validate(['tahun' => 'required|digits:4']);

        $tahun = $request->query('tahun');
        $kodePengusul = Auth::user()->kode_pengusul;
        $kodePerihal = 'RT.01.00'; // Pastikan ini sesuai dengan yang di form

        // Pola yang akan dicari di database, contoh: "%/KO/RT.01.00/2025"
        $pattern = '%/' . $kodePengusul . '/' . $kodePerihal . '/' . $tahun;

        // Query untuk mencari nomor urut tertinggi dari string `nomor_surat_usulan_jurusan`
        $latestNomor = SuratTugas::where('user_id', auth()->id())
            ->where('nomor_surat_usulan_jurusan', 'like', $pattern)
            ->select(DB::raw('MAX(CAST(SUBSTRING_INDEX(nomor_surat_usulan_jurusan, "/", 1) AS UNSIGNED)) as max_nomor'))
            ->value('max_nomor');

        return response()->json(['latest_nomor' => (int)$latestNomor ?? 0]);
    }
    
    public function show($id)
    {
        $suratTugas = SuratTugas::with(['detailPelaksanaTugas.personable', 'wadirApprover', 'direkturApprover'])
            ->where('surat_tugas_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $suratSettings = TemplateSurat::find(1);
        if (!$suratSettings) {
            $suratSettings = new TemplateSurat();
        }

        return view('layouts.pengusul.show', compact('suratTugas', 'suratSettings'));
    }
        
    public function previewSurat(Request $request)
    {
        $selectedPersonel = [];
        if ($request->has('pegawai_ids')) {
            $selectedPersonel = Pegawai::whereIn('id', $request->input('pegawai_ids'))->get();
        }

        $data = [
            'nama' => $request->input('nama'),
            'nip' => $request->input('nip'),
            'golongan' => $request->input('golongan'),
            'pangkat' => $request->input('pangkat'),
            'jabatan' => $request->input('jabatan'),
            'nama_kegiatan' => $request->input('nama_kegiatan'),
            'nama_penyelenggara' => $request->input('nama_penyelenggara'),
            'tempat_kegiatan' => $request->input('tempat_kegiatan'),
            'alamat_kegiatan' => $request->input('alamat_kegiatan'),
            'tanggal_pelaksanaan' => $request->input('tanggal_pelaksanaan'),
            'pelaksana' => $selectedPersonel
        ];

        return view('layouts.pengusul.preview-surat', compact('data'));
    }

    public function showTemplateSelection()
    {
        $templates = Template::all();
        return view('layouts.pengusul.pilih-pelaksana', compact('templates'));
    }

    public function templateSuratTugas(Request $request)
    {
        $template = Template::find($request->template_id);
        $templatePath = storage_path('app/templates/' . $template->file_path);

        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValue('Kegiatan', $request->kegiatan);
        $templateProcessor->setValue('Tempat', $request->tempat);
        $templateProcessor->setValue('Tanggal', $request->tanggal);

        $filePath = 'surat_tugas_' . time() . '.docx';
        $templateProcessor->saveAs(storage_path('app/generated/' . $filePath));

        return response()->download(storage_path('app/generated/' . $filePath));
    }

    public function downloadPdf($id)
    {
        $suratTugas = SuratTugas::with(['detailPelaksanaTugas.personable'])
        ->whereIn('status_surat', ['pending_direktur_review', 'diterbitkan']) // Izinkan dua status ini
        ->findOrFail($id);

        $user = Auth::user();
        $isAllowed = false;

        // ===================================================================
        // <-- AWAL BLOK KODE YANG DIPERBAIKI (LOGIKA OTORISASI) -->
        // ===================================================================

        // 1. Cek apakah user adalah PENGUSUL surat ini
        if ($suratTugas->user_id == $user->id) {
            $isAllowed = true;
        }

        // 2. Cek apakah user adalah PELAKSANA di surat ini
        if (!$isAllowed && $user->pegawai_id) {
            $isAllowed = $suratTugas->detailPelaksanaTugas()
                                    ->where('personable_id', $user->pegawai_id)
                                    ->where('personable_type', \App\Models\Pegawai::class)
                                    ->exists();
        }
        
        // 3. Cek apakah user adalah WADIR yang menyetujui surat ini
        if (!$isAllowed) {
            $isAllowed = $suratTugas->wadir_approver_id == $user->id;
        }

        // 4. Cek apakah user adalah DIREKTUR yang menyetujui surat ini
        if (!$isAllowed) {
            $isAllowed = $suratTugas->direktur_approver_id == $user->id;
        }
        
        // 5. Cek apakah user adalah SEKDIR yang memproses surat ini
        if (!$isAllowed) {
            $isAllowed = $suratTugas->sekdir_processor_id == $user->id;
        }

        // 6. Cek apakah user memiliki role ADMIN atau BKU (bisa melihat semua)
        if (!$isAllowed) {
            $isAllowed = in_array($user->role, ['admin', 'bku']);
        }

        // Jika setelah semua pengecekan tetap tidak diizinkan, tolak akses
        if (!$isAllowed) {
            abort(403, 'Anda tidak memiliki hak untuk mengakses atau mengunduh surat tugas ini.');
        }

        // ===================================================================
        // <-- AKHIR BLOK KODE YANG DIPERBAIKI -->
        // ===================================================================

        // Logika pembuatan PDF tetap sama
        $suratSettings = TemplateSurat::find(1);
        if (!$suratSettings) {
            $suratSettings = new \stdClass();
            $suratSettings->nama_kementerian = 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI';
            $suratSettings->nama_direktur = '[Nama Direktur Belum Diatur]';
            $suratSettings->nip_direktur = '[NIP Direktur Belum Diatur]';
            $suratSettings->tembusan_default = [];
        }

        $pdf = Pdf::loadView('pdf.surat_tugas_template', compact('suratTugas', 'suratSettings'));
        $fileName = 'Surat_Tugas_' . str_replace('/', '_', $suratTugas->nomor_surat_tugas_resmi) . '.pdf';
        
        return $pdf->download($fileName);
    }
}