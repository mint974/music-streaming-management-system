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
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
     * API tìm kiếm bằng giọng nói (speech-to-text transcript).
     * POST /search/voice
     */
    public function voiceSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transcript' => 'required|string|min:1|max:200',
        ]);

        $query = trim((string) $validated['transcript']);
        if ($query === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Nội dung giọng nói không hợp lệ.',
            ], 422);
        }

        $previewSongs = Song::query()
            ->published()
            ->with(['artist:id,name,artist_name'])
            ->where(function ($songQuery) use ($query) {
                $songQuery->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('author', 'LIKE', "%{$query}%")
                    ->orWhereHas('artist', function ($artistQuery) use ($query) {
                        $artistQuery->where('artist_name', 'LIKE', "%{$query}%")
                            ->orWhere('name', 'LIKE', "%{$query}%");
                    });
            })
            ->orderByDesc('listens')
            ->limit(5)
            ->get()
            ->map(function (Song $song) {
                return [
                    'id' => $song->id,
                    'title' => $song->title,
                    'artist_name' => $song->artist?->artist_name ?: $song->artist?->name ?: 'Nghệ sĩ',
                    'song_url' => route('songs.show', $song->id),
                ];
            })
            ->values();

        return response()->json([
            'ok' => true,
            'query' => $query,
            'redirect_url' => route('search', ['q' => $query]),
            'preview' => $previewSongs,
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

    /**
     * API Tìm kiếm bằng giai điệu (Hum-to-Search)
     * POST /search/humming
     */
    public function hummingSearch(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'ok' => false,
                'message' => 'Vui lòng đăng nhập để sử dụng tìm kiếm ngân nga.',
            ], 401);
        }

        $user = User::find(Auth::id());
        if (!$user || !$user->isPremium()) {
            return response()->json([
                'ok' => false,
                'message' => 'Tính năng tìm kiếm ngân nga chỉ dành cho tài khoản Premium.',
            ], 403);
        }

        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,webm,ogg,m4a,mp4|max:10240', // Tối đa 10MB
            'top_k' => 'nullable|integer|min:1|max:20',
        ]);

        $file = $request->file('audio');
        $topK = (int) $request->input('top_k', 5);

        [$forwardPath, $forwardName, $cleanupPath] = $this->prepareAudioForAi($file);

        try {
            // Forward file thu âm sang máy chủ AI Python
            $response = \Illuminate\Support\Facades\Http::attach(
                'audio',
                File::get($forwardPath),
                $forwardName
            )->post('http://127.0.0.1:8000/search-humming?top_k=' . $topK);

            if (!$response->successful()) {
                return response()->json(['ok' => false, 'message' => 'AI Server error: ' . $response->body()], 500);
            }

            // AI trả về danh sách object: file_name/song, score, confidence...
            $aiSongs = collect($response->json('songs', []))
                ->filter(fn ($item) => is_array($item) || is_string($item))
                ->values();

            if ($aiSongs->isEmpty()) {
                return response()->json(['ok' => true, 'matches' => []]);
            }

            // Kéo thông tin chi tiết bài hát từ bảng songs và map theo file stem
            $matchedSongs = Song::published()
                ->with(['artist:id,name,artist_name,avatar'])
                ->get()
                ->keyBy(function (Song $song) {
                    return Str::of(str_replace('\\', '/', (string) $song->file_path))
                        ->basename()
                        ->beforeLast('.')
                        ->toString();
                });

            $matches = $aiSongs->map(function ($item) use ($matchedSongs) {
                $fileName = is_array($item)
                    ? (string) ($item['file_name'] ?? $item['song'] ?? '')
                    : (string) $item;

                $stem = Str::of(str_replace('\\', '/', $fileName))
                    ->basename()
                    ->beforeLast('.')
                    ->toString();
                if ($stem === '') {
                    return null;
                }

                /** @var Song|null $song */
                $song = $matchedSongs->get($stem);
                if (!$song) {
                    return null;
                }

                $artistName = $song->artist?->artist_name ?: $song->artist?->name ?: 'Nghệ sĩ';

                return [
                    'id' => $song->id,
                    'title' => $song->title,
                    'file_name' => $stem,
                    'file_path' => $song->file_path,
                    'song_url' => route('songs.show', $song->id),
                    'stream_url' => route('songs.stream', $song->id),
                    'cover_url' => $song->getCoverUrl(),
                    'artist_name' => $artistName,
                    'artist_id' => $song->artist?->id,
                    'album_title' => $song->album?->title,
                    'duration_formatted' => $song->durationFormatted(),
                    'duration' => $song->duration,
                    'listens' => (int) $song->listens,
                    'is_vip' => (bool) $song->is_vip,
                    'score' => round((float) ($item['score'] ?? $item['distance'] ?? 0.0), 6),
                    'confidence' => round((float) ($item['confidence'] ?? 0.0), 1),
                    'support_ratio' => round((float) ($item['support_ratio'] ?? 0.0), 4),
                    'margin12' => round((float) ($item['margin12'] ?? 0.0), 6),
                    'best_distance' => round((float) ($item['best_distance'] ?? $item['distance'] ?? 0.0), 6),
                    'best_window_index' => $item['best_window_index'] ?? null,
                    'rank_gap_to_next' => $item['rank_gap_to_next'] ?? null,
                    'num_windows' => $item['num_windows'] ?? null,
                ];
            })->filter()->values();

            return response()->json([
                'ok' => true,
                'matches' => $matches,
            ]);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Lỗi kết nối AI: ' . $e->getMessage()], 500);
        } finally {
            if ($cleanupPath && File::exists($cleanupPath)) {
                File::delete($cleanupPath);
            }
        }
    }

    /**
     * Chuẩn hóa file upload trước khi gửi sang AI server.
     * AI endpoint hiện ổn định nhất với .wav / .mp3, nên convert các định dạng khác về wav.
     *
     * @return array{0:string,1:string,2:?string} [forwardPath, forwardName, cleanupPath]
     */
    private function prepareAudioForAi(UploadedFile $file): array
    {
        $originalPath = $file->getRealPath();
        $originalName = $file->getClientOriginalName();
        $ext = Str::lower($file->getClientOriginalExtension() ?: '');

        if (in_array($ext, ['mp3', 'wav'], true)) {
            return [$originalPath, $originalName, null];
        }

        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $outputName = 'humming_' . Str::uuid() . '.wav';
        $outputPath = $tempDir . DIRECTORY_SEPARATOR . $outputName;

        // Convert về mono wav 16k để AI đọc ổn định hơn.
        $command = sprintf(
            'ffmpeg -y -i %s -ac 1 -ar 16000 %s 2>&1',
            escapeshellarg($originalPath),
            escapeshellarg($outputPath)
        );

        @exec($command, $outputLines, $exitCode);
        if ($exitCode !== 0 || !File::exists($outputPath)) {
            throw new \Exception('Không thể xử lý file audio upload. Vui lòng thử file khác.');
        }

        return [$outputPath, $outputName, $outputPath];
    }
}
