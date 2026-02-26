<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Blue Wave] Nâng cấp tài khoản Premium thành công!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription_confirmation',
            with: [
                'subscription' => $this->subscription,
                'user'         => $this->subscription->user,
                'vip'          => $this->subscription->vip,
                'payment'      => $this->subscription->payment,
            ],
        );
    }
}
