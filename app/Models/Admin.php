<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Admin extends Model
{
    use Notifiable;
    use HasFactory;
    protected $table='admin';
    protected $primarykey='id';
    protected $fillable=[
        'unit_id',
        'jabatan_id',
        'nama_lengkap',
        'no_pokok',
        'unit',
        'jabatan',
        'sisa_cuti',
        'sisa_cuti_sebelumnya',
        'role',
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
}
