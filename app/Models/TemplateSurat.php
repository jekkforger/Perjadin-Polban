<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSurat extends Model
{
    use HasFactory;

    protected $table = 'template_surat'; // Pastikan nama tabel benar

    protected $fillable = [
        'nama_kementerian',
        'nama_direktur',
        'nip_direktur',
        'tembusan_default',
    ];

    protected $casts = [
        'tembusan_default' => 'array', // Mengubah kolom JSON menjadi array PHP secara otomatis
    ];
}
