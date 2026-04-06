<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeVerification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $newEmail,
        public readonly string $verificationUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Blue Wave] Xác nhận thay đổi email tài khoản',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email_change_verification',
            with: [
                'user'            => $this->user,
                'newEmail'        => $this->newEmail,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }
}
