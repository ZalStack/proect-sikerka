<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email uji kirim konfigurasi SMTP (Fitur B9).
 *
 * Catatan: nama property JANGAN `$mailer` — bentrok dengan property
 * bawaan class Mailable dan mematikan proses PHP.
 */
class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $sentAt,
        public string $usedMailer,
        public string $usedHost,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIKEKAR] Uji Kirim Email — ' . $this->sentAt,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: "Uji kirim email dari SIKEKAR.\n\n"
                . 'Waktu: ' . $this->sentAt . "\n"
                . 'Mailer: ' . $this->usedMailer . "\n"
                . 'Host: ' . ($this->usedHost !== '' ? $this->usedHost : '-') . "\n\n"
                . 'Jika Anda menerima email ini, konfigurasi SMTP sudah benar.',
        );
    }
}
