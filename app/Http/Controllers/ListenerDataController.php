<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\ArtistFollow;
use App\Models\ListeningHistory;
use App\Models\NotificationSetting;
use App\Models\SavedAlbum;
use App\Models\Song;
use App\Models\SongFavorite;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ListenerDataController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $followedArtists = ArtistFollow::query()
            ->with('artist:id,name,artist_name,avatar,artist_verified_at,bio')
            ->where('user_id', $user->id)
            ->latest()
            ->take(24)
            ->get();

        $savedAlbums = SavedAlbum::query()
            ->with(['album.artist:id,name,artist_name,avatar'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(24)
            ->get();

        $recentHistory = ListeningHistory::query()
            ->with(['song.artist:id,name,artist_name', 'song.album:id,title'])
            ->where('user_id', $user->id)
            ->latest('listened_at')
            ->take(50)
            ->get();

        $notificationSetting = NotificationSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'notify_new_song' => true,
                'notify_new_album' => true,
                'notify_in_app' => true,
                'notify_email' => true,
            ]
        );

        return view('pages.listener-data', compact(
            'followedArtists',
            'savedAlbums',
            'recentHistory',
            'notificationSetting'
        ));
    }

    public function toggleFollowArtist(Request $request, int $artistId): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $artist = User::query()
            ->where('id', $artistId)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'artist'))
            ->where('deleted', false)
            ->firstOrFail();

        if ($artist->id === $user->id) {
            return $this->respond($request, false, 'Bạn không thể tự theo dõi chính mình.', 422);
        }

        $follow = ArtistFollow::query()
            ->where('user_id', $user->id)
            ->where('artist_id', $artist->id)
            ->first();

        if ($follow) {
            $follow->delete();
            return $this->respond($request, true, 'Đã hủy theo dõi nghệ sĩ.', 200, ['following' => false]);
        }

        ArtistFollow::create([
            'user_id' => $user->id,
            'artist_id' => $artist->id,
            'notify_in_app' => true,
            'notify_email' => true,
        ]);

        return $this->respond($request, true, 'Đã theo dõi nghệ sĩ.', 200, ['following' => true]);
    }

    public function toggleSaveAlbum(Request $request, int $albumId): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $album = Album::query()
            ->published()
            ->where('id', $albumId)
            ->firstOrFail();

        $saved = SavedAlbum::query()
            ->where('user_id', $user->id)
            ->where('album_id', $album->id)
            ->first();

        if ($saved) {
            $saved->delete();
            return $this->respond($request, true, 'Đã bỏ lưu album.', 200, ['saved' => false]);
        }

        SavedAlbum::create([
            'user_id' => $user->id,
            'album_id' => $album->id,
        ]);

        return $this->respond($request, true, 'Đã lưu album vào thư viện.', 200, ['saved' => true]);
    }

    public function history(): View
    {
        $histories = ListeningHistory::query()
            ->with(['song.artist:id,name,artist_name', 'song.album:id,title'])
            ->where('user_id', Auth::id())
            ->latest('listened_at')
            ->paginate(30);

        return view('pages.listener-history', compact('histories'));
    }

    public function favorites(): View
    {
        $favorites = SongFavorite::query()
            ->with(['song.artist:id,name,artist_name', 'song.album:id,title'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(30);

        return view('pages.listener-favorites', compact('favorites'));
    }

    public function albums(): View
    {
        $savedAlbums = SavedAlbum::query()
            ->with([
                'album.artist:id,name,artist_name,avatar,artist_verified_at',
                'album' => function ($query) {
                    $query->withCount([
                        'songs as published_songs_count' => fn ($songQuery) => $songQuery->published(),
                    ]);
                },
            ])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('pages.listener-albums', compact('savedAlbums'));
    }

    public function toggleFavoriteSong(Request $request, int $songId): JsonResponse|RedirectResponse
    {
        $userId = (int) Auth::id();

        $song = Song::query()
            ->published()
            ->where('id', $songId)
            ->firstOrFail();

        $favorite = SongFavorite::query()
            ->where('user_id', $userId)
            ->where('song_id', $song->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return $this->respond($request, true, 'Đã bỏ yêu thích bài hát.', 200, ['favorited' => false]);
        }

        SongFavorite::create([
            'user_id' => $userId,
            'song_id' => $song->id,
        ]);

        return $this->respond($request, true, 'Đã thêm bài hát vào yêu thích.', 200, ['favorited' => true]);
    }

    public function clearHistory(Request $request): JsonResponse|RedirectResponse
    {
        ListeningHistory::query()
            ->where('user_id', Auth::id())
            ->delete();

        return $this->respond($request, true, 'Đã xóa toàn bộ lịch sử nghe.', 200);
    }

    public function removeHistoryItem(Request $request, int $id): JsonResponse|RedirectResponse
    {
        ListeningHistory::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return $this->respond($request, true, 'Đã xóa mục lịch sử.', 200);
    }

    public function settings(): View
    {
        $setting = NotificationSetting::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'notify_new_song' => true,
                'notify_new_album' => true,
                'notify_in_app' => true,
                'notify_email' => true,
            ]
        );

        return view('pages.listener-settings', compact('setting'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notify_new_song' => ['nullable', 'boolean'],
            'notify_new_album' => ['nullable', 'boolean'],
            'notify_in_app' => ['nullable', 'boolean'],
            'notify_email' => ['nullable', 'boolean'],
        ]);

        $setting = NotificationSetting::firstOrCreate(['user_id' => Auth::id()]);

        $setting->update([
            'notify_new_song' => (bool) ($validated['notify_new_song'] ?? false),
            'notify_new_album' => (bool) ($validated['notify_new_album'] ?? false),
            'notify_in_app' => (bool) ($validated['notify_in_app'] ?? false),
            'notify_email' => (bool) ($validated['notify_email'] ?? false),
        ]);

        return back()->with('success', 'Đã cập nhật cài đặt thông báo.');
    }

    private function respond(Request $request, bool $ok, string $message, int $status = 200, array $extra = []): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(array_merge(['ok' => $ok, 'message' => $message], $extra), $status);
        }

        if (! $ok) {
            return back()->withErrors(['listener' => $message]);
        }

        return back()->with('success', $message);
    }
}
