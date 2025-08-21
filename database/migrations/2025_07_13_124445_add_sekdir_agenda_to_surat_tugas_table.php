// database/migrations/xxxx_xx_xx_xxxxxx_add_sekdir_agenda_to_surat_tugas_table.php

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
        Schema::table('surat_tugas', function (Blueprint $table) {
            // Nomor yang diberikan Sekdir sebelum ke Direktur
            $table->string('nomor_agenda_sekdir')->nullable()->after('nomor_surat_tugas_resmi');

            // Waktu Sekdir memproses
            $table->timestamp('tanggal_penomoran_sekdir')->nullable()->after('tanggal_persetujuan_direktur');
            
            // ID user Sekdir yang memproses
            $table->foreignId('sekdir_processor_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->after('direktur_approver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sekdir_processor_id');
            $table->dropColumn(['nomor_agenda_sekdir', 'tanggal_penomoran_sekdir']);
        });
    }
};