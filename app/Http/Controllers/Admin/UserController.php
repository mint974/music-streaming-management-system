<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(protected UserRepository $repo) {}

    /**
     * Danh sách người dùng (free + premium), có lọc và tìm kiếm.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'role', 'status']);

        if (empty($filters['role'])) {
            $filters['role_in'] = ['free', 'premium'];
        }

        $users = $this->repo->getAdminUserList($filters, 15);
        $stats = $this->repo->adminGetStats();

        return view('admin.users.index', compact('users', 'filters', 'stats'));
    }

    /**
     * Trang chi tiết người dùng + lịch sử tài khoản.
     */
    public function show(int $id): View
    {
        $user = $this->repo->findById($id);

        if (! $user || $user->isAdmin()) {
            abort(404);
        }

        $history = $this->repo->getHistory($id);
        $subscriptions = $user->subscriptions()->with(['vip', 'payment'])->latest()->get();
        $artistRegistrations = $user->artistRegistrations()->with(['package', 'reviewer', 'refundConfirmer'])->latest()->get();

        return view('admin.users.show', compact('user', 'history', 'subscriptions', 'artistRegistrations'));
    }

    /**
     * Form tạo tài khoản mới.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Lưu tài khoản mới do admin tạo.
     */
    public function store(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'phone'                 => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'birthday'              => ['nullable', 'date', 'before:today'],
            'gender'                => ['nullable', 'in:Nam,Nữ,Khác'],
            'role'                  => ['required', 'in:free,premium,artist'],
        ], [
            'name.required'         => 'Vui lòng nhập họ tên.',
            'email.required'        => 'Vui lòng nhập email.',
            'email.unique'          => 'Email này đã được sử dụng.',
            'password.required'     => 'Vui lòng nhập mật khẩu.',
            'password.min'          => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed'    => 'Xác nhận mật khẩu không khớp.',
            'phone.unique'          => 'Số điện thoại này đã được sử dụng.',
        ]);

        $user = $this->repo->adminCreateUser($data, $admin->id);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', "Đã tạo tài khoản <strong>{$user->name}</strong> thành công.");
    }

    /**
     * Form chỉnh sửa tài khoản.
     */
    public function edit(int $id): View
    {
        $user = $this->repo->findById($id);

        if (! $user || $user->isAdmin()) {
            abort(404);
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Lưu chỉnh sửa tài khoản.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        $user  = $this->repo->findById($id);

        if (! $user || $user->isAdmin()) {
            abort(404);
        }

        $data = $request->validate([
            'name'                         => ['required', 'string', 'max:255'],
            'email'                        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'                        => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'birthday'                     => ['nullable', 'date', 'before:today'],
            'gender'                       => ['nullable', 'in:Nam,Nữ,Khác'],
            'role'                         => ['required', 'in:free,premium,artist'],
            'new_password'                 => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'                => 'Vui lòng nhập họ tên.',
            'email.required'               => 'Vui lòng nhập email.',
            'email.unique'                 => 'Email này đã được sử dụng bởi tài khoản khác.',
            'phone.unique'                 => 'Số điện thoại đã được sử dụng bởi tài khoản khác.',
            'new_password.min'             => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed'       => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        // Update basic info
        $updateData = collect($data)->except(['new_password', 'new_password_confirmation'])->toArray();
        $this->repo->adminUpdateUser($user, $updateData, $admin->id);

        // Reset password if provided
        if (! empty($data['new_password'])) {
            $this->repo->adminResetPassword($user, $data['new_password'], $admin->id);
        }

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', "Đã cập nhật tài khoản <strong>{$user->name}</strong>.");
    }

    /**
     * Khóa / mở khóa tài khoản.
     * Khi khóa phải cung cấp lý do.
     */
    public function toggleStatus(Request $request, int $id): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        $user  = $this->repo->findById($id);

        if (! $user || $user->isAdmin()) {
            return back()->with('error', 'Không tìm thấy người dùng hoặc không có quyền thực hiện.');
        }

        $isLocking = $user->status === 'Đang hoạt động';

        // Khi khóa tài khoản: bắt buộc nhập lý do
        if ($isLocking) {
            $request->validate(
                ['lock_reason' => ['required', 'string', 'max:500']],
                ['lock_reason.required' => 'Vui lòng nhập lý do khóa tài khoản.']
            );
        }

        $this->repo->adminToggleStatus($user, $admin->id, $isLocking ? $request->lock_reason : null);

        $user->refresh();
        $action = $user->status === 'Bị khóa' ? 'khóa' : 'mở khóa';
        return back()->with('success', "Đã {$action} tài khoản <strong>{$user->name}</strong>.");
    }

    /**
     * Đổi loại tài khoản (free ↔ premium, hoặc cấp vai trò artist).
     */
    public function changeRole(Request $request, int $id): RedirectResponse
    {
        $request->validate(
            ['role' => ['required', 'in:free,premium,artist']],
            ['role.in' => 'Loại tài khoản không hợp lệ.']
        );

        $admin = Auth::guard('admin')->user();
        $user  = $this->repo->findById($id);

        if (! $user || $user->isAdmin()) {
            return back()->with('error', 'Không tìm thấy người dùng hoặc không có quyền thực hiện.');
        }

        $this->repo->adminChangeRole($user, $request->role, $admin->id);

        $roleLabel = match ($request->role) {
            'free'    => 'Thính giả miễn phí',
            'premium' => 'Thính giả Premium',
            'artist'  => 'Nghệ sĩ',
            default   => $request->role,
        };

        return back()->with('success', "Đã đổi loại tài khoản <strong>{$user->name}</strong> thành <strong>{$roleLabel}</strong>.");
    }

    /**
     * Xóa (soft-delete) tài khoản người dùng.
     */
    public function destroy(int $id): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        $user  = $this->repo->findById($id);

        if (! $user || $user->isAdmin()) {
            return back()->with('error', 'Không tìm thấy người dùng hoặc không có quyền thực hiện.');
        }

        $name = $user->name;
        $this->repo->adminDelete($user, $admin->id);

        return redirect()->route('admin.users.index')
            ->with('success', "Đã xóa tài khoản <strong>{$name}</strong>.");
    }
}
