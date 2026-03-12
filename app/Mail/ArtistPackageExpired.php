<?php

namespace App\Mail;

use App\Models\ArtistRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ArtistPackageExpired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ArtistRegistration $registration
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Blue Wave] Gói Nghệ sĩ của bạn đã hết hạn',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.artist_package_expired',
            with: [
                'registration' => $this->registration,
                'user'         => $this->registration->user,
                'package'      => $this->registration->package,
            ],
        );
    }
}
