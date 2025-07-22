<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CutiDiajukanEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $cuti;
    /**
     * Create a new message instance.
     */
    public function __construct($cuti)
    {
        $this->cuti = $cuti;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {

        $nama = 'Unknown';

        if ($this->cuti->karyawan) {
            $nama = $this->cuti->karyawan->nama_lengkap;
        } elseif ($this->cuti->manager) {
            $nama = $this->cuti->manager->nama_lengkap;
        } elseif ($this->cuti->admin) {
            $nama = $this->cuti->admin->nama_lengkap;
        }
        return new Envelope(
            from: new Address('noreply@example.com', 'Sistem Pengajuan Cuti'),
            subject: 'Cuti Diajukan oleh' .' ' . $nama, 
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.cuti-diajukan',
            with: ['cuti' => $this->cuti],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
