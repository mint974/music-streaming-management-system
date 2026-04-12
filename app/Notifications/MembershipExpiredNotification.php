<?php

namespace App\Notifications;

use App\Notifications\Concerns\RespectsNotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipExpiredNotification extends Notification
{
    use Queueable;
    use RespectsNotificationSettings;

    public function __construct(
        public readonly string $membershipType,
        public readonly string $packageName,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->resolveChannels($notifiable, true, true);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Blue Wave Music] Gói ' . $this->membershipType . ' đã hết hạn')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Gói ' . $this->membershipType . ' của bạn đã hết hạn.')
            ->line('Gói vừa hết hạn: **' . $this->packageName . '**')
            ->action('Đăng ký lại', url('/subscription'))
            ->line('Bạn có thể đăng ký lại bất kỳ lúc nào để tiếp tục sử dụng các tính năng tương ứng.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event'        => 'membership_expired',
            'title'        => 'Gói ' . $this->membershipType . ' đã hết hạn',
            'message'      => 'Gói ' . $this->packageName . ' của bạn đã hết hạn.',
            'icon'         => 'fa-circle-exclamation',
            'color'        => '#ef4444',
            'action_url'   => '/subscription',
            'action_label' => 'Đăng ký lại',
        ];
    }
}
