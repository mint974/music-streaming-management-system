<?php

namespace App\Notifications;

use App\Notifications\Concerns\RespectsNotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Thông báo gửi đến người dùng khi admin cập nhật tài khoản của họ.
 * Văn phong thân thiện, không lộ cơ sở dữ liệu.
 * Hỗ trợ kênh database (in-app) và mail (email).
 */
class AccountUpdated extends Notification
{
    use Queueable;
    use RespectsNotificationSettings;

    /**
     * Danh sách loại sự kiện và nội dung thông báo tương ứng.
     */
    private const EVENTS = [
        // Trạng thái
        'status_locked' => [
            'title'       => 'Tài khoản bị hạn chế',
            'message'     => 'Tài khoản của bạn hiện đang bị tạm hạn chế hoạt động. Nếu bạn cho rằng đây là nhầm lẫn, vui lòng liên hệ bộ phận hỗ trợ để được giải quyết.',
            'icon'        => 'fa-shield-exclamation',
            'color'       => '#f87171',
            'action_url'  => '/account/unlock-request',
            'action_label'=> 'Gửi yêu cầu mở khóa',
        ],
        'status_unlocked' => [
            'title'       => 'Tài khoản đã được khôi phục',
            'message'     => 'Tài khoản của bạn đã hoạt động trở lại bình thường. Chào mừng bạn quay lại Blue Wave Music!',
            'icon'        => 'fa-circle-check',
            'color'       => '#4ade80',
            'action_url'  => '/dashboard',
            'action_label'=> 'Trở về trang chủ',
        ],

        // Đổi loại tài khoản
        'role_free' => [
            'title'       => 'Loại tài khoản được cập nhật',
            'message'     => 'Loại tài khoản của bạn đã được điều chỉnh về Thính giả. Bạn vẫn có thể trải nghiệm âm nhạc miễn phí trên Blue Wave Music.',
            'icon'        => 'fa-user',
            'color'       => '#818cf8',
            'action_url'  => '/profile',
            'action_label'=> 'Xem tài khoản',
        ],
        'role_premium' => [
            'title'       => 'Nâng cấp tài khoản Premium!',
            'message'     => 'Chúc mừng! Tài khoản của bạn đã được nâng cấp lên Premium. Bạn có thể tận hưởng toàn bộ tính năng cao cấp ngay bây giờ.',
            'icon'        => 'fa-crown',
            'color'       => '#fbbf24',
            'action_url'  => '/dashboard',
            'action_label'=> 'Khám phá ngay',
        ],
        'role_artist' => [
            'title'       => 'Quyền Nghệ sĩ đã được cấp',
            'message'     => 'Tài khoản của bạn đã được cấp quyền Nghệ sĩ. Bạn có thể bắt đầu tải lên và chia sẻ âm nhạc của mình trên Blue Wave Music.',
            'icon'        => 'fa-microphone-lines',
            'color'       => '#c084fc',
            'action_url'  => '/artist/dashboard',
            'action_label'=> 'Trang nghệ sĩ',
        ],

        // Xác minh nghệ sĩ
        'artist_verified' => [
            'title'       => 'Tài khoản nghệ sĩ đã được xác minh ✓',
            'message'     => 'Chúc mừng! Tài khoản nghệ sĩ của bạn đã được xác minh chính thức (tick xanh). Huy hiệu xác minh sẽ xuất hiện trên trang hồ sơ và Artist Studio của bạn.',
            'icon'        => 'fa-circle-check',
            'color'       => '#38bdf8',
            'action_url'  => '/artist/dashboard',
            'action_label'=> 'Vào Artist Studio',
        ],
        'artist_unverified' => [
            'title'       => 'Cập nhật trạng thái xác minh',
            'message'     => 'Trạng thái xác minh trên tài khoản nghệ sĩ của bạn đã được cập nhật. Nếu cần thêm thông tin, vui lòng liên hệ bộ phận hỗ trợ.',
            'icon'        => 'fa-circle-info',
            'color'       => '#94a3b8',
            'action_url'  => '/profile',
            'action_label'=> 'Xem tài khoản',
        ],
        'artist_revoked' => [
            'title'       => 'Quyền Nghệ sĩ bị thu hồi vĩnh viễn',
            'message'     => 'Quyền Nghệ sĩ của tài khoản bạn đã bị thu hồi vĩnh viễn bởi quản trị viên. Bài hát và album đã đăng tải vẫn được giữ nguyên. Bạn không thể đăng ký lại gói nghệ sĩ. Nếu có thắc mắc, vui lòng liên hệ bộ phận hỗ trợ.',
            'icon'        => 'fa-microphone-slash',
            'color'       => '#f87171',
            'action_url'  => '/dashboard',
            'action_label'=> 'Về trang chủ',
        ],

        // Vô hiệu hóa
        'account_disabled' => [
            'title'       => 'Tài khoản đã bị vô hiệu hóa',
            'message'     => 'Tài khoản của bạn đã bị vô hiệu hóa theo chính sách của Blue Wave Music. Nếu bạn cho rằng đây là nhầm lẫn, vui lòng liên hệ bộ phận hỗ trợ.',
            'icon'        => 'fa-user-slash',
            'color'       => '#f87171',
            'action_url'  => '/profile',
            'action_label'=> 'Liên hệ hỗ trợ',
        ],

        // Yêu cầu mở khóa
        'unlock_approved' => [
            'title'       => 'Yêu cầu mở khóa đã được chấp thuận',
            'message'     => 'Yêu cầu mở khóa tài khoản của bạn đã được admin chấp thuận. Tài khoản của bạn đã hoạt động trở lại bình thường!',
            'icon'        => 'fa-lock-open',
            'color'       => '#4ade80',
            'action_url'  => '/dashboard',
            'action_label'=> 'Vào trang chủ',
        ],
        'unlock_rejected' => [
            'title'       => 'Yêu cầu mở khóa bị từ chối',
            'message'     => 'Rất tiếc, yêu cầu mở khóa tài khoản của bạn đã bị từ chối. Vui lòng kiểm tra email để biết thêm chi tiết.',
            'icon'        => 'fa-ban',
            'color'       => '#f87171',
            'action_url'  => '/account/unlock-request',
            'action_label'=> 'Gửi yêu cầu mới',
        ],
    ];

    /** Chỉ gửi email cho những sự kiện quan trọng ảnh hưởng trạng thái tài khoản. */
    private const MAIL_EVENTS = [
        'status_locked', 'status_unlocked',
        'account_disabled',
        'unlock_approved', 'unlock_rejected',
        'artist_verified', 'artist_unverified',
        'artist_revoked',
    ];

    public function __construct(
        public readonly string  $event,
        public readonly ?string $reason = null,   // Lý do khóa / ghi chú admin
    ) {}

    /**
     * Kênh gửi: lưu vào database; thêm mail cho các sự kiện quan trọng.
     */
    public function via(object $notifiable): array
    {
        $allowEmail = in_array($this->event, self::MAIL_EVENTS, true);

        return $this->resolveChannels($notifiable, true, $allowEmail);
    }

    /**
     * Nội dung email gửi đến user.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $info = self::EVENTS[$this->event] ?? [
            'title'        => 'Cập nhật tài khoản',
            'message'      => 'Có sự thay đổi trên tài khoản của bạn.',
            'action_url'   => '/profile',
            'action_label' => 'Xem tài khoản',
        ];

        $mail = (new MailMessage)
            ->subject('[Blue Wave Music] ' . $info['title'])
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line($info['message']);

        // Đính kèm lý do nếu có (khóa hoặc từ chối mở khóa)
        if ($this->reason) {
            $mail->line('**Lý do:** ' . $this->reason);
        }

        if ($this->event === 'status_locked' || $this->event === 'unlock_rejected') {
            $mail->line('Nếu bạn cho rằng đây là nhầm lẫn, bạn có thể gửi yêu cầu khiếu nại.')
                 ->action($info['action_label'], url($info['action_url']));
        } else {
            $mail->action($info['action_label'], url($info['action_url']));
        }

        return $mail->line('Cảm ơn bạn đã sử dụng Blue Wave Music!')
                    ->salutation('Trân trọng, Đội ngũ Blue Wave Music');
    }

    /**
     * Dữ liệu lưu vào DB, văn phong thân thiện — không có thông tin kỹ thuật.
     */
    public function toDatabase(object $notifiable): array
    {
        $info = self::EVENTS[$this->event] ?? [
            'title'        => 'Cập nhật tài khoản',
            'message'      => 'Có sự thay đổi trên tài khoản của bạn. Vui lòng kiểm tra thông tin cá nhân.',
            'icon'         => 'fa-bell',
            'color'        => '#818cf8',
            'action_url'   => '/profile',
            'action_label' => 'Xem tài khoản',
        ];

        return [
            'event'        => $this->event,
            'title'        => $info['title'],
            'message'      => $info['message'],
            'icon'         => $info['icon'],
            'color'        => $info['color'],
            'action_url'   => $info['action_url'],
            'action_label' => $info['action_label'],
        ];
    }
}
