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

class CutiDisetujui implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $prosesCuti;
    public $userRole;

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
        $channels = [new Channel('karyawan.proses_cuti')];
 
        if ($this->userRole === 'manager'){
            $channels[] =new Channel('manager.proses_cuti.' . $this->prosesCuti->manager_id);
            \Log::info('Broadcasting to channel: manager.proses_cuti'.$this->prosesCuti->manager_id);
            $channels[] = new Channel('karyawan.proses_cuti.' . $this->prosesCuti->karyawan_id);
            \Log::info('Broadcasting to channel: karyawan.proses_cuti'.$this->prosesCuti->karyawan_id);
        }
        elseif ($this->userRole === 'admin'){
            $channels[] =new Channel('manager.proses_cuti.' . $this->prosesCuti->manager_id);
            \Log::info('Broadcasting to channel: manager.proses_cuti'.$this->prosesCuti->manager_id);
            $channels[] =new Channel('admin.proses_cuti.' . $this->prosesCuti->admin_id);
            \Log::info('Broadcasting to channel: admin.proses_cuti'.$this->prosesCuti->admin_id);
            $channels[] = new Channel('karyawan.proses_cuti.' . $this->prosesCuti->karyawan_id);
            \Log::info('Broadcasting to channel: karyawan.proses_cuti'.$this->prosesCuti->karyawan_id);   
        }

        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->prosesCuti->id,
            'jenis_cuti' => $this->prosesCuti->jenis_cuti,
            // 'nama_lengkap' => $this->prosesCuti->admin->nama_lengkap,
            'tanggal_disetujui' => $this->prosesCuti->tanggal_disetujui,
            'tanggal_mulai' => $this->prosesCuti->tanggal_mulai
        ];
    }
}
