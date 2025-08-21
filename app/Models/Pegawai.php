<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    // protected $connection = 'mysql_simpeg';

    protected $table = 'pegawai'; // Pastikan ini ada

    protected $fillable = [
        'nama',
        'nip',
        'pangkat',
        'golongan',
        'jabatan',
        'status',
    ];

    // Relasi polimorfik ke DetailPelaksanaTugas
    public function detailPelaksanaTugas()
    {
        return $this->morphMany(DetailPelaksanaTugas::class, 'personable');
    }
}