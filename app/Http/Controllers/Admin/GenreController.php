<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GenreController extends Controller
{
    /**
     * Danh sách thể loại + tìm kiếm.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search', '');

        $genres = Genre::when($search, fn ($q) =>
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%")
                  )
                  ->ordered()
                  ->get();

        $stats = [
            'total'    => Genre::count(),
            'active'   => Genre::active()->count(),
            'inactive' => Genre::where('is_active', false)->count(),
        ];

        return view('admin.genres.index', compact('genres', 'stats', 'search'));
    }

    /**
     * Tạo thể loại mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:genres,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon'        => ['nullable', 'string', 'max:100'],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['slug']       = Genre::uniqueSlug($data['name']);
        $data['sort_order'] = Genre::max('sort_order') + 1;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')
                ->store('genres', 'public');
        }

        Genre::create($data);

        return redirect()->route('admin.genres.index')
            ->with('success', "Đã thêm thể loại <strong>{$data['name']}</strong> thành công.");
    }

    /**
     * Cập nhật thể loại.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $genre = Genre::findOrFail($id);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', "unique:genres,name,{$id}"],
            'description' => ['nullable', 'string', 'max:500'],
            'icon'        => ['nullable', 'string', 'max:100'],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        // Cập nhật slug chỉ khi name thay đổi
        if ($data['name'] !== $genre->name) {
            $data['slug'] = Genre::uniqueSlug($data['name'], $id);
        }

        // Xử lý ảnh mới — xóa ảnh cũ nếu có
        if ($request->hasFile('cover_image')) {
            if ($genre->cover_image) {
                Storage::disk('public')->delete($genre->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')
                ->store('genres', 'public');
        } elseif ($request->boolean('remove_cover') && $genre->cover_image) {
            Storage::disk('public')->delete($genre->cover_image);
            $data['cover_image'] = null;
        }

        $genre->update($data);

        return redirect()->route('admin.genres.index')
            ->with('success', "Đã cập nhật thể loại <strong>{$genre->name}</strong>.");
    }

    /**
     * Ẩn hoặc hiện thể loại.
     */
    public function toggleActive(int $id): RedirectResponse
    {
        $genre = Genre::findOrFail($id);
        $genre->update(['is_active' => ! $genre->is_active]);

        $label = $genre->is_active ? 'kích hoạt' : 'ẩn';
        return back()->with('success', "Đã {$label} thể loại <strong>{$genre->name}</strong>.");
    }

    /**
     * Xóa thể loại (và ảnh bìa nếu có).
     */
    public function destroy(int $id): RedirectResponse
    {
        $genre = Genre::findOrFail($id);
        $name  = $genre->name;

        if ($genre->cover_image) {
            Storage::disk('public')->delete($genre->cover_image);
        }

        $genre->delete();

        return redirect()->route('admin.genres.index')
            ->with('success', "Đã xóa thể loại <strong>{$name}</strong>.");
    }

    /**
     * Lưu thứ tự sắp xếp sau khi kéo thả (nhận JSON).
     * POST /admin/genres/reorder
     * Body: { "order": [3, 1, 5, 2, ...] }   (mảng id theo thứ tự mới)
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach ($request->order as $position => $genreId) {
            Genre::where('id', $genreId)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}
