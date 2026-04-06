<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangeVerification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verificationUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Blue Wave] Xác nhận đổi mật khẩu tài khoản',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_change_verification',
            with: [
                'user'            => $this->user,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }
}
