<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringSoon extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Blue Wave] Gói Premium của bạn sắp hết hạn vào ngày mai!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription_expiring_soon',
            with: [
                'subscription' => $this->subscription,
                'user'         => $this->subscription->user,
                'vip'          => $this->subscription->vip,
            ],
        );
    }
}
