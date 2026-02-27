<?php

namespace App\Notifications;

use App\Models\ArtistRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Gửi thông báo in-app đến tất cả admin khi một user
 * đã thanh toán đơn đăng ký nghệ sĩ và đang chờ xét duyệt.
 */
class NewArtistRegistration extends Notification
{
    use Queueable;

    public function __construct(
        public readonly ArtistRegistration $registration
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $user = $this->registration->user;

        return [
            'event'        => 'new_artist_registration',
            'title'        => 'Đơn đăng ký Nghệ sĩ mới',
            'message'      => "Người dùng {$user->name} đã thanh toán đơn đăng ký nghệ sĩ với tên nghệ danh \"{$this->registration->artist_name}\". Vui lòng xét duyệt.",
            'icon'         => 'fa-microphone-lines',
            'color'        => '#c084fc',
            'action_url'   => '/admin/artist-registrations',
            'action_label' => 'Xem và xét duyệt',
            'registration_id' => $this->registration->id,
        ];
    }
}
