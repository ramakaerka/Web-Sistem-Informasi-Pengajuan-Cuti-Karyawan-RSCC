<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatan';

    protected $primarykey = 'id';

    protected $fillable = [
        'nama_jabatan',
        'level',
        'jenis_jabatan',
    ];

    public function admin(){
        return $this->hasMany(Admin::class,'jabatan_id');
    }
    public function manager(){
        return $this->hasMany(Manager::class,'jabatan_id');
    }
    public function karyawan(){
        return $this->hasMany(Karyawan::class,'jabatan_id');
    }
}
