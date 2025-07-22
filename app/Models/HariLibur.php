<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    use HasFactory;

    protected $table = 'hari_liburs';
    protected $primarykey = 'id';
    protected $fillable = [
        'tanggal',
        'nama',
        'is_nasional',
    ];
}
