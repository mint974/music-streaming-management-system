<?php

namespace App\Notifications;

use App\Notifications\Concerns\RespectsNotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipExpiringSoonNotification extends Notification
{
    use Queueable;
    use RespectsNotificationSettings;

    public function __construct(
        public readonly string $membershipType,
        public readonly string $packageName,
        public readonly string $expiresDate,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->resolveChannels($notifiable, true, true);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Blue Wave Music] Gói ' . $this->membershipType . ' sắp hết hạn')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Gói ' . $this->membershipType . ' của bạn sắp hết hạn vào ngày ' . $this->expiresDate . '.')
            ->line('Gói hiện tại: **' . $this->packageName . '**')
            ->action('Gia hạn ngay', url('/subscription'))
            ->line('Hãy gia hạn để không bị gián đoạn trải nghiệm trên Blue Wave Music.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event'        => 'membership_expiring_soon',
            'title'        => 'Gói ' . $this->membershipType . ' sắp hết hạn',
            'message'      => 'Gói ' . $this->packageName . ' sẽ hết hạn vào ngày ' . $this->expiresDate . '.',
            'icon'         => 'fa-hourglass-half',
            'color'        => '#f59e0b',
            'action_url'   => '/subscription',
            'action_label' => 'Gia hạn',
        ];
    }
}
