<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArtistController extends Controller
{
    public function __construct(protected UserRepository $repo) {}

    /**
     * Danh sách nghệ sĩ với lọc theo xác minh, trạng thái, tìm kiếm.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'verified', 'status']);
        $artists = $this->repo->getAdminArtistList($filters, 15);

        return view('admin.artists.index', compact('artists', 'filters'));
    }

    /**
     * Khóa / mở khóa tài khoản nghệ sĩ.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $admin  = Auth::guard('admin')->user();
        $artist = $this->repo->findById($id);

        if (! $artist || ! $artist->isArtist()) {
            return back()->with('error', 'Không tìm thấy nghệ sĩ.');
        }

        $this->repo->adminToggleStatus($artist, $admin->id);

        $action = $artist->fresh()->status === 'Bị khóa' ? 'khóa' : 'mở khóa';
        return back()->with('success', "Đã {$action} tài khoản nghệ sĩ <strong>{$artist->name}</strong>.");
    }

    /**
     * Cấp / thu hồi xác minh chính thức (tick xanh).
     */
    public function toggleVerify(int $id): RedirectResponse
    {
        $admin  = Auth::guard('admin')->user();
        $artist = $this->repo->findById($id);

        if (! $artist || ! $artist->isArtist()) {
            return back()->with('error', 'Không tìm thấy nghệ sĩ.');
        }

        $this->repo->adminToggleArtistVerified($artist, $admin->id);

        $wasVerified = $artist->artist_verified_at !== null;
        $action = $wasVerified ? 'Thu hồi xác minh' : 'Xác minh chính thức (tick xanh)';
        return back()->with('success', "{$action} cho nghệ sĩ <strong>{$artist->name}</strong> thành công.");
    }

    /**
     * Khóa và thu hồi quyền nghệ sĩ (chuyển về free).
     */
    public function revoke(int $id): RedirectResponse
    {
        $admin  = Auth::guard('admin')->user();
        $artist = $this->repo->findById($id);

        if (! $artist || ! $artist->isArtist()) {
            return back()->with('error', 'Không tìm thấy nghệ sĩ.');
        }

        $this->repo->adminChangeRole($artist, 'free', $admin->id);

        return back()->with('success', "Đã thu hồi quyền nghệ sĩ của <strong>{$artist->name}</strong>. Tài khoản chuyển về Thính giả miễn phí.");
    }
}
