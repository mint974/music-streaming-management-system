<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\ArtistFollow;
use App\Models\Genre;
use App\Models\ListeningHistory;
use App\Models\NotificationSetting;
use App\Models\SavedAlbum;
use App\Models\Song;
use App\Models\SongFavorite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function history(Request $request): View
    {
        $filters = $this->validateHistoryFilters($request);

        $histories = ListeningHistory::query()
            ->with(['song.artist:id,name,artist_name', 'song.album:id,title', 'song.genre:id,name'])
            ->where('user_id', Auth::id());

        $this->applyHistoryFilters($histories, $filters);

        if ($filters['sort'] === 'most_listened') {
            $replayCounts = ListeningHistory::query()
                ->selectRaw('song_id, COUNT(*) as replay_count')
                ->where('user_id', Auth::id());

            $this->applyHistoryFilters($replayCounts, $filters);

            $replayCounts->groupBy('song_id');

            $histories
                ->leftJoinSub($replayCounts, 'song_replays', function ($join) {
                    $join->on('listening_histories.song_id', '=', 'song_replays.song_id');
                })
                ->select('listening_histories.*', DB::raw('COALESCE(song_replays.replay_count, 0) as replay_count'))
                ->orderByDesc('replay_count')
                ->orderByDesc('listened_at');
        } elseif ($filters['sort'] === 'oldest') {
            $histories->orderBy('listened_at');
        } else {
            $histories->orderByDesc('listened_at');
        }

        $histories = $histories->paginate(30)->withQueryString();

        $summaryBase = ListeningHistory::query()->where('user_id', Auth::id());
        $this->applyHistoryFilters($summaryBase, $filters);

        $summary = (clone $summaryBase)
            ->selectRaw('COUNT(*) as total_listens, COUNT(DISTINCT song_id) as unique_songs, SUM(COALESCE(played_seconds, 0)) as total_seconds')
            ->first();

        $topSongs = (clone $summaryBase)
            ->with(['song.artist:id,name,artist_name'])
            ->selectRaw('song_id, COUNT(*) as replay_count, MAX(listened_at) as last_listened_at, SUM(COALESCE(played_seconds, 0)) as total_seconds')
            ->groupBy('song_id')
            ->orderByDesc('replay_count')
            ->orderByDesc('last_listened_at')
            ->limit(8)
            ->get();

        $rangeStart = ! empty($filters['from_date']) ? Carbon::parse($filters['from_date']) : null;
        $rangeEnd = ! empty($filters['to_date']) ? Carbon::parse($filters['to_date']) : null;
        $chartGroup = $filters['chart_group'];

        if ($chartGroup === 'day' && $rangeStart && $rangeEnd && $rangeStart->diffInDays($rangeEnd) > 45) {
            $chartGroup = 'week';
        }

        $chartRows = match ($chartGroup) {
            'hour' => (clone $summaryBase)
                ->selectRaw("DATE_FORMAT(listened_at, '%Y-%m-%d %H:00') as label, SUM(COALESCE(played_seconds, 0)) as total_seconds")
                ->groupBy('label')
                ->orderBy('label')
                ->get(),
            'week' => (clone $summaryBase)
                ->selectRaw("YEARWEEK(listened_at, 1) as label_sort, DATE_FORMAT(MIN(listened_at), '%d/%m/%Y') as label, SUM(COALESCE(played_seconds, 0)) as total_seconds")
                ->groupBy('label_sort')
                ->orderBy('label_sort')
                ->get(),
            default => (clone $summaryBase)
                ->selectRaw("DATE(listened_at) as label, SUM(COALESCE(played_seconds, 0)) as total_seconds")
                ->groupBy('label')
                ->orderBy('label')
                ->get(),
        };

        $chartData = [
            'labels' => $chartRows->pluck('label')->all(),
            'minutes' => $chartRows->map(fn ($row) => round(((int) $row->total_seconds) / 60, 2))->all(),
            'group' => $chartGroup,
        ];

        $genres = Genre::query()
            ->join('songs', 'songs.genre_id', '=', 'genres.id')
            ->join('listening_histories', 'listening_histories.song_id', '=', 'songs.id')
            ->where('listening_histories.user_id', Auth::id())
            ->orderBy('genres.name')
            ->distinct()
            ->get(['genres.id', 'genres.name']);

        $artists = User::query()
            ->join('songs', 'songs.user_id', '=', 'users.id')
            ->join('listening_histories', 'listening_histories.song_id', '=', 'songs.id')
            ->where('listening_histories.user_id', Auth::id())
            ->where('users.deleted', false)
            ->orderBy('users.artist_name')
            ->orderBy('users.name')
            ->distinct()
            ->get(['users.id', 'users.name', 'users.artist_name']);

        return view('pages.listener-history', compact(
            'histories',
            'filters',
            'summary',
            'topSongs',
            'chartData',
            'genres',
            'artists'
        ));
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

    private function validateHistoryFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:recent,oldest,most_listened'],
            'status' => ['nullable', 'in:all,unfinished'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'artist_id' => ['nullable', 'integer', 'exists:users,id'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'from_time' => ['nullable', 'date_format:H:i'],
            'to_time' => ['nullable', 'date_format:H:i'],
            'chart_group' => ['nullable', 'in:day,hour'],
        ]);

        return array_merge([
            'sort' => 'recent',
            'status' => 'all',
            'chart_group' => 'day',
        ], $validated);
    }

    private function applyHistoryFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['genre_id'])) {
            $query->whereHas('song', fn (Builder $songQuery) => $songQuery->where('genre_id', $filters['genre_id']));
        }

        if (! empty($filters['artist_id'])) {
            $query->whereHas('song', fn (Builder $songQuery) => $songQuery->where('user_id', $filters['artist_id']));
        }

        if (! empty($filters['q'])) {
            $keyword = trim((string) $filters['q']);

            $query->whereHas('song', function (Builder $songQuery) use ($keyword) {
                $songQuery->where(function (Builder $subQuery) use ($keyword) {
                    $subQuery->where('title', 'like', "%{$keyword}%")
                        ->orWhereHas('artist', function (Builder $artistQuery) use ($keyword) {
                            $artistQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('artist_name', 'like', "%{$keyword}%");
                        });
                });
            });
        }

        if (($filters['status'] ?? 'all') === 'unfinished') {
            $query->where(function (Builder $subQuery) {
                $subQuery
                    ->where('is_completed', false)
                    ->orWhereNull('played_percent')
                    ->orWhere('played_percent', '<', 95);
            });
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('listened_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('listened_at', '<=', $filters['to_date']);
        }

        $fromTime = $filters['from_time'] ?? null;
        $toTime = $filters['to_time'] ?? null;

        if ($fromTime && $toTime) {
            if ($fromTime <= $toTime) {
                $query->whereTime('listened_at', '>=', $fromTime)
                    ->whereTime('listened_at', '<=', $toTime);
            } else {
                $query->where(function (Builder $subQuery) use ($fromTime, $toTime) {
                    $subQuery
                        ->whereTime('listened_at', '>=', $fromTime)
                        ->orWhereTime('listened_at', '<=', $toTime);
                });
            }
        } elseif ($fromTime) {
            $query->whereTime('listened_at', '>=', $fromTime);
        } elseif ($toTime) {
            $query->whereTime('listened_at', '<=', $toTime);
        }
    }
}
