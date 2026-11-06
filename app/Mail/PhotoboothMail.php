<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PhotoboothMail extends Mailable
{
    use Queueable, SerializesModels;

    public $queueNumber;

    /** @var array<int, string> biner JPEG hasil komposisi frame */
    public array $strips;

    public function __construct($queueNumber, array $strips = [])
    {
        $this->queueNumber = $queueNumber;
        $this->strips = $strips;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hasil Foto Photobooth '.$this->queueNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.photobooth',
        );
    }

    /**
     * Lampirkan setiap strip foto yang sudah jadi (sudah berframe + filter).
     */
    public function attachments(): array
    {
        $files = [];

        foreach (array_values($this->strips) as $i => $raw) {
            $files[] = Attachment::fromData(fn () => $raw, 'photobooth-'.($i + 1).'.jpg')
                ->withMime('image/jpeg');
        }

        return $files;
    }
}
