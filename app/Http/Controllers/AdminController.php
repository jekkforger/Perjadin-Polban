<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Mahasiswa;
use App\Models\TemplatesSurat;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\TemplateSurat;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PegawaiImport;

class AdminController extends Controller
{
    public function pegawai(Request $request)
    {
        // 1. Ambil parameter untuk sorting, defaultnya 'updated_at' desc (terbaru)
        $sort = $request->input('sort', 'updated_at');
        $direction = $request->input('direction', 'desc');
        
        // (Opsional tapi direkomendasikan) Daftar kolom yang boleh di-sort
        $sortableColumns = ['nama', 'nip', 'jabatan', 'updated_at'];
        if (!in_array($sort, $sortableColumns)) {
            $sort = 'updated_at'; // Kembali ke default jika kolom tidak valid
        }

        // 2. Ambil semua data pegawai dan terapkan pengurutan
        $pegawai = Pegawai::orderBy($sort, $direction)->get();

        // 3. Kirim variabel sort dan direction ke view
        return view('layouts.admin.pegawai', compact('pegawai', 'sort', 'direction'));

    }
    

    public function mahasiswa() {
        $mahasiswa = Mahasiswa::all(); // ambil semua data mahasiswa
        return view('layouts.admin.mahasiswa', compact('mahasiswa'));
    }

    public function akun() {
        $perPage = request('per_page', 10);
        $users = User::paginate($perPage)->appends(['per_page' => $perPage]);

        // Mapping nama asli role
        $roleLabels = [
            'admin' => 'Admin',
            'wadir_1' => 'Wakil Direktur I',
            'wadir_2' => 'Wakil Direktur II',
            'wadir_3' => 'Wakil Direktur III',
            'wadir_4' => 'Wakil Direktur IV',
            'direktur' => 'Direktur',
            'bku' => 'Badan Keuangan dan Umum',
            'sekdir' => 'Sekretaris Direktur',
            'pelaksana' => 'Pelaksana',
            'pengusul' => 'Pengusul',
            // Tambah sesuai kebutuhan
        ];

        return view('layouts.admin.akun', compact('users', 'roleLabels'));
    }

    public function destroyAkun($id)
    {
        // Cari user berdasarkan ID
        $user = User::findOrFail($id);
        
        // Hapus akun yang ditemukan
        $user->delete();

        // Redirect dengan pesan sukses
        return redirect()->route('admin.akun')->with('success_message', 'Akun berhasil dihapus!');
    }

    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'role' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'kode_pengusul' => ['nullable', 'string'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain.',
        ]);

        // Menyimpan data ke dalam tabel users
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ];

        if ($request->role === 'pengusul') {
            $data['kode_pengusul'] = $request->kode_pengusul; // Menyimpan kode_pengusul jika role pengusul
        }

        User::create($data);

        // Redirect dengan pesan sukses
        return redirect()->route('admin.akun')->with('success_message', 'Akun berhasil ditambahkan!');
    }

    public function create()
    {
        // Daftar role yang tersedia
        $roles = [
            'pengusul' => 'Pengusul',
            'wadir_1' => 'Wakil Direktur I',
            'wadir_2' => 'Wakil Direktur II',
            'wadir_3' => 'Wakil Direktur III',
            'wadir_4' => 'Wakil Direktur IV',
            'direktur' => 'Direktur',
            'bku' => 'BKU',
            'admin' => 'Admin',
            'pelaksana' => 'Pelaksana',
            'sekdir' => 'Sekretaris Direktur',
        ];

        return view('layouts.admin.create-account', compact('roles'));
    }

    public function editTemplate()
    {
        // **PERBAIKAN DI SINI: Langsung get dari DB, bukan firstOrCreate**
        $settings = TemplateSurat::find(1);
        
        // Jika tidak ditemukan di database, buat objek kosong baru untuk form
        if (!$settings) {
            $settings = new TemplateSurat(); // Buat instance kosong
            // Ini akan memastikan form tampil kosong saat pertama kali diakses
        }

        // **PERBAIKAN: Nama view harus sesuai dengan file blade Anda (layouts.admin.template)**
        return view('layouts.admin.template', compact('settings'));
    }

        /**
     * Menyimpan (update) pengaturan template surat.
     */
    public function updateTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kementerian' => 'required|string',
            'nama_direktur' => 'required|string|max:255',
            'nip_direktur' => 'required|string|max:255',
            'tembusan_default' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Ambil pengaturan yang ada, atau buat yang baru jika belum ada
        // **PERBAIKAN: Jika update, harusnya cari dulu. Kalau tidak ada, baru buat.
        // Jika selalu update, gunakan updateOrCreate atau cari lalu update.**
        $settings = TemplateSurat::find(1); // Coba cari record dengan ID 1

        // Jika record belum ada, buat baru (ini hanya terjadi saat pertama kali admin menyimpan)
        if (!$settings) {
            $settings = new TemplateSurat(['id' => 1]); // Create a new instance and set ID
        }

        $tembusanArray = [];
        if (!empty($request->tembusan_default)) {
            $tembusanArray = array_map('trim', explode(',', $request->tembusan_default));
            $tembusanArray = array_filter($tembusanArray);
        }

        $settings->nama_kementerian = $request->nama_kementerian;
        $settings->nama_direktur = $request->nama_direktur;
        $settings->nip_direktur = $request->nip_direktur;
        $settings->tembusan_default = $tembusanArray; // Simpan sebagai array

        $settings->save(); // Simpan perubahan atau buat record baru

        return redirect()->back()->with('success_message', 'Template berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $template = TemplateSuratTugas::findOrFail($id);
        // Hapus file template yang ada di storage
        \Storage::delete($template->template_file);
        $template->delete();
    
        return redirect()->route('admin.template')->with('success_message', 'Template surat berhasil dihapus!');
    }

    public function cekEmail(Request $request)
    {
        $exists = User::where('email', $request->email)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->status = $request->status;
        $pegawai->save();

        return response()->json(['success' => true]);
    }

    public function importPegawai(Request $request)
    {
        $request->validate([
            'file_pegawai' => 'required|mimes:xlsx,csv'
        ]);

        try {
            Excel::import(new PegawaiImport, $request->file('file_pegawai'));
            
            return redirect()->route('admin.pegawai')->with('success_message', 'Data pegawai berhasil diimpor!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             // Anda bisa menangani error validasi per baris di sini jika perlu
             return redirect()->route('admin.pegawai')->with('error', 'Terjadi error validasi saat impor. Pastikan format file Anda benar.');
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return redirect()->route('admin.pegawai')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
