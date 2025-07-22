<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cuti extends Model
{
    use HasFactory;
    
    use SoftDeletes;

    // Untuk Laravel 8.x ke atas:
    protected $casts = [
        'deleted_at' => 'datetime'
    ];
    protected $table='proses_cuti';
    protected $primarykey='id';
    protected $fillable=[
        'karyawan_id',
        'manager_id',
        'admin_id',
        'jenis_cuti',
        'tanggal_pengajuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'status_manager',
        'status_admin',
        'validitas_suket',
        'alasan',
        'alasan_penolakan',
        'surat_keterangan',
        'penalty_gaji',
        'ttd_atasan',
        'ttd_atasan_admin',
        'ttd_kasi',
        'ttd_kabag',
        'ttd_admin',
        'ttd_direktur',
        'ttd_pt',
        'apr_atasan_id',
        'apr_atasan_admin_id',
        'apr_kasi_id',
        'apr_kabag_id',
        'apr_admin_id',
        'apr_direktur_id'
    ];

    public function karyawan(): BelongsTo{
        return $this->belongsTo(Karyawan::class,'karyawan_id');
    }
    public function manager(): BelongsTo{
        return $this->belongsTo(Manager::class,'manager_id');
    }
    public function approval_atasan(): BelongsTo{
        return $this->belongsTo(Manager::class,'apr_atasan_id');
    }
    public function approval_atasan_admin(): BelongsTo{
        return $this->belongsTo(Admin::class,'apr_atasan_admin_id');
    }
    public function approval_kasi(): BelongsTo{
        return $this->belongsTo(Manager::class,'apr_kasi_id');
    }
    public function approval_kabag(): BelongsTo{
        return $this->belongsTo(Manager::class,'apr_kabag_id');
    }
    public function approval_admin(): BelongsTo{
        return $this->belongsTo(Admin::class,'apr_admin_id');
    }
    public function approval_direktur(): BelongsTo{
        return $this->belongsTo(Manager::class,'apr_direktur_id');
    }
    public function admin(): BelongsTo{
        return $this->belongsTo(Admin::class,'admin_id');
    }
    public function user(): BelongsTo{
        return $this->belongsTo(User::class,'user_id');
    }
}
