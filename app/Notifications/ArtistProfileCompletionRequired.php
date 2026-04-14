<?php

namespace App\Notifications;

use App\Models\ArtistRegistration;
use App\Notifications\Concerns\RespectsNotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArtistProfileCompletionRequired extends Notification
{
    use Queueable;
    use RespectsNotificationSettings;

    public function __construct(
        public readonly ArtistRegistration $registration,
        public readonly string $adminNote,
        public readonly array $missingFields,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->resolveChannels($notifiable, true, true);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('[Blue Wave Music] Yêu cầu bổ sung hồ sơ Nghệ sĩ')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Admin đã kiểm tra đơn đăng ký nghệ sĩ của bạn và yêu cầu bổ sung thông tin hồ sơ trước khi xét duyệt tiếp.')
            ->line('**Nghệ danh đăng ký:** ' . $this->registration->artist_name)
            ->line('**Ghi chú từ admin:** ' . $this->adminNote);

        if (! empty($this->missingFields)) {
            $mail->line('**Các mục còn thiếu:** ' . implode(', ', $this->missingFields));
        }

        return $mail
            ->action('Điền thông tin hồ sơ nghệ sĩ', url('/artist/profile/setup'))
            ->line('Sau khi cập nhật đầy đủ, vui lòng lưu lại để admin tiếp tục xét duyệt đơn của bạn.')
            ->salutation('Trân trọng, Đội ngũ Blue Wave Music');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event' => 'artist_profile_completion_required',
            'title' => 'Yêu cầu bổ sung hồ sơ Nghệ sĩ',
            'message' => 'Admin yêu cầu bạn hoàn thiện đầy đủ hồ sơ nghệ sĩ trước khi xét duyệt.',
            'detail' => 'Thiếu: ' . (empty($this->missingFields) ? 'Vui lòng kiểm tra lại hồ sơ.' : implode(', ', $this->missingFields))
                . ' Ghi chú admin: ' . $this->adminNote,
            'icon' => 'fa-file-circle-exclamation',
            'color' => '#f59e0b',
            'action_url' => '/artist/profile/setup',
            'action_label' => 'Điền thông tin',
        ];
    }
}
