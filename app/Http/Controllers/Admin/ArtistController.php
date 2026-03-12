<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $wasVerified = $artist->artist_verified_at !== null;

        $this->repo->adminToggleArtistVerified($artist, $admin->id);

        $action = $wasVerified ? 'Thu hồi xác minh' : 'Cấp tick xanh xác minh chính thức';
        return back()->with('success', "{$action} cho nghệ sĩ <strong>{$artist->name}</strong> thành công.");
    }

    /**
     * Thu hồi vĩnh viễn quyền nghệ sĩ — yêu cầu xác nhận mật khẩu admin.
     * Bài hát/album hiện có được giữ nguyên theo status của chúng.
     * POST /admin/artists/{id}/revoke
     */
    public function revoke(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'admin_password' => ['required', 'string'],
            'revoke_reason'  => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'admin_password.required' => 'Vui lòng nhập mật khẩu xác nhận.',
            'revoke_reason.required'  => 'Vui lòng nhập lý do thu hồi.',
            'revoke_reason.min'       => 'Lý do phải có ít nhất 10 ký tự.',
        ]);

        $admin  = Auth::guard('admin')->user();
        $artist = $this->repo->findById($id);

        if (! $artist || ! $artist->isArtist()) {
            return back()->with('error', 'Không tìm thấy nghệ sĩ.');
        }

        if ($artist->isArtistRevoked()) {
            return back()->with('error', 'Tài khoản này đã bị thu hồi quyền nghệ sĩ trước đó.');
        }

        // Xác thực mật khẩu admin trước khi thực hiện
        if (! Hash::check($request->input('admin_password'), $admin->password)) {
            return back()->with('error', 'Mật khẩu xác nhận không đúng. Hành động bị hủy.');
        }

        $this->repo->adminRevokeArtist($artist, $admin->id, $request->input('revoke_reason'));

        return back()->with('success',
            "Đã thu hồi vĩnh viễn quyền Nghệ sĩ của <strong>{$artist->name}</strong>. "
            . 'Dữ liệu âm nhạc được giữ nguyên. Email thông báo đã được gửi.');
    }
}
