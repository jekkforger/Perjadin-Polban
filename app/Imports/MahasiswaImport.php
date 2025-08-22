<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class MahasiswaImport implements ToModel, WithHeadingRow, WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Pastikan nama header di file Excel Anda adalah: nama, nim, jurusan, prodi
        return new Mahasiswa([
            'nama'    => $row['nama'],
            'nim'     => $row['nim'], 
            'jurusan' => $row['jurusan'],
            'prodi'   => $row['prodi'],
        ]);
    }

    /**
     * Gunakan NIM sebagai kunci unik untuk update atau create.
     */
    public function uniqueBy()
    {
        return 'nim';
    }
}