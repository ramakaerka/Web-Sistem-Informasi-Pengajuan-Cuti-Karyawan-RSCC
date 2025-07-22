<?php

namespace App\Events;

use App\Models\Cuti;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CutiDiajukan implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $prosesCuti;
    public $userRole;
    /**
     * Create a new event instance.
     */
    public function __construct(Cuti $prosesCuti, $userRole)
    {
        $this->prosesCuti = $prosesCuti;
        $this->userRole = $userRole;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        \Log::info('Broadcasting to channel: admin.proses_cuti');
        return [
            new Channel('admin.proses_cuti')
        ];
    }

    public function broadcastWith()
    {
        
        // return [
        //     'id' => $this->prosesCuti->id,
        //     'nama_lengkap' => $this->prosesCuti->karyawan->nama_lengkap,
        //     'tanggal_mulai' => $this->prosesCuti->tanggal_mulai
        // ];
        $baseData = [
            'id' => $this->prosesCuti->id,
            'tanggal_mulai' => $this->prosesCuti->tanggal_mulai,
            'tanggal_pengajuan' => now()->toDateTimeString()
        ];

        // Data tambahan berdasarkan role pengirim
        if ($this->userRole === 'karyawan') {
            $baseData['nama_lengkap'] = $this->prosesCuti->karyawan->nama_lengkap;
            $baseData['jenis_cuti'] = $this->prosesCuti->jenis_cuti;
            
        } elseif ($this->userRole === 'manager') {
            $baseData['nama_lengkap'] = $this->prosesCuti->manager->nama_lengkap;
            $baseData['jenis_cuti'] = $this->prosesCuti->jenis_cuti;
            
        } elseif ($this->userRole === 'admin') {
            $baseData['nama_lengkap'] = $this->prosesCuti->admin->nama_lengkap;
            $baseData['jenis_cuti'] = $this->prosesCuti->jenis_cuti;
        }
        return $baseData;
    }
}
