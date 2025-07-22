<?php

namespace App\Listeners;

use App\Events\CutiDiajukan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\NewCutiNotification;

class SendCutiNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CutiDiajukan $event)
    {
        // 1. Kirim notifikasi database
        $admin = $event->prosesCuti->admin;
        $admin->notify(new NewCutiNotification($event->prosesCuti));

        // 2. Kirim WhatsApp (via Job)
        // SendWhatsAppNotification::dispatch(
        //     $admin->phone,
        //     "Pengajuan cuti baru dari {$event->prosesCuti->karyawan->nama_lengkap}"
        // );

        // 3. Log aktivitas
        \Log::channel('cuti')->info('Notifikasi terkirim ke admin', [
            'admin_id' => $admin->id,
            'cuti_id' => $event->prosesCuti->id
        ]);
    }
}
