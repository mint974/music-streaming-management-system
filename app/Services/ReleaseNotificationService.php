<?php

namespace App\Services;

use App\Models\User;
use App\Models\Song;
use App\Models\Album;
use App\Notifications\NewReleaseNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ReleaseNotificationService
{
    /**
     * Dispatch new release notifications to artist's followers based on preferences.
     * 
     * @param User $artist
     * @param Song|Album $item
     * @return void
     */
    public static function notifyFollowers(User $artist, $item)
    {
        $type = $item instanceof Song ? 'song' : 'album';
        $itemId = (int) $item->id;

        // Retrieve followers eagerly loading their notification_settings
        $followers = User::whereHas('followedArtists', function ($query) use ($artist) {
            $query->where('artist_id', $artist->id);
        })->with('notificationSetting')->get();

        $recipients = $followers->filter(function ($user) use ($type) {
            $setting = $user->notificationSetting;
            if (!$setting) {
                return true; // Default to notify if no explicit settings exist
            }

            // Filtering based on release type option
            if ($type === 'song' && !$setting->notify_new_song) {
                return false;
            }
            if ($type === 'album' && !$setting->notify_new_album) {
                return false;
            }

            // At least one notification channel must be enabled to receive
            return $setting->notify_in_app || $setting->notify_email;
        });

        if ($recipients->isEmpty()) {
            return;
        }

        // Extra safety: de-duplicate recipients by user id.
        $recipients = $recipients->unique('id')->values();

        // Idempotency guard: skip users that already received notification for this exact release.
        // This prevents repeated emails when scheduler retries or command is run repeatedly.
        $alreadyNotifiedUserIds = DB::table('notifications')
            ->where('type', NewReleaseNotification::class)
            ->whereIn('user_id', $recipients->pluck('id')->all())
            ->where('created_at', '>=', now()->subDays(7))
            ->where('data', 'like', '%"event":"new_release"%')
            ->where('data', 'like', '%"release_type":"' . $type . '"%')
            ->where('data', 'like', '%"item_id":' . $itemId . '%')
            ->where('data', 'like', '%"artist_id":' . (int) $artist->id . '%')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->flip()
            ->all();

        $recipients = $recipients
            ->reject(fn (User $user) => isset($alreadyNotifiedUserIds[(int) $user->id]))
            ->values();

        if ($recipients->isNotEmpty()) {
            $notification = new NewReleaseNotification($artist, $item);

            foreach ($recipients as $recipient) {
                /** @var User $recipient */
                $lockKey = sprintf(
                    'release_notify:%s:artist:%d:item:%d:user:%d',
                    $type,
                    (int) $artist->id,
                    $itemId,
                    (int) $recipient->id
                );

                // Atomic key: once set, this user will not receive duplicate alerts for the same release.
                if (! Cache::add($lockKey, 1, now()->addDays(7))) {
                    continue;
                }

                try {
                    Notification::sendNow($recipient, $notification);
                } catch (\Throwable $e) {
                    // Re-open the key so a later retry can send if this attempt failed.
                    Cache::forget($lockKey);
                    Log::warning('Release notification send failed', [
                        'artist_id' => (int) $artist->id,
                        'item_id' => $itemId,
                        'release_type' => $type,
                        'recipient_id' => (int) $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
