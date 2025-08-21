<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNomorFieldsToSuratTugasTable extends Migration
{
    public function up()
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            $table->integer('nomor_urutan_surat')->nullable()->after('surat_tugas_id');
            // PERBAIKAN DI SINI
            $table->string('kode_unit_kerja')->nullable()->after('nomor_urutan_surat');
            $table->string('kode_perihal')->nullable()->default('RT.01.00')->after('kode_unit_kerja');
            // AKHIR PERBAIKAN
            $table->integer('tahun_nomor_surat')->nullable()->after('kode_perihal');
        });
    }

    public function down()
    {
        Schema::table('surat_tugas', function (Blueprint $table) {
            // PERBAIKAN DI SINI
            $table->dropColumn(['nomor_urutan_surat', 'kode_unit_kerja', 'kode_perihal', 'tahun_nomor_surat']);
            // AKHIR PERBAIKAN
        });
    }
}