<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Tìm kiếm nghệ sĩ theo từ khóa.
     */
    private function searchArtists(string $q, int $limit = 20): \Illuminate\Support\Collection
    {
        return User::where('role', 'artist')
            ->where('deleted', false)
            ->where('status', '!=', 'Bị khóa')
            ->where(function ($query) use ($q) {
                $query->where('artist_name', 'LIKE', "%{$q}%")
                      ->orWhere('name', 'LIKE', "%{$q}%")
                      ->orWhere('bio', 'LIKE', "%{$q}%");
            })
            ->orderByRaw("
                CASE
                    WHEN LOWER(artist_name) = ? THEN 0
                    WHEN LOWER(name) = ? THEN 1
                    WHEN LOWER(artist_name) LIKE ? THEN 2
                    WHEN LOWER(name) LIKE ? THEN 3
                    ELSE 4
                END
            ", [
                mb_strtolower($q),
                mb_strtolower($q),
                mb_strtolower($q) . '%',
                mb_strtolower($q) . '%',
            ])
            ->limit($limit)
            ->get(['id', 'name', 'artist_name', 'avatar', 'artist_verified_at', 'bio']);
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    /**
     * Trang tìm kiếm chính.
     * GET /search
     */
    public function index(Request $request): View
    {
        $q       = trim($request->input('q', ''));
        $artists = collect();

        if ($q !== '') {
            $artists = $this->searchArtists($q);

            // Ghi lịch sử cho user đăng nhập
            if (auth()->check()) {
                SearchHistory::record(auth()->id(), $q);
            }
        }

        // Lịch sử cho user đăng nhập (gửi xuống view để render)
        $history = auth()->check()
            ? SearchHistory::recent(auth()->id(), 8)
            : [];

        return view('pages.search', compact('q', 'artists', 'history'));
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
            $initial    = strtoupper(substr($u->name, 0, 1));
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
                'url'        => route('search', ['q' => $u->artist_name ?: $u->name]),
            ];
        });

        // Lịch sử DB (auth user) – filtered by query prefix
        $history = [];
        if (auth()->check()) {
            $history = SearchHistory::where('user_id', auth()->id())
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
        if (! auth()->check()) {
            return response()->json(['ok' => false], 401);
        }

        SearchHistory::where('user_id', auth()->id())->delete();

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
        if (! auth()->check()) {
            return response()->json(['ok' => false], 401);
        }

        $q = trim($request->input('query', ''));
        if ($q !== '') {
            SearchHistory::where('user_id', auth()->id())
                ->whereRaw('LOWER(query) = ?', [mb_strtolower($q)])
                ->delete();
        }

        return response()->json(['ok' => true]);
    }
}
