<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Thông báo hoàn tiền gửi đến người dùng.
 * Được kích hoạt khi:
 *  - Admin từ chối đơn đăng ký Nghệ sĩ (hoàn 100%)
 *  - User hủy gói Premium khi còn hơn 1/2 thời gian (hoàn 20%)
 */
class RefundIssued extends Notification
{
    use Queueable;

    /**
     * @param int    $amount         Số tiền hoàn (VNĐ)
     * @param string $type           'artist_rejected' | 'subscription_cancelled'
     * @param string $transactionCode Mã giao dịch gốc (nếu có)
     */
    public function __construct(
        public readonly int    $amount,
        public readonly string $type,
        public readonly string $transactionCode = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reason = $this->type === 'subscription_cancelled'
            ? 'hủy gói Premium sớm (còn hơn 1/2 thời gian sử dụng)'
            : 'đơn đăng ký Nghệ sĩ bị từ chối';

        $mail = (new MailMessage)
            ->subject('[Blue Wave Music] Xác nhận hoàn tiền – ' . number_format($this->amount) . ' ₫')
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Chúng tôi xác nhận đã xử lý hoàn tiền cho bạn.')
            ->line('**Lý do hoàn tiền:** ' . ucfirst($reason))
            ->line('**Số tiền hoàn trả:** ' . number_format($this->amount) . ' VNĐ');

        if ($this->transactionCode) {
            $mail = $mail->line('**Mã giao dịch gốc:** ' . $this->transactionCode);
        }

        return $mail
            ->line('Số tiền sẽ được chuyển về tài khoản ngân hàng của bạn trong vòng **3–5 ngày làm việc**.')
            ->action('Xem lịch sử giao dịch', url('/subscription'))
            ->line('Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ đội ngũ hỗ trợ của chúng tôi.')
            ->salutation('Trân trọng, Đội ngũ Blue Wave Music');
    }

    public function toDatabase(object $notifiable): array
    {
        $detail = $this->type === 'subscription_cancelled'
            ? 'do hủy gói Premium sớm'
            : 'do đơn đăng ký Nghệ sĩ bị từ chối';

        return [
            'event'        => 'refund_issued',
            'title'        => 'Xác nhận hoàn tiền',
            'message'      => 'Bạn sẽ nhận được ' . number_format($this->amount) . ' ₫ ' . $detail . '. Tiền sẽ về tài khoản trong 3–5 ngày làm việc.',
            'icon'         => 'fa-rotate-left',
            'color'        => '#34d399',
            'action_url'   => '/subscription',
            'action_label' => 'Xem lịch sử',
        ];
    }
}
