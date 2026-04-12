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
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ListenerDataController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $filters = $this->validateListenerDashboardFilters(request());

        $followedArtistsQuery = ArtistFollow::query()
            ->with('artist:id,name,artist_name,avatar,artist_verified_at,bio')
            ->where('user_id', $user->id);

        $this->applyFollowedArtistFilters($followedArtistsQuery, $filters['artists']);

        $followedArtistsTotal = (clone $followedArtistsQuery)->count();
        $followedArtists = $this->limitSectionCollection(
            $this->sortFollowedArtists((clone $followedArtistsQuery)->get(), $filters['artists']['sort']),
            8
        );

        $savedAlbumsQuery = SavedAlbum::query()
            ->with(['album.artist:id,name,artist_name,avatar'])
            ->where('user_id', $user->id);

        $this->applySavedAlbumFilters($savedAlbumsQuery, $filters['albums']);

        $savedAlbumsTotal = (clone $savedAlbumsQuery)->count();
        $savedAlbums = $this->limitSectionCollection(
            $this->sortSavedAlbums((clone $savedAlbumsQuery)->get(), $filters['albums']['sort']),
            8
        );

        $favoriteSongsQuery = SongFavorite::query()
            ->with(['song.artist:id,name,artist_name', 'song.album:id,title'])
            ->where('user_id', $user->id);

        $this->applyFavoriteSongFilters($favoriteSongsQuery, $filters['favorites']);

        $favoriteSongsTotal = (clone $favoriteSongsQuery)->count();
        $favoriteSongs = $this->limitSectionCollection(
            $this->sortFavoriteSongs((clone $favoriteSongsQuery)->get(), $filters['favorites']['sort']),
            8
        );

        $recentHistory = ListeningHistory::query()
            ->with(['song.artist:id,name,artist_name', 'song.album:id,title'])
            ->where('user_id', $user->id)
            ->latest('listened_at')
            ->take(8)
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
            'filters',
            'followedArtists',
            'followedArtistsTotal',
            'savedAlbums',
            'savedAlbumsTotal',
            'favoriteSongs',
            'favoriteSongsTotal',
            'recentHistory',
            'notificationSetting'
        ));
    }

    private function validateListenerDashboardFilters(Request $request): array
    {
        $validated = $request->validate([
            'artists_q' => ['nullable', 'string', 'max:120'],
            'artists_sort' => ['nullable', 'in:recent,oldest,name'],
            'artists_from_date' => ['nullable', 'date'],
            'artists_to_date' => ['nullable', 'date', 'after_or_equal:artists_from_date'],
            'albums_q' => ['nullable', 'string', 'max:120'],
            'albums_sort' => ['nullable', 'in:recent,oldest,title'],
            'albums_from_date' => ['nullable', 'date'],
            'albums_to_date' => ['nullable', 'date', 'after_or_equal:albums_from_date'],
            'favorites_q' => ['nullable', 'string', 'max:120'],
            'favorites_sort' => ['nullable', 'in:recent,oldest,title'],
            'favorites_from_date' => ['nullable', 'date'],
            'favorites_to_date' => ['nullable', 'date', 'after_or_equal:favorites_from_date'],
        ]);

        return [
            'artists' => [
                'q' => trim((string) data_get($validated, 'artists_q', '')),
                'sort' => data_get($validated, 'artists_sort', 'recent'),
                'from_date' => data_get($validated, 'artists_from_date'),
                'to_date' => data_get($validated, 'artists_to_date'),
            ],
            'albums' => [
                'q' => trim((string) data_get($validated, 'albums_q', '')),
                'sort' => data_get($validated, 'albums_sort', 'recent'),
                'from_date' => data_get($validated, 'albums_from_date'),
                'to_date' => data_get($validated, 'albums_to_date'),
            ],
            'favorites' => [
                'q' => trim((string) data_get($validated, 'favorites_q', '')),
                'sort' => data_get($validated, 'favorites_sort', 'recent'),
                'from_date' => data_get($validated, 'favorites_from_date'),
                'to_date' => data_get($validated, 'favorites_to_date'),
            ],
        ];
    }

    private function applyDateRangeFilter(Builder $query, array $filters, string $fromKey, string $toKey, string $column = 'created_at'): void
    {
        if (! empty($filters[$fromKey])) {
            $query->whereDate($column, '>=', Carbon::parse($filters[$fromKey])->toDateString());
        }

        if (! empty($filters[$toKey])) {
            $query->whereDate($column, '<=', Carbon::parse($filters[$toKey])->toDateString());
        }
    }

    private function applyFollowedArtistFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $query->whereHas('artist', function (Builder $artistQuery) use ($filters) {
                $artistQuery->where(function (Builder $nested) use ($filters) {
                    $nested->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('artist_name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('bio', 'like', '%' . $filters['q'] . '%');
                });
            });
        }

        $this->applyDateRangeFilter($query, $filters, 'from_date', 'to_date');
    }

    private function applySavedAlbumFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $query->whereHas('album', function (Builder $albumQuery) use ($filters) {
                $albumQuery->where(function (Builder $nested) use ($filters) {
                    $nested->where('title', 'like', '%' . $filters['q'] . '%')
                        ->orWhereHas('artist', function (Builder $artistQuery) use ($filters) {
                            $artistQuery->where(function (Builder $artistNested) use ($filters) {
                                $artistNested->where('name', 'like', '%' . $filters['q'] . '%')
                                    ->orWhere('artist_name', 'like', '%' . $filters['q'] . '%');
                            });
                        });
                });
            });
        }

        $this->applyDateRangeFilter($query, $filters, 'from_date', 'to_date');
    }

    private function applyFavoriteSongFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $query->whereHas('song', function (Builder $songQuery) use ($filters) {
                $songQuery->where(function (Builder $nested) use ($filters) {
                    $nested->where('title', 'like', '%' . $filters['q'] . '%')
                        ->orWhereHas('artist', function (Builder $artistQuery) use ($filters) {
                            $artistQuery->where(function (Builder $artistNested) use ($filters) {
                                $artistNested->where('name', 'like', '%' . $filters['q'] . '%')
                                    ->orWhere('artist_name', 'like', '%' . $filters['q'] . '%');
                            });
                        })
                        ->orWhereHas('album', function (Builder $albumQuery) use ($filters) {
                            $albumQuery->where('title', 'like', '%' . $filters['q'] . '%');
                        });
                });
            });
        }

        $this->applyDateRangeFilter($query, $filters, 'from_date', 'to_date');
    }

    private function sortFollowedArtists(Collection $items, string $sort): Collection
    {
        return match ($sort) {
            'oldest' => $items->sortBy('created_at')->values(),
            'name' => $items->sortBy(function ($item) {
                return mb_strtolower($item->artist?->getDisplayArtistName() ?? $item->artist?->name ?? '');
            })->values(),
            default => $items->sortByDesc('created_at')->values(),
        };
    }

    private function sortSavedAlbums(Collection $items, string $sort): Collection
    {
        return match ($sort) {
            'oldest' => $items->sortBy('created_at')->values(),
            'title' => $items->sortBy(function ($item) {
                return mb_strtolower($item->album?->title ?? '');
            })->values(),
            default => $items->sortByDesc('created_at')->values(),
        };
    }

    private function sortFavoriteSongs(Collection $items, string $sort): Collection
    {
        return match ($sort) {
            'oldest' => $items->sortBy('created_at')->values(),
            'title' => $items->sortBy(function ($item) {
                return mb_strtolower($item->song?->title ?? '');
            })->values(),
            default => $items->sortByDesc('created_at')->values(),
        };
    }

    private function limitSectionCollection(Collection $items, int $limit): Collection
    {
        return $items->take($limit)->values();
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

    public function favorites(Request $request): View
    {
        $filters = $this->validateFavoriteSectionFilters($request);

        $favoritesQuery = SongFavorite::query()
            ->with(['song.artist:id,name,artist_name', 'song.album:id,title'])
            ->where('user_id', Auth::id());

        $this->applyFavoritePageFilters($favoritesQuery, $filters);

        $favorites = $this->paginateFavoriteSongs($favoritesQuery, $filters['sort']);

        return view('pages.listener-favorites', compact('favorites', 'filters'));
    }

    public function albums(Request $request): View
    {
        $filters = $this->validateAlbumSectionFilters($request);

        $savedAlbumsQuery = SavedAlbum::query()
            ->with([
                'album.artist:id,name,artist_name,avatar,artist_verified_at',
                'album' => function ($query) {
                    $query->withCount([
                        'songs as published_songs_count' => fn ($songQuery) => $songQuery->published(),
                    ]);
                },
            ])
            ->where('user_id', Auth::id());

        $this->applyAlbumPageFilters($savedAlbumsQuery, $filters);

        $savedAlbums = $this->paginateSavedAlbums($savedAlbumsQuery, $filters['sort']);

        return view('pages.listener-albums', compact('savedAlbums', 'filters'));
    }

    private function validateFavoriteSectionFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:recent,oldest,title'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'sort' => $validated['sort'] ?? 'recent',
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
        ];
    }

    private function validateAlbumSectionFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'in:recent,oldest,title'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'sort' => $validated['sort'] ?? 'recent',
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
        ];
    }

    private function applyFavoritePageFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $query->whereHas('song', function (Builder $songQuery) use ($filters) {
                $songQuery->where(function (Builder $nested) use ($filters) {
                    $nested->where('title', 'like', '%' . $filters['q'] . '%')
                        ->orWhereHas('artist', function (Builder $artistQuery) use ($filters) {
                            $artistQuery->where(function (Builder $artistNested) use ($filters) {
                                $artistNested->where('name', 'like', '%' . $filters['q'] . '%')
                                    ->orWhere('artist_name', 'like', '%' . $filters['q'] . '%');
                            });
                        })
                        ->orWhereHas('album', function (Builder $albumQuery) use ($filters) {
                            $albumQuery->where('title', 'like', '%' . $filters['q'] . '%');
                        });
                });
            });
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }
    }

    private function applyAlbumPageFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $query->whereHas('album', function (Builder $albumQuery) use ($filters) {
                $albumQuery->where(function (Builder $nested) use ($filters) {
                    $nested->where('title', 'like', '%' . $filters['q'] . '%')
                        ->orWhereHas('artist', function (Builder $artistQuery) use ($filters) {
                            $artistQuery->where(function (Builder $artistNested) use ($filters) {
                                $artistNested->where('name', 'like', '%' . $filters['q'] . '%')
                                    ->orWhere('artist_name', 'like', '%' . $filters['q'] . '%');
                            });
                        });
                });
            });
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }
    }

    private function paginateFavoriteSongs(Builder $query, string $sort)
    {
        if ($sort === 'title') {
            $query->join('songs', 'song_favorites.song_id', '=', 'songs.id')
                ->select('song_favorites.*')
                ->orderBy('songs.title')
                ->orderByDesc('song_favorites.created_at');
        } elseif ($sort === 'oldest') {
            $query->orderBy('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query->paginate(24)->withQueryString();
    }

    private function paginateSavedAlbums(Builder $query, string $sort)
    {
        if ($sort === 'title') {
            $query->join('albums', 'saved_albums.album_id', '=', 'albums.id')
                ->select('saved_albums.*')
                ->orderBy('albums.title')
                ->orderByDesc('saved_albums.created_at');
        } elseif ($sort === 'oldest') {
            $query->orderBy('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query->paginate(24)->withQueryString();
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
