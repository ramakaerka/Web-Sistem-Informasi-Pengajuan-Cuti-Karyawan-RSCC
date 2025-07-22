<?php

namespace App\Notifications;

use App\Models\Cuti;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewCutiNotification extends Notification
{
    use Queueable;

    public function __construct(public Cuti $prosesCuti) {}
    /**
     * Create a new notification instance.
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast']; // Channel pengiriman
    }

    public function toDatabase($notifiable)
    {
        return [
            'cuti_id' => $this->prosesCuti->id,
            'message' => "Pengajuan cuti baru dari {$this->prosesCuti->karyawan->nama_lengkap}",
            'url' => "/admin/cuti/{$this->prosesCuti->id}"
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable)
        ]);
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
