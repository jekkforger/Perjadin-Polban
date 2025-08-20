<?php

namespace App\Imports;

use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class PegawaiImport implements ToModel, WithHeadingRow, WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Logika ini akan dipanggil untuk setiap baris di file Excel.
        // 'nama', 'nip', 'pangkat', dll. harus cocok dengan nama header kolom di file Excel Anda.
        return new Pegawai([
            'nama'     => $row['nama'],
            'nip'      => $row['nip'], 
            'pangkat'  => $row['pangkat'],
            'golongan' => $row['golongan'],
            'jabatan'  => $row['jabatan'],
            'status'   => 1, // Default status aktif saat import
        ]);
    }

    /**
     * Metode ini memberitahu Laravel Excel untuk menggunakan NIP sebagai kunci unik.
     * Jika NIP sudah ada, data akan di-update. Jika tidak, data baru akan dibuat.
     */
    public function uniqueBy()
    {
        return 'nip';
    }
}