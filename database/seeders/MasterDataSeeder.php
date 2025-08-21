<?php
// FILE: database/seeders/MasterDataSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding master data to external databases...');

        // ================== TAMBAHKAN BLOK INI ==================
        // LANGKAH 1: Kosongkan tabel di database eksternal sebelum mengisi.
        // Ini memastikan kita selalu mulai dari awal dan tidak ada duplikasi.
        $this->command->warn('Truncating external tables (pegawai & mahasiswa)...');
        DB::connection('mysql_simpeg')->table('pegawai')->truncate();
        DB::connection('mysql_simakad')->table('mahasiswa')->truncate();
        $this->command->info('Truncated external tables successfully.');
        // =======================================================

        // LANGKAH 2: Panggil seeder seperti biasa.
        $this->command->line('Calling PegawaiSeeder...');
        $this->call(PegawaiSeeder::class);
        $this->command->info('Pegawai data seeded to db_simpeg.');

        $this->command->line('Calling MahasiswaSeeder...');
        $this->call(MahasiswaSeeder::class);
        $this->command->info('Mahasiswa data seeded to db_simakad.');

        $this->command->info('Master data seeding complete.');
    }
}