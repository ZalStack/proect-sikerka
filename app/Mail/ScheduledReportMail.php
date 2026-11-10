<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email pengiriman laporan terjadwal (Fitur C12) — file di-attach
 * sehingga penerima bisa langsung membuka tanpa login ke sistem.
 */
class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $scheduleName,
        public string $summary,
        public string $periodLabel,
        public string $attachmentPath,
        public string $attachmentName,
        public string $attachmentMime,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIKEKAR] Laporan: ' . $this->scheduleName,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'Laporan terjadwal "' . $this->scheduleName . "\" berhasil dibuat.\n\n"
                . 'Periode: ' . $this->periodLabel . "\n"
                . 'Data: ' . $this->summary . "\n\n"
                . 'File terlampir (' . $this->attachmentName . ").\n\n"
                . 'Email ini dikirim otomatis oleh SIKEKAR.',
        );
    }

    public function attachments(): array
    {
        if (!is_file($this->attachmentPath)) {
            return [];
        }

        return [
            Attachment::fromPath($this->attachmentPath)
                ->as($this->attachmentName)
                ->withMime($this->attachmentMime),
        ];
    }
}
