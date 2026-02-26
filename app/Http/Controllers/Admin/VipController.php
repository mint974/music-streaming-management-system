<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VipController extends Controller
{
    /**
     * Danh sách tất cả gói VIP + số subscriber.
     */
    public function index(): View
    {
        $vips = Vip::withCount([
            'subscriptions',
            'subscriptions as active_subscriptions_count' => fn ($q) => $q->where('status', 'active'),
        ])->orderBy('price')->get();

        return view('admin.vips.index', compact('vips'));
    }

    /**
     * Lưu gói VIP mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id'            => ['required', 'string', 'max:50', 'unique:vips,id', 'regex:/^[a-z0-9_\-]+$/'],
            'title'         => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'price'         => ['required', 'integer', 'min:0'],
            'is_active'     => ['sometimes', 'boolean'],
        ], [
            'id.regex'    => 'ID chỉ được chứa chữ thường, số, dấu gạch dưới hoặc gạch ngang.',
            'id.unique'   => 'ID gói này đã tồn tại.',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Vip::create($data);

        return redirect()->route('admin.vips.index')
                         ->with('success', "Đã tạo gói <strong>{$data['title']}</strong>.");
    }

    /**
     * Cập nhật thông tin gói VIP.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $vip = Vip::findOrFail($id);

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'price'         => ['required', 'integer', 'min:0'],
            'is_active'     => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $vip->update($data);

        return redirect()->route('admin.vips.index')
                         ->with('success', "Đã cập nhật gói <strong>{$vip->title}</strong>.");
    }

    /**
     * Bật/tắt trạng thái gói VIP.
     */
    public function toggleActive(string $id): RedirectResponse
    {
        $vip = Vip::findOrFail($id);
        $vip->update(['is_active' => ! $vip->is_active]);

        $state = $vip->fresh()->is_active ? 'kích hoạt' : 'ẩn';
        return back()->with('success', "Đã {$state} gói <strong>{$vip->title}</strong>.");
    }

    /**
     * Xóa gói VIP (chỉ cho phép nếu không có subscription nào).
     */
    public function destroy(string $id): RedirectResponse
    {
        $vip = Vip::withCount('subscriptions')->findOrFail($id);

        if ($vip->subscriptions_count > 0) {
            return back()->with('error', "Không thể xóa gói <strong>{$vip->title}</strong> vì đang có {$vip->subscriptions_count} lượt đăng ký liên kết.");
        }

        $name = $vip->title;
        $vip->delete();

        return redirect()->route('admin.vips.index')
                         ->with('success', "Đã xóa gói <strong>{$name}</strong>.");
    }
}
