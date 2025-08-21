<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Auth\LoginController;
use App\Models\SuratTugas;
use App\Models\TemplateSurat;
use App\Models\User;
use Carbon\Carbon;  
use Barryvdh\DomPDF\Facade\Pdf;

class DirekturController extends Controller
{
    protected $loginController;

    public function __construct(LoginController $loginController)
    {
        $this->loginController = $loginController;
    }

    private function getLoggedInDirekturDisplayName()
    {
        if (Auth::check() && Auth::user()->role === 'direktur') {
            return $this->loginController->getRoleDisplayName('direktur');
        }
        return null;
    }

    public function dashboard(Request $request)
    {
        $direkturDisplayName = $this->getLoggedInDirekturDisplayName();

        if (is_null($direkturDisplayName)) {
            Log::warning("Akses Direktur Dashboard oleh non-Direktur role: " . (Auth::check() ? Auth::user()->role : 'Guest'));
            abort(403, 'Akses Ditolak: Anda bukan Direktur yang valid.');
        }

        $totalPengusulan = SuratTugas::count();
        $dalamProsesWadir = SuratTugas::whereIn('status_surat', ['pending_wadir_review', 'reverted_by_wadir'])->count();
        $dalamProsesBku = 0; // Placeholder
        $bertugas = SuratTugas::where('status_surat', 'diterbitkan')
                                ->whereDate('tanggal_berangkat', '<=', Carbon::today())
                                ->whereDate('tanggal_kembali', '>=', Carbon::today())
                                ->count();
        
        $dashboardStats = [
            'total_pengusulan' => $totalPengusulan,
            'dalam_proses_wadir' => $dalamProsesWadir,
            'dalam_proses_bku' => $dalamProsesBku,
            'bertugas' => $bertugas,
        ];

        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $pengusulanDetails = SuratTugas::whereIn('status_surat', [
                                            'approved_by_wadir',
                                            'rejected_by_direktur',
                                            'reverted_by_direktur',
                                            'diterbitkan'
                                        ])
                                        ->when($search, function ($query) use ($search) {
                                            return $query->where('perihal_tugas', 'like', "%{$search}%")
                                                        ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$search}%");
                                        })
                                        ->orderBy('updated_at', 'desc')
                                        ->paginate($perPage)
                                        ->appends(['search' => $search, 'per_page' => $perPage]);

        $roleDisplayName = $direkturDisplayName;
        $userRole = Auth::user()->role;

        return view('layouts.direktur.dashboard', compact('dashboardStats', 'pengusulanDetails', 'roleDisplayName', 'userRole'));
    }

    public function persetujuan(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $suratTugasUntukReview = SuratTugas::where('status_surat', 'pending_direktur_review')
                                        ->with(['pengusul', 'wadirApprover'])
                                        ->when($search, function ($query) use ($search) {
                                            return $query->where('perihal_tugas', 'like', "%{$search}%")
                                                         ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$search}%");
                                        })
                                        ->orderBy('tanggal_paraf_wadir', 'desc')
                                        ->paginate($perPage)
                                        ->appends(['search' => $search, 'per_page' => $perPage]);
        
        $roleDisplayName = $this->getLoggedInDirekturDisplayName();
        $userRole = Auth::user()->role;

        return view('layouts.direktur.persetujuan', compact('suratTugasUntukReview', 'search', 'roleDisplayName', 'userRole'));
    }

    public function paraf()
    {
        $direkturDisplayName = $this->getLoggedInDirekturDisplayName();
        if (is_null($direkturDisplayName)) {
            abort(403, 'Akses Ditolak: Anda bukan Direktur yang valid.');
        }

        $user = Auth::user();
        $currentSignaturePath = $user->signature_file_path ?? null;

        $roleDisplayName = $direkturDisplayName;
        $userRole = Auth::user()->role;

        return view('layouts.direktur.paraf', compact('currentSignaturePath', 'roleDisplayName', 'userRole'));
    }

    public function uploadParaf(Request $request)
    {
        $direkturDisplayName = $this->getLoggedInDirekturDisplayName();
        if (is_null($direkturDisplayName)) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Anda bukan Direktur yang valid.'], 403);
        }

        $user = Auth::user();

        try {
            // Jika ada input base64 dari signature pad
            if ($request->has('signature')) {
                $data = $request->input('signature');
                $image = str_replace('data:image/png;base64,', '', $data);
                $image = str_replace(' ', '+', $image);
                $imageData = base64_decode($image);

                $fileName = 'paraf_direktur/' . uniqid() . '.png';
                Storage::disk('public')->put($fileName, $imageData);

                // Hapus file lama
                if ($user->signature_file_path) {
                    Storage::disk('public')->delete($user->signature_file_path);
                }

                $user->signature_file_path = $fileName;
                $user->save();

                return response()->json(['success' => true, 'message' => 'Tanda tangan berhasil disimpan.']);
            }

            // Kalau tidak, fallback ke upload file manual
            $validator = Validator::make($request->all(), [
                'signature_file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:1024',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $validator->errors()], 422);
            }

            if ($user->signature_file_path) {
                Storage::disk('public')->delete($user->signature_file_path);
            }

            $filePath = $request->file('signature_file')->store('paraf_direktur', 'public');
            $user->signature_file_path = $filePath;
            $user->save();

            return response()->json(['success' => true, 'message' => 'File paraf berhasil diunggah.', 'filePath' => Storage::url($filePath)]);
        } catch (\Exception $e) {
            Log::error('Error unggah paraf: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan.']);
        }
    }

    public function deleteParaf(Request $request)
    {
        $direkturDisplayName = $this->getLoggedInDirekturDisplayName();
        if (is_null($direkturDisplayName)) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Anda bukan Direktur yang valid.'], 403);
        }

        $user = Auth::user();

        try {
            if ($user->signature_file_path && Storage::disk('public')->exists($user->signature_file_path)) {
                Storage::disk('public')->delete($user->signature_file_path);
                $user->signature_file_path = null; // Set path menjadi null di database
                $user->save();
                Log::info("Direktur (ID: {$user->id}, Role: {$user->role}) berhasil menghapus file paraf.");
                return response()->json(['success' => true, 'message' => 'File paraf berhasil dihapus.']);
            }

            return response()->json(['success' => false, 'message' => 'Tidak ada file paraf untuk dihapus.'], 404);

        } catch (\Exception $e) {
            Log::error('Error menghapus file paraf oleh Direktur: '.$e->getMessage(), [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus file: ' . $e->getMessage()], 500);
        }
    }

    public function reviewSuratTugas($id)
    {
        $direkturDisplayName = $this->getLoggedInDirekturDisplayName();
        if (is_null($direkturDisplayName)) { 
            abort(403, 'Akses Ditolak: Anda bukan Direktur yang valid.'); 
        }

        // Tambahkan with('direkturApprover') agar relasi signature_file_path bisa diakses di view
        $suratTugas = SuratTugas::with(['pengusul', 'wadirApprover', 'detailPelaksanaTugas.personable', 'direkturApprover'])
                                ->findOrFail($id);
                
        $suratSettings = TemplateSurat::find(1);
        if (!$suratSettings) {
            $suratSettings = new TemplateSurat();
            $suratSettings->nama_kementerian = '';
            $suratSettings->nama_direktur = '';
            $suratSettings->nip_direktur = '';
            $suratSettings->tembusan_default = [];
        }

        if (!in_array($suratTugas->status_surat, ['approved_by_wadir', 'reverted_by_direktur'])) {
            // return redirect()->route('direktur.persetujuan')->with('error', 'Surat tugas ini tidak lagi dalam tahap persetujuan Anda.');
        }

        $roleDisplayName = $this->getLoggedInDirekturDisplayName();
        $userRole = Auth::user()->role;

        return view('layouts.direktur.reviewSuratTugas', compact('suratTugas', 'roleDisplayName', 'userRole', 'suratSettings'));
    }

    public function processSuratTugasReview(Request $request, $id)
    {
        try {
            Log::info('processSuratTugasReview called', [
                'surat_tugas_id' => $id,
                'request_data' => $request->all()
            ]);        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:approve,reject,revert',
            'catatan_revisi' => 'nullable|string|max:1000',
            'signature_data' => 'nullable|string', // Validasi untuk data signature
            'signature_position' => 'nullable|string', // Validasi untuk posisi signature
        ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return back()->withErrors($validator)->withInput();
            }

            $suratTugas = SuratTugas::findOrFail($id);
            $userDirektur = Auth::user();

            // Check if user has permission
            $direkturDisplayName = $this->getLoggedInDirekturDisplayName();
            if (is_null($direkturDisplayName)) {
                abort(403, 'Akses Ditolak: Anda bukan Direktur yang valid.');
            }

            \DB::beginTransaction();

            $action = $request->input('action');
            $catatanRevisi = $request->input('catatan_revisi');
            $signatureData = $request->input('signature_data'); // Ambil data signature
            $signaturePosition = $request->input('signature_position'); // Ambil posisi signature
            $message = '';

            switch ($action) {
                case 'approve':
                    $suratTugas->status_surat = 'diterbitkan';
                    $suratTugas->tanggal_persetujuan_direktur = Carbon::now();
                    $suratTugas->direktur_approver_id = $userDirektur->id;
                    // $suratTugas->nomor_surat_tugas_resmi = $suratTugas->surat_tugas_id . '/ST-DIR/' . Carbon::now()->format('m/Y');

                    // Simpan signature data dan posisi jika ada
                    if ($signatureData) {
                        $suratTugas->direktur_signature_data = $signatureData;
                    }
                    if ($signaturePosition) {
                        $suratTugas->direktur_signature_position = json_decode($signaturePosition, true);
                    }

                    // =================== AWAL TAMBAHAN LOGIKA PDF ===================
                    // 1. Ambil data template surat
                    $suratSettings = TemplateSurat::find(1) ?? new TemplateSurat();

                    // 2. Generate PDF dari view
                    $pdf = Pdf::loadView('pdf.surat_tugas_template', compact('suratTugas', 'suratSettings'));

                    // 3. Buat nama file yang unik
                    $fileName = 'surat_tugas_final/' . 'ST_' . str_replace('/', '_', $suratTugas->nomor_surat_tugas_resmi) . '_' . time() . '.pdf';

                    // 4. Simpan file PDF ke storage (di dalam storage/app/public/surat_tugas_final/)
                    Storage::disk('public')->put($fileName, $pdf->output());

                    // 5. Simpan path file ke database
                    $suratTugas->path_file_surat_tugas_final = $fileName;
                    // =================== AKHIR TAMBAHAN LOGIKA PDF ===================

                    $message = 'Surat tugas berhasil disetujui dan diterbitkan.';
                    break;

                case 'reject':
                    $suratTugas->status_surat = 'rejected_by_direktur';
                    $suratTugas->catatan_revisi = $catatanRevisi;
                    $suratTugas->direktur_approver_id = $userDirektur->id;
                    $message = 'Surat tugas berhasil ditolak.';
                    break;

                case 'revert':
                    $suratTugas->status_surat = 'reverted_by_direktur';
                    $suratTugas->catatan_revisi = $catatanRevisi;
                    $suratTugas->direktur_approver_id = $userDirektur->id;
                    $message = 'Surat tugas berhasil dikembalikan untuk revisi.';
                    break;
            }

            if (!$suratTugas->save()) {
                throw new \Exception('Failed to save surat tugas');
            }

            \DB::commit();

            Log::info("Direktur (ID: {$userDirektur->id}) memproses Surat Tugas ID {$id} dengan aksi '{$action}'. Status baru: {$suratTugas->status_surat}");

            return redirect()->route('direktur.persetujuan')->with('success_message', $message);

        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Exception in processSuratTugasReview:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'surat_tugas_id' => $id,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat memproses keputusan: ' . $e->getMessage())->withInput();
        }
    }
}