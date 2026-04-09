<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArtistPackageController extends Controller
{
    /**
     * Danh sách gói đăng ký Nghệ sĩ + số lượt đăng ký.
     */
    public function index(): View
    {
        $packages = ArtistPackage::with(['features'])
            ->withCount([
                'registrations',
                'registrations as active_registrations_count' => fn ($q) => $q
                    ->where('status', 'approved')
                    ->where('expires_at', '>=', now()),
            ])
            ->orderBy('price')
            ->get();

        return view('admin.artist-packages.index', compact('packages'));
    }

    /**
     * Tạo gói đăng ký Nghệ sĩ mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120', 'unique:artist_packages,name'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'price'         => ['required', 'integer', 'min:0'],
            'is_active'     => ['sometimes', 'boolean'],
            'features_text' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($data): void {
            $package = ArtistPackage::create([
                'name'          => $data['name'],
                'description'   => $data['description'] ?? null,
                'duration_days' => $data['duration_days'],
                'price'         => $data['price'],
                'is_active'     => $data['is_active'],
            ]);

            $this->syncFeatures($package, $data['features_text'] ?? null);
        });

        return redirect()->route('admin.artist-packages.index')
            ->with('success', 'Đã tạo gói đăng ký nghệ sĩ <strong>' . e($data['name']) . '</strong>.');
    }

    /**
     * Cập nhật gói đăng ký Nghệ sĩ.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $package = ArtistPackage::with('features')->findOrFail($id);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120', 'unique:artist_packages,name,' . $package->id],
            'description'   => ['nullable', 'string', 'max:1000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'price'         => ['required', 'integer', 'min:0'],
            'is_active'     => ['sometimes', 'boolean'],
            'features_text' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($package, $data): void {
            $package->update([
                'name'          => $data['name'],
                'description'   => $data['description'] ?? null,
                'duration_days' => $data['duration_days'],
                'price'         => $data['price'],
                'is_active'     => $data['is_active'],
            ]);

            $this->syncFeatures($package, $data['features_text'] ?? null);
        });

        return redirect()->route('admin.artist-packages.index')
            ->with('success', 'Đã cập nhật gói đăng ký nghệ sĩ <strong>' . e($package->name) . '</strong>.');
    }

    /**
     * Bật/tắt trạng thái gói.
     */
    public function toggleActive(int $id): RedirectResponse
    {
        $package = ArtistPackage::findOrFail($id);
        $package->update(['is_active' => ! $package->is_active]);

        $state = $package->fresh()->is_active ? 'kích hoạt' : 'ẩn';

        return back()->with('success', 'Đã ' . $state . ' gói <strong>' . e($package->name) . '</strong>.');
    }

    /**
     * Xóa gói (chỉ cho phép nếu không có đơn đăng ký liên kết).
     */
    public function destroy(int $id): RedirectResponse
    {
        $package = ArtistPackage::withCount('registrations')->findOrFail($id);

        if ($package->registrations_count > 0) {
            return back()->with('error', 'Không thể xóa gói <strong>' . e($package->name) . '</strong> vì đang có ' . $package->registrations_count . ' lượt đăng ký liên kết.');
        }

        $name = $package->name;
        $package->delete();

        return redirect()->route('admin.artist-packages.index')
            ->with('success', 'Đã xóa gói đăng ký nghệ sĩ <strong>' . e($name) . '</strong>.');
    }

    /**
     * Đồng bộ danh sách quyền lợi vào bảng artist_package_features.
     */
    private function syncFeatures(ArtistPackage $package, ?string $featuresText): void
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", (string) $featuresText);
        $rows = explode("\n", $normalized);
        $features = collect($rows)
            ->map(fn ($row) => trim((string) $row))
            ->filter(fn ($row) => $row !== '')
            ->values();

        $package->features()->delete();

        foreach ($features as $idx => $feature) {
            $package->features()->create([
                'feature'    => $feature,
                'sort_order' => $idx,
            ]);
        }
    }
}
