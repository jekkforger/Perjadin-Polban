<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Models\DigitalParaf; // Import model DigitalParaf
use Illuminate\Support\Facades\Storage; // Untuk upload file
use Illuminate\Support\Facades\Validator; // Untuk validasi
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str; // Untuk UUID atau string acak

class ParafController extends Controller
{
    /**
     * Menampilkan halaman Paraf.
     * Akan menampilkan pesan jika belum ada paraf atau daftar paraf yang sudah diupload.
     */
    public function index()
    {
        $userId = Auth::id(); // Ambil ID user yang sedang login

        // Ambil paraf digital yang diupload oleh user yang sedang login
        $digitalParafs = DigitalParaf::where('user_id', $userId)
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        $hasParafs = $digitalParafs->isNotEmpty(); // Cek langsung dari database

        // Mendapatkan display name role untuk sidebar
        $userRole = null;
        $roleDisplayName = 'Pengguna';
        if (\Auth::check()) {
            $userRole = \Auth::user()->role;
            $loginController = new LoginController();
            $roleDisplayName = $loginController->getRoleDisplayName($userRole);
        }

        return view('paraf.index', compact('hasParafs', 'digitalParafs', 'userRole', 'roleDisplayName'));
    }
    
    /**
     * Memproses upload paraf digital.
     */
    public function upload(Request $request)
    {
        // Validasi hanya menerima PNG dan JPG/JPEG
        $validator = Validator::make($request->all(), [
            'paraf_file' => 'required|file|mimes:png,jpg,jpeg|max:2048', // Maks 2MB, PNG atau JPG/JPEG
        ], [
            'paraf_file.required' => 'File paraf wajib diunggah.',
            'paraf_file.file' => 'Input harus berupa file.',
            'paraf_file.mimes' => 'Format file harus PNG atau JPG/JPEG.',
            'paraf_file.max' => 'Ukuran file maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('showUploadModal', true);
        }

        try {
            // Memulai transaksi database
            \DB::beginTransaction();

            $uploadedFile = $request->file('paraf_file');
            $originalName = $uploadedFile->getClientOriginalName();
            $mimeType = $uploadedFile->getClientMimeType();
            $extension = $uploadedFile->getClientOriginalExtension();

            $fileName = 'paraf_' . Auth::id() . '_' . time() . '.' . $extension;
            $filePath = $uploadedFile->storeAs('digital_parafs', $fileName, 'public'); // Simpan file

            if (!$filePath) {
                throw new \Exception("Gagal menyimpan file ke storage.");
            }

            // **DIHAPUS: Logika pembuatan thumbnail dan Imagick/Image::make**
            $thumbnailPath = null; // Set langsung null, karena tidak ada thumbnail yang dibuat

            // Simpan data paraf ke database
            $paraf = DigitalParaf::create([
                'user_id'        => Auth::id(),
                'file_path'      => $filePath,
                'file_name'      => $fileName,
                'original_name'  => $originalName,
                'file_mime_type' => $mimeType,
                'thumbnail_path' => $thumbnailPath, // Ini akan menyimpan NULL di DB
            ]);

            // Jika semua berhasil, commit transaksi
            \DB::commit();

            return redirect()->route('wadir.paraf')->with('success_message', 'Paraf berhasil diunggah!');

        } catch (\Exception $e) {
            // Jika terjadi error, rollback transaksi dan log
            \DB::rollBack();
            \Log::error('Error saat mengunggah paraf: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id() ?? 'N/A',
                'original_name' => $originalName ?? 'N/A'
            ]);

            // Hapus file yang mungkin sudah tersimpan sebagian di storage
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            // Hapus thumbnail juga jika ada (meskipun sekarang null)
            if (isset($thumbnailPath) && Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunggah paraf: ' . $e->getMessage())->withInput()->with('showUploadModal', true);
        }
    }

    /**
     * Menghapus paraf digital.
     */
    public function delete($id)
    {
        $paraf = DigitalParaf::where('id', $id)
                             ->where('user_id', Auth::id()) // Pastikan user yang menghapus adalah pemiliknya
                             ->firstOrFail();

        // Hapus file dari storage
        Storage::disk('public')->delete($paraf->file_path);

        // Hapus record dari database
        $paraf->delete();

        return redirect()->back()->with('success_message', 'Paraf berhasil dihapus!');
    }
}