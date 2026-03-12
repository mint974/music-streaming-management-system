<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Blue Wave] Gói Premium của bạn đã hết hạn',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription_expired',
            with: [
                'subscription' => $this->subscription,
                'user'         => $this->subscription->user,
                'vip'          => $this->subscription->vip,
            ],
        );
    }
}
