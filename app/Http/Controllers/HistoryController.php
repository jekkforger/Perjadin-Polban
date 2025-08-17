<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SuratTugas;
use App\Models\Pegawai;
use App\Models\TemplateSurat;

class HistoryController extends Controller
{
    /**
     * Menampilkan halaman riwayat surat tugas berdasarkan peran pengguna.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role;
        $pegawaiId = $user->pegawai_id;

        // Ambil parameter untuk sorting dan searching
        $sort = $request->input('sort', 'updated_at');
        $direction = $request->input('direction', 'desc');
        $search = $request->input('search');

        // Query dasar
        $query = SuratTugas::query()->with('pengusul');

        // Logika untuk memfilter data berdasarkan peran pengguna
        switch ($userRole) {
            case 'pengusul':
                $query->where('user_id', $user->id);
                break;

            case 'pelaksana':
                if ($pegawaiId) {
                    $query->whereHas('detailPelaksanaTugas', function ($q) use ($pegawaiId) {
                        $q->where('personable_id', $pegawaiId)
                          ->where('personable_type', Pegawai::class);
                    });
                } else {
                    $query->whereRaw('1 = 0'); // Tidak menampilkan apa-apa jika user pelaksana tidak terhubung ke data pegawai
                }
                break;
            
            case str_starts_with($userRole, 'wadir_'):
                $loginController = new \App\Http\Controllers\Auth\LoginController();
                $wadirDisplayName = $loginController->getRoleDisplayName($userRole);
                $query->where('diusulkan_kepada', $wadirDisplayName);
                break;

            case 'sekdir':
                $query->where('status_surat', '!=', 'draft') // Sekdir tidak perlu lihat draft
                      ->whereNotNull('wadir_approver_id'); // Hanya yang sudah melewati wadir
                break;

            case 'direktur':
                $query->where('status_surat', '!=', 'draft')
                      ->whereNotNull('sekdir_processor_id'); // Hanya yang sudah melewati sekdir
                break;

            case 'bku':
                // BKU melihat semua surat yang sudah diterbitkan atau memiliki laporan
                $query->where(function ($q) {
                    $q->where('status_surat', 'diterbitkan')
                      ->orWhereHas('laporanPerjalananDinas');
                });
                break;

            default:
                $query->whereRaw('1 = 0'); // Default: tidak menampilkan apa-apa
                break;
        }

        // Terapkan pencarian jika ada
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('perihal_tugas', 'like', "%{$search}%")
                  ->orWhere('nomor_surat_usulan_jurusan', 'like', "%{$search}%")
                  ->orWhere('nomor_surat_tugas_resmi', 'like', "%{$search}%");
            });
        }
        
        // Terapkan pengurutan
        $query->orderBy($sort, $direction);

        // Ambil data dengan paginasi
        $suratTugasList = $query->paginate(10)->appends($request->query());
        
        return view('history.index', compact('suratTugasList', 'sort', 'direction', 'userRole'));
    }

    /**
     * Menampilkan detail surat tugas (mirip dengan PengusulController@show)
     */
    public function show($id)
    {
        // Query ini lebih longgar, user bisa melihat detail selama mereka terlibat
        // Logika yang lebih ketat bisa ditambahkan di sini jika perlu
        $suratTugas = SuratTugas::with(['detailPelaksanaTugas.personable', 'wadirApprover', 'direkturApprover', 'sekdirProcessor'])
            ->findOrFail($id);

        $suratSettings = TemplateSurat::find(1) ?? new TemplateSurat();
        
        $userRole = Auth::user()->role;

        return view('history.show', compact('suratTugas', 'suratSettings', 'userRole'));
    }
}