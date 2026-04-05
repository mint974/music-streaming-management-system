<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\ArtistFollow;
use App\Models\SavedAlbum;
use App\Models\SearchHistory;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SearchController extends Controller
{
    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Chuẩn hóa tab tìm kiếm.
     */
    private function resolveTab(string $tab): string
    {
        return in_array($tab, ['artists', 'songs', 'albums'], true) ? $tab : 'artists';
    }

    /**
     * Query nền cho nghệ sĩ.
     */
    private function artistQuery(string $q): Builder
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'artist'))
            ->where('deleted', false)
            ->where('status', '!=', 'Bị khóa')
            ->where(function ($query) use ($q) {
                $query->where('artist_name', 'LIKE', "%{$q}%")
                    ->orWhere('name', 'LIKE', "%{$q}%")
                    ->orWhere('bio', 'LIKE', "%{$q}%");
            })
            ->orderByRaw(
                "
                CASE
                    WHEN LOWER(artist_name) = ? THEN 0
                    WHEN LOWER(name) = ? THEN 1
                    WHEN LOWER(artist_name) LIKE ? THEN 2
                    WHEN LOWER(name) LIKE ? THEN 3
                    ELSE 4
                END
            ",
                [
                    mb_strtolower($q),
                    mb_strtolower($q),
                    mb_strtolower($q) . '%',
                    mb_strtolower($q) . '%',
                ]
            );
    }

    /**
     * Tìm kiếm nghệ sĩ theo từ khóa.
     */
    private function searchArtists(string $q, int $limit = 20): \Illuminate\Support\Collection
    {
        return $this->artistQuery($q)
            ->limit($limit)
            ->get(['id', 'name', 'artist_name', 'avatar', 'artist_verified_at', 'bio']);
    }

    /**
     * Query nền cho bài hát công khai.
     */
    private function songQuery(string $q)
    {
        return Song::query()
            ->published()
            ->with([
                'artist:id,name,artist_name,avatar,artist_verified_at',
                'album:id,title',
            ])
            ->where(function ($query) use ($q) {
                $query->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('author', 'LIKE', "%{$q}%")
                    ->orWhereHas('artist', function ($artistQuery) use ($q) {
                        $artistQuery->where('artist_name', 'LIKE', "%{$q}%")
                            ->orWhere('name', 'LIKE', "%{$q}%");
                    })
                    ->orWhereHas('album', function ($albumQuery) use ($q) {
                        $albumQuery->where('title', 'LIKE', "%{$q}%");
                    });
            })
            ->orderByRaw(
                "
                CASE
                    WHEN LOWER(title) = ? THEN 0
                    WHEN LOWER(title) LIKE ? THEN 1
                    ELSE 2
                END
            ",
                [
                    mb_strtolower($q),
                    mb_strtolower($q) . '%',
                ]
            )
            ->orderByDesc('listens')
            ->orderByDesc('released_date')
            ->orderByDesc('id');
    }

    /**
     * Query nền cho album công khai.
     */
    private function albumQuery(string $q): Builder
    {
        return Album::query()
            ->published()
            ->with(['artist:id,name,artist_name,avatar,artist_verified_at'])
            ->withCount([
                'songs as published_songs_count' => function ($songQuery) {
                    $songQuery->published();
                },
            ])
            ->where(function ($query) use ($q) {
                $query->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('description', 'LIKE', "%{$q}%")
                    ->orWhereHas('artist', function ($artistQuery) use ($q) {
                        $artistQuery->where('artist_name', 'LIKE', "%{$q}%")
                            ->orWhere('name', 'LIKE', "%{$q}%");
                    });
            })
            ->orderByRaw(
                "
                CASE
                    WHEN LOWER(title) = ? THEN 0
                    WHEN LOWER(title) LIKE ? THEN 1
                    ELSE 2
                END
            ",
                [
                    mb_strtolower($q),
                    mb_strtolower($q) . '%',
                ]
            )
            ->orderByDesc('released_date')
            ->orderByDesc('id');
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    /**
     * Trang tìm kiếm chính.
     * GET /search
     */
    public function index(Request $request): View
    {
        $q       = trim($request->input('q', ''));
        $tab     = $this->resolveTab((string) $request->input('tab', 'artists'));
        $artists = collect();
        $songs   = collect();
        $albums  = collect();

        $counts = [
            'artists' => 0,
            'songs'   => 0,
            'albums'  => 0,
        ];

        if ($q !== '') {
            $artistQuery = $this->artistQuery($q);
            $songQuery   = $this->songQuery($q);
            $albumQuery  = $this->albumQuery($q);

            $counts['artists'] = (clone $artistQuery)->count();
            $counts['songs']   = (clone $songQuery)->count();
            $counts['albums']  = (clone $albumQuery)->count();

            if ($tab === 'artists') {
                $artists = (clone $artistQuery)
                    ->limit(24)
                    ->get(['id', 'name', 'artist_name', 'avatar', 'artist_verified_at', 'bio']);
            }

            if ($tab === 'songs') {
                $songs = (clone $songQuery)
                    ->paginate(12, ['*'], 'songs_page')
                    ->withQueryString();
            }

            if ($tab === 'albums') {
                $albums = (clone $albumQuery)
                    ->paginate(12, ['*'], 'albums_page')
                    ->withQueryString();
            }

            // Ghi lịch sử cho user đăng nhập
            if (Auth::check()) {
                SearchHistory::record((int) Auth::id(), $q);
            }
        }

        // Lịch sử cho user đăng nhập (gửi xuống view để render)
        $history = Auth::check()
            ? SearchHistory::recent((int) Auth::id(), 8)
            : [];

        $totalResults = $counts['artists'] + $counts['songs'] + $counts['albums'];

        return view('pages.search', compact(
            'q',
            'tab',
            'artists',
            'songs',
            'albums',
            'counts',
            'totalResults',
            'history'
        ));
    }

    /**
     * Trang chi tiết tài khoản nghệ sĩ công khai.
     * GET /search/artists/{artistId}
     */
    public function artistShow(Request $request, int $artistId): View
    {
        $tab = in_array($request->input('tab', 'songs'), ['songs', 'albums'], true)
            ? (string) $request->input('tab', 'songs')
            : 'songs';

        $artist = User::with('socialLinks')
            ->where('id', $artistId)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'artist'))
            ->where('deleted', false)
            ->where('status', '!=', 'Bị khóa')
            ->firstOrFail();

        $songsQuery = Song::published()
            ->with('album:id,title')
            ->where('user_id', $artist->id)
            ->orderByDesc('released_date')
            ->orderByDesc('id');

        $albumsQuery = Album::published()
            ->withCount([
                'songs as published_songs_count' => function ($query) {
                    $query->published();
                },
            ])
            ->where('user_id', $artist->id)
            ->orderByDesc('released_date')
            ->orderByDesc('id');

        $songsCount = (clone $songsQuery)->count();
        $albumsCount = (clone $albumsQuery)->count();

        $songs = collect();
        $albums = collect();

        if ($tab === 'songs') {
            $songs = (clone $songsQuery)
                ->paginate(12, ['*'], 'songs_page')
                ->withQueryString();
        }

        if ($tab === 'albums') {
            $albums = (clone $albumsQuery)
                ->paginate(12, ['*'], 'albums_page')
                ->withQueryString();
        }

        $latestRegistration = $artist->artistRegistrations()
            ->with('package:id,name')
            ->whereIn('status', ['approved', 'expired'])
            ->latest('reviewed_at')
            ->first();

        $isFollowingArtist = false;
        $savedAlbumIds = [];

        if (Auth::check()) {
            $userId = (int) Auth::id();

            $isFollowingArtist = ArtistFollow::query()
                ->where('user_id', $userId)
                ->where('artist_id', $artist->id)
                ->exists();

            $savedAlbumIds = SavedAlbum::query()
                ->where('user_id', $userId)
                ->pluck('album_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
        }

        return view('pages.search-artist-detail', [
            'artist'             => $artist,
            'tab'                => $tab,
            'songs'              => $songs,
            'albums'             => $albums,
            'songsCount'         => $songsCount,
            'albumsCount'        => $albumsCount,
            'latestRegistration' => $latestRegistration,
            'isFollowingArtist'  => $isFollowingArtist,
            'savedAlbumIds'      => $savedAlbumIds,
        ]);
    }

    /**
     * API autocomplete – trả về JSON instantly.
     * GET /search/autocomplete?q=...
     * Không yêu cầu đăng nhập (khách cũng dùng được).
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (mb_strlen($q) < 1) {
            return response()->json(['results' => [], 'history' => []]);
        }

        $artists = $this->searchArtists($q, 6)->map(function (User $u) {
            $nameForInitial = $u->name ?: ($u->artist_name ?: 'U');
            $initial    = mb_strtoupper(mb_substr($nameForInitial, 0, 1, 'UTF-8'), 'UTF-8');
            $avatarSvg  = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23a855f7'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='16' fill='%23ffffff' font-weight='bold'%3E{$initial}%3C/text%3E%3C/svg%3E";
            $avatar     = ($u->avatar && $u->avatar !== '/storage/avt.jpg')
                            ? asset($u->avatar) : $avatarSvg;

            return [
                'type'       => 'artist',
                'id'         => $u->id,
                'label'      => $u->artist_name ?: $u->name,
                'sublabel'   => 'Nghệ sĩ',
                'avatar'     => $avatar,
                'verified'   => (bool) $u->artist_verified_at,
                'url'        => route('search.artist.show', ['artistId' => $u->id]),
            ];
        });

        // Lịch sử DB (auth user) – filtered by query prefix
        $history = [];
        if (Auth::check()) {
            $history = SearchHistory::where('user_id', Auth::id())
                ->where('query', 'LIKE', "{$q}%")
                ->orderByDesc('created_at')
                ->limit(4)
                ->pluck('query')
                ->toArray();
        }

        return response()->json([
            'results' => $artists->values(),
            'history' => $history,
        ]);
    }

    /**
     * Xóa toàn bộ lịch sử tìm kiếm của user.
     * DELETE /search/history
     */
    public function clearHistory(Request $request): JsonResponse|RedirectResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false], 401);
        }

        SearchHistory::where('user_id', Auth::id())->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Đã xóa lịch sử tìm kiếm.');
    }

    /**
     * Xóa một mục lịch sử.
     * DELETE /search/history/item
     */
    public function removeHistoryItem(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false], 401);
        }

        $q = trim($request->input('query', ''));
        if ($q !== '') {
            SearchHistory::where('user_id', Auth::id())
                ->whereRaw('LOWER(query) = ?', [mb_strtolower($q)])
                ->delete();
        }

        return response()->json(['ok' => true]);
    }
}
