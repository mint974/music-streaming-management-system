<?php

namespace App\Services;

use App\Models\User;
use App\Models\Song;
use App\Models\Album;
use App\Notifications\NewReleaseNotification;
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

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewReleaseNotification($artist, $item));
        }
    }
}
