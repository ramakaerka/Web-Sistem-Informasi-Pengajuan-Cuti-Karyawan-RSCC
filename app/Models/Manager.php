<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

    protected $table='manager';
    protected $primarykey='id';
    protected $fillable=[
        'unit_id',
        'jabatan_id',
        'nama_lengkap',
        'no_pokok',
        'unit',
        'jabatan',
        'role',
        'sisa_cuti',
        'sisa_cuti_sebelumnya',
        'alamat',
        'email',
        'jenis_kelamin',
        'no_telepon',
        'foto',
        'ttd',
        'user_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function Unit(){
        return $this->belongsTo(Unit::class);
    }
    public function Jabatan(){
        return $this->belongsTo(Jabatan::class);
    }
    public function cuti(){
        return $this->hasMany(Cuti::class);
    }
    public function approvalAtasan(){
        return $this->hasMany(Cuti::class,'apr_atasan_id',);
    }
    public function approvalKasi(){
        return $this->hasMany(Cuti::class,'apr_kasi_id',);
    }
    public function approvalKabag(){
        return $this->hasMany(Cuti::class,'apr_kabag_id',);
    }
    public function approvalAdmin(){
        return $this->hasMany(Cuti::class,'apr_admin_id',);
    }
    public function approvalDirektur(){
        return $this->hasMany(Cuti::class,'apr_direktur_id',);
    }
}
