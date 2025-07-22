<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'unit';

    protected $primarykey = 'id';

    protected $fillable = [
        'nama_unit',
    ];
    public function admin(){
        return $this->hasMany(Admin::class,'unit_id');
    }
    public function manager(){
        return $this->hasMany(Manager::class,'unit_id');
    }
    public function karyawan(){
        return $this->hasMany(Karyawan::class,'unit_id');
    }
}
