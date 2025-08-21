<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('digital_parafs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ID user yang mengupload paraf
            $table->string('file_path'); // Path file paraf di storage (e.g., digital_parafs/user_id/namafile.png)
            $table->string('file_name'); // Nama file yang disimpan (e.g., unique_name.png)
            $table->string('original_name'); // Nama file asli saat diupload user
            $table->string('file_mime_type'); // Tipe file (e.g., image/png, application/pdf)
            $table->string('keterangan')->nullable(); // Keterangan tambahan untuk paraf
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_parafs');
    }
};
