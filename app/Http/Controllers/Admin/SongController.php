<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SongController extends Controller
{
    // ── Số bản ghi mỗi trang ─────────────────────────────────────────────────
    private const PER_PAGE = 18;

    /**
     * Danh sách bài hát – lọc theo thể loại, nghệ sĩ, trạng thái, VIP, xóa.
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'search', 'genre_id', 'status', 'is_vip', 'deleted', 'released_year'
        ]);

        $query = Song::with(['artist', 'genre', 'album'])
            // Tìm kiếm kết hợp (Tên bài, tên tác giả tự do, hoặc tên nghệ sĩ)
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('title', 'like', "%{$search}%")
                       ->orWhere('author', 'like', "%{$search}%")
                       ->orWhereHas('artist', fn($q3) => $q3->where('name', 'like', "%{$search}%")
                           ->orWhere('artist_name', 'like', "%{$search}%"));
                });
            })
            // Lọc theo thể loại
            ->when($filters['genre_id'] ?? null, fn($q, $v) => $q->where('genre_id', $v))
            // Lọc theo trạng thái
            ->when(isset($filters['status']) && $filters['status'] !== '', fn($q) => $q->where('status', $filters['status']))
            // Lọc VIP
            ->when(isset($filters['is_vip']) && $filters['is_vip'] !== '', fn($q) => $q->where('is_vip', (bool) $filters['is_vip']))
            // Lọc đã xóa
            ->when(isset($filters['deleted']) && $filters['deleted'] !== '', fn($q) => $q->where('deleted', (bool) $filters['deleted']))
            // Lọc theo năm phát hành
            ->when($filters['released_year'] ?? null, fn($q, $y) => $q->whereYear('released_date', $y))
            ->latest();

        $songs  = $query->paginate(self::PER_PAGE)->withQueryString();
        $genres = Genre::orderBy('name')->get(['id', 'name']);

        // Stats cards
        $stats = [
            'total'     => Song::count(),
            'published' => Song::where('status', 'published')->where('deleted', false)->count(),
            'hidden'    => Song::where('deleted', false)->whereIn('status', ['hidden', 'pending', 'draft'])->count(),
            'deleted'   => Song::where('deleted', true)->count(),
            'vip'       => Song::where('is_vip', true)->where('deleted', false)->count(),
        ];

        // Lấy danh sách năm phát hành để hiển thị dropdown
        $releaseYears = Song::whereNotNull('released_date')
            ->selectRaw('YEAR(released_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('admin.songs.index', compact('songs', 'filters', 'genres', 'stats', 'releaseYears'));
    }

    /**
     * Chi tiết bài hát (readonly, thông tin đầy đủ).
     */
    public function show(Song $song): View
    {
        $song->load(['artist', 'genre', 'album', 'tags']);
        return view('admin.songs.show', compact('song'));
    }

    /**
     * Admin ẩn bài hát (status → hidden) hoặc bỏ ẩn (status → published).
     * Dùng cho vi phạm nhẹ — bài hát vẫn còn trong DB.
     */
    public function toggleHide(Song $song): RedirectResponse
    {
        $newStatus = $song->status === 'hidden' ? 'published' : 'hidden';
        $song->update(['status' => $newStatus]);

        $label = $newStatus === 'hidden' ? 'ẩn' : 'hiện';
        return back()->with('success', "Đã {$label} bài hát <strong>{$song->title}</strong>.");
    }

    /**
     * Admin đánh dấu bài hát là đã xóa mềm (deleted = true).
     * Dùng khi vi phạm bản quyền hoặc nội dung không phù hợp.
     */
    public function softDelete(Request $request, Song $song): RedirectResponse
    {
        $request->validate(
            ['reason' => ['required', 'string', 'min:10', 'max:500']],
            ['reason.required' => 'Vui lòng nhập lý do gỡ bỏ bài hát.']
        );

        $song->update([
            'deleted' => true,
            'status'  => 'hidden',
        ]);

        return back()->with('success',
            "Đã gỡ bỏ bài hát <strong>{$song->title}</strong>. Lý do: {$request->reason}"
        );
    }

    /**
     * Khôi phục bài hát bị xóa mềm.
     */
    public function restore(Song $song): RedirectResponse
    {
        $song->update([
            'deleted' => false,
            'status'  => 'published',
        ]);

        return back()->with('success', "Đã khôi phục bài hát <strong>{$song->title}</strong>.");
    }

    /**
     * Xóa cứng bài hát (không thể phục hồi).
     * Chỉ được xóa khi bài hát đã bị deleted = true.
     */
    public function forceDelete(Song $song): RedirectResponse
    {
        if (! $song->deleted) {
            return back()->with('error', 'Chỉ có thể xóa vĩnh viễn bài hát đã bị gỡ bỏ.');
        }

        $title = $song->title;

        // Xóa file âm thanh
        if ($song->file_path && Storage::disk('public')->exists($song->file_path)) {
            Storage::disk('public')->delete($song->file_path);
        }

        // Xóa cover
        if ($song->cover_image && Storage::disk('public')->exists($song->cover_image)) {
            Storage::disk('public')->delete($song->cover_image);
        }

        $song->forceDelete();

        return redirect()->route('admin.songs.index')
            ->with('success', "Đã xóa vĩnh viễn bài hát <strong>{$title}</strong>.");
    }

    /**
     * Danh sách trả về từ API – dùng cho filter artist autocomplete.
     */
    public function artistSearch(Request $request)
    {
        $q = $request->get('q', '');
        $artists = User::where('role', 'artist')
            ->where(fn($q2) => $q2->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
            ->select('id', 'name', 'email', 'avatar')
            ->limit(10)
            ->get();

        return response()->json($artists);
    }
}
