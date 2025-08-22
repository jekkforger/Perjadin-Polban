<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            // Tambahkan kolom JSON baru untuk menyimpan lokasi
            $table->json('lokasi_kegiatan')->nullable()->after('nama_penyelenggara');

            // Hapus kolom-kolom lama
            $table->dropColumn('tempat_kegiatan');
            $table->dropColumn('alamat_kegiatan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            // Logika untuk mengembalikan jika di-rollback
            $table->dropColumn('lokasi_kegiatan');
            $table->text('tempat_kegiatan');
            $table->text('alamat_kegiatan');
        });
    }
};