<?php

namespace App\Notifications\Concerns;

use App\Models\NotificationSetting;

trait RespectsNotificationSettings
{
    /**
     * Resolve delivery channels from user's notification settings.
     * Defaults to both channels enabled when setting row does not exist.
     *
     * @return array<int, string>
     */
    protected function resolveChannels(object $notifiable, bool $allowInApp = true, bool $allowEmail = true): array
    {
        $setting = $notifiable->notificationSetting ?? null;

        if ($setting === null && method_exists($notifiable, 'notificationSetting')) {
            /** @var NotificationSetting|null $setting */
            $setting = $notifiable->notificationSetting()->first();
        }

        $inAppEnabled = $setting?->notify_in_app ?? true;
        $emailEnabled = $setting?->notify_email ?? true;

        $channels = [];

        if ($allowInApp && $inAppEnabled) {
            $channels[] = 'database';
        }

        if ($allowEmail && $emailEnabled) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
