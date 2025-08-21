<?php
// FILE: database/seeders/PegawaiSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Pegawai; // <-- Gunakan Model Pegawai
use Illuminate\Support\Facades\DB;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        
        // --- Buat Pegawai Kunci ---
        DB::connection('mysql_simpeg')->table('pegawai')->insertOrIgnore([
            ['nama' => 'Ahmad Fauzy', 'nip' => '199001012020121005', 'pangkat' => 'Penata Muda', 'golongan' => 'III/a', 'jabatan' => 'Staf Umum'],
            ['nama' => 'Pengusul Utama', 'nip' => '199001012020121001', 'pangkat' => 'Penata', 'golongan' => 'III/c', 'jabatan' => 'Ketua Jurusan'],
            ['nama' => 'Faras Rama Mahadika', 'nip' => '198501012015121001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Wakil Direktur I'],
            ['nama' => 'Naufal Syafiq S', 'nip' => '198001012010121001', 'pangkat' => 'Pembina Utama', 'golongan' => 'IV/e', 'jabatan' => 'Direktur'],
            ['nama' => 'Budi Santoso', 'nip' => '198601012016121001', 'pangkat' => 'Pembina', 'golongan' => 'IV/a', 'jabatan' => 'Wakil Direktur II'],
        ]);

        // --- Buat Pegawai Acak ---
        for ($i = 1; $i <= 499; $i++) {
            // Kita gunakan DB facade di sini agar konsisten
            DB::connection('mysql_simpeg')->table('pegawai')->insert([
                'nama' => $faker->unique()->name,
                'nip' => $faker->unique()->numerify('199###############'),
                'pangkat' => 'Penata Muda Tingkat I',
                'golongan' => 'III/b',
                'jabatan' => 'Staf Humas',
            ]);
        }
    }
}