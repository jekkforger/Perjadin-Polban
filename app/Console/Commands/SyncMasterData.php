<?php
// FILE: app/Console/Commands/SyncMasterData.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use App\Models\Mahasiswa;

class SyncMasterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:master-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Pegawai and Mahasiswa data from external databases to the local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting master data synchronization...');

        $this->syncPegawaiData();
        $this->syncMahasiswaData();

        $this->info('Master data synchronization finished successfully!');
        return 0;
    }

    private function syncPegawaiData()
    {
        $this->line('Syncing Pegawai data...');
        
        // 1. Ambil semua data pegawai dari database eksternal (SimPeg)
        //    Kita gunakan DB::connection() untuk menargetkan database spesifik
        try {
            $externalPegawai = DB::connection('mysql_simpeg')->table('pegawai')->get();
        } catch (\Exception $e) {
            $this->error('Failed to connect to SimPeg database: ' . $e->getMessage());
            return;
        }

        if ($externalPegawai->isEmpty()) {
            $this->warn('No data found in external Pegawai database. Skipping.');
            return;
        }

        // 2. Loop setiap data dan simpan/update ke database lokal
        $bar = $this->output->createProgressBar($externalPegawai->count());
        $bar->start();

        foreach ($externalPegawai as $pegawai) {
            // Gunakan updateOrCreate untuk efisiensi.
            // Dia akan mencari pegawai dengan 'nip' yang sama.
            // Jika ketemu -> UPDATE datanya.
            // Jika tidak ketemu -> CREATE data baru.
            Pegawai::updateOrCreate(
                ['nip' => $pegawai->nip], // Kunci unik untuk mencari
                [
                    'nama' => $pegawai->nama,
                    'pangkat' => $pegawai->pangkat,
                    'golongan' => $pegawai->golongan,
                    'jabatan' => $pegawai->jabatan,
                    // Pastikan nama kolom di sini sama persis dengan di tabel lokal
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Pegawai data synced.');
    }

    private function syncMahasiswaData()
    {
        $this->line('Syncing Mahasiswa data...');
        
        // 1. Ambil data dari SimAkad
        try {
            $externalMahasiswa = DB::connection('mysql_simakad')->table('mahasiswa')->get();
        } catch (\Exception $e) {
            $this->error('Failed to connect to SimAkad database: ' . $e->getMessage());
            return;
        }

        if ($externalMahasiswa->isEmpty()) {
            $this->warn('No data found in external Mahasiswa database. Skipping.');
            return;
        }

        // 2. Loop dan simpan/update ke database lokal
        $bar = $this->output->createProgressBar($externalMahasiswa->count());
        $bar->start();

        foreach ($externalMahasiswa as $mahasiswa) {
            Mahasiswa::updateOrCreate(
                ['nim' => $mahasiswa->nim], // Kunci unik
                [
                    'nama' => $mahasiswa->nama,
                    'jurusan' => $mahasiswa->jurusan,
                    'prodi' => $mahasiswa->prodi,
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Mahasiswa data synced.');
    }
}