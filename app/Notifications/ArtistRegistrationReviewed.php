<?php

namespace App\Notifications;

use App\Models\ArtistRegistration;
use App\Notifications\Concerns\RespectsNotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Gửi thông báo đến user khi đơn đăng ký nghệ sĩ được xét duyệt (approved / rejected).
 * Kênh: database + mail.
 */
class ArtistRegistrationReviewed extends Notification
{
    use Queueable;
    use RespectsNotificationSettings;

    public function __construct(
        public readonly ArtistRegistration $registration
    ) {}

    public function via(object $notifiable): array
    {
        return $this->resolveChannels($notifiable, true, true);
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->registration->isApproved()) {
            return (new MailMessage)
                ->subject('[Blue Wave Music] Đơn đăng ký Nghệ sĩ được phê duyệt 🎉')
                ->greeting('Xin chào ' . $notifiable->name . '!')
                ->line('Chúc mừng! Đơn đăng ký Nghệ sĩ của bạn với tên nghệ danh **"' . $this->registration->artist_name . '"** đã được phê duyệt.')
                ->line('Tài khoản của bạn đã được nâng cấp lên **Nghệ sĩ**. Bạn có thể bắt đầu tải lên và quản lý âm nhạc ngay bây giờ.')
                ->when($this->registration->admin_note, fn ($mail) => $mail->line('**Ghi chú từ admin:** ' . $this->registration->admin_note))
                ->action('Đến trang Nghệ sĩ', url('/artist/dashboard'))
                ->line('Cảm ơn bạn đã tham gia cộng đồng nghệ sĩ Blue Wave Music!')
                ->salutation('Trân trọng, Đội ngũ Blue Wave Music');
        }

        return (new MailMessage)
            ->subject('[Blue Wave Music] Đơn đăng ký Nghệ sĩ bị từ chối')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Rất tiếc, đơn đăng ký Nghệ sĩ của bạn với tên nghệ danh **"' . $this->registration->artist_name . '"** đã bị từ chối.')
            ->line('**Nhóm lý do:** ' . $this->registration->rejectionReasonLabel())
            ->when($this->registration->admin_note, fn ($mail) => $mail->line('**Lý do:** ' . $this->registration->admin_note))
            ->when($this->registration->refund_amount > 0, fn ($mail) => $mail
                ->line('**Hoàn tiền:** Số tiền **' . number_format($this->registration->refund_amount) . ' VNĐ** sẽ được chuyển về tài khoản ngân hàng của bạn trong vòng 3–5 ngày làm việc.')
            )
            ->line('**Hướng dẫn tiếp theo:** ' . $this->registration->rejectionNextStepGuidance())
            ->line('Nếu bạn có thắc mắc, vui lòng liên hệ bộ phận hỗ trợ.')
            ->action('Trang hỗ trợ', url('/dashboard'))
            ->line('Cảm ơn bạn đã sử dụng Blue Wave Music!')
            ->salutation('Trân trọng, Đội ngũ Blue Wave Music');
    }

    public function toDatabase(object $notifiable): array
    {
        if ($this->registration->isApproved()) {
            return [
                'event'        => 'artist_registration_approved',
                'title'        => 'Đơn đăng ký Nghệ sĩ được phê duyệt',
                'message'      => 'Chúc mừng! Đơn đăng ký nghệ sĩ của bạn đã được phê duyệt. Tài khoản đã được nâng cấp lên Nghệ sĩ!',
                'icon'         => 'fa-circle-check',
                'color'        => '#4ade80',
                'action_url'   => '/artist/dashboard',
                'action_label' => 'Trang Nghệ sĩ',
            ];
        }

        return [
            'event'        => 'artist_registration_rejected',
            'title'        => 'Đơn đăng ký Nghệ sĩ bị từ chối',
            'message'      => 'Rất tiếc, đơn đăng ký nghệ sĩ của bạn đã bị từ chối.'
                . ($this->registration->refund_amount > 0
                    ? ' Số tiền ' . number_format($this->registration->refund_amount) . ' ₫ sẽ được hoàn trong 3–5 ngày làm việc.'
                    : ' Vui lòng kiểm tra email để biết thêm chi tiết.'),
            'detail'       => 'Nhóm lý do: ' . $this->registration->rejectionReasonLabel() . '. ' . $this->registration->rejectionNextStepGuidance(),
            'icon'         => 'fa-ban',
            'color'        => '#f87171',
            'action_url'   => '/dashboard',
            'action_label' => 'Trang chủ',
        ];
    }
}
