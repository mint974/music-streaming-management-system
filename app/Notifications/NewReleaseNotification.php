<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Song;
use App\Models\Album;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReleaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $artist;
    public $item;
    public $type; // 'song' or 'album'

    public function __construct(User $artist, $item)
    {
        $this->artist = $artist;
        $this->item = $item;
        $this->type = $item instanceof Song ? 'song' : 'album';
    }

    public function via($notifiable)
    {
        $setting = $notifiable->notificationSetting;
        
        $channels = [];
        // Determine allowed routing channels
        if (!$setting || $setting->notify_email) {
            $channels[] = 'mail';
        }
        if (!$setting || $setting->notify_in_app) {
            $channels[] = 'database';
        }
        
        return $channels;
    }

    public function toMail($notifiable)
    {
        $title = $this->type === 'song' ? $this->item->title : $this->item->title;
        $label = $this->type === 'song' ? 'bài hát' : 'album';
        $routeStr = $this->type === 'song' ? route('songs.show', $this->item->id) : route('albums.show', $this->item->id);

        return (new MailMessage)
            ->subject(ucfirst($label) . ' mới từ ' . $this->artist->getDisplayArtistName())
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line('Nghệ sĩ bạn theo dõi (' . $this->artist->getDisplayArtistName() . ') vừa ra mắt ' . $label . ' mới: ' . $title)
            ->action('Khám phá ngay trên Blue Wave Music', $routeStr)
            ->line('Cảm ơn bạn đã luôn ủng hộ nghệ sĩ trên nền tảng của chúng tôi!');
    }

    public function toArray($notifiable)
    {
        $title = $this->type === 'song' ? $this->item->title : $this->item->title;
        $label = $this->type === 'song' ? 'bài hát' : 'album';
        $icon = $this->type === 'song' ? 'fa-music' : 'fa-compact-disc';
        $url = $this->type === 'song' ? route('songs.show', $this->item->id) : route('albums.show', $this->item->id);

        return [
            'icon'    => $icon,
            'color'   => '#a855f7',
            'title'   => 'Phát hành mới',
            'message' => $this->artist->getDisplayArtistName() . ' vừa phát hành ' . $label . ' mới ' . $title,
            'url'     => $url
        ];
    }
}
