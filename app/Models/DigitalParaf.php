<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalParaf extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_path',
        'file_name',
        'original_name',
        'file_mime_type',
    ];

    // Relasi ke user yang mengunggah paraf
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
