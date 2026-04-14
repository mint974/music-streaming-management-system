<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'type', 'status']);
        
        $banners = Banner::with('creator')
                    ->when($filters['search'] ?? null, function($q, $search) {
                        $q->where('title', 'like', "%{$search}%");
                    })
                    ->when($filters['type'] ?? null, fn($q, $v) => $q->where('type', $v))
                    ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
                    ->orderBy('order_index', 'asc')
                    ->latest()
                    ->paginate(15)
                    ->withQueryString();

        $stats = [
            'total' => Banner::count(),
            'active_hero' => Banner::where('type', 'hero')->where('status', 'active')->count(),
            'active_ad' => Banner::where('type', 'ad')->where('status', 'active')->count(),
            'total_clicks' => Banner::sum('clicks'),
        ];

        return view('admin.banners.index', compact('banners', 'filters', 'stats'));
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:hero,ad',
            'target_url' => 'nullable|url|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'audio_file' => 'required_if:type,ad|nullable|file|mimetypes:audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/webm|mimes:mp3,wav,ogg,m4a,webm|max:10240',
            'order_index' => 'required|integer|min:0',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = '/storage/' . $request->file('image')->store('banners', 'public');
        }

        if ($request->hasFile('audio_file')) {
            $data['audio_path'] = '/storage/' . $request->file('audio_file')->store('banners/audio', 'public');
        }

        if (($data['type'] ?? null) !== 'ad') {
            $data['audio_path'] = null;
        }

        $adminId = Auth::guard('admin')->id() ?? Auth::id();
        $data['created_by'] = $adminId !== null ? (int) $adminId : null;

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('success', 'Thêm banner/quảng cáo mới thành công!');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:hero,ad',
            'target_url' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'audio_file' => 'required_if:type,ad|nullable|file|mimetypes:audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/webm|mimes:mp3,wav,ogg,m4a,webm|max:10240',
            'order_index' => 'required|integer|min:0',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            // Delete old
            if ($banner->image_path) {
                $oldPath = str_replace('/storage/', '', $banner->image_path);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $data['image_path'] = '/storage/' . $request->file('image')->store('banners', 'public');
        }

        if ($request->hasFile('audio_file')) {
            if ($banner->audio_path) {
                $oldAudioPath = str_replace('/storage/', '', $banner->audio_path);
                if (Storage::disk('public')->exists($oldAudioPath)) {
                    Storage::disk('public')->delete($oldAudioPath);
                }
            }

            $data['audio_path'] = '/storage/' . $request->file('audio_file')->store('banners/audio', 'public');
        } elseif (($data['type'] ?? $banner->type) !== 'ad' && $banner->audio_path) {
            $oldAudioPath = str_replace('/storage/', '', $banner->audio_path);
            if (Storage::disk('public')->exists($oldAudioPath)) {
                Storage::disk('public')->delete($oldAudioPath);
            }
            $data['audio_path'] = null;
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Cập nhật banner thành công!');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image_path) {
            $oldPath = str_replace('/storage/', '', $banner->image_path);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        if ($banner->audio_path) {
            $oldAudioPath = str_replace('/storage/', '', $banner->audio_path);
            if (Storage::disk('public')->exists($oldAudioPath)) {
                Storage::disk('public')->delete($oldAudioPath);
            }
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Đã xóa banner vĩnh viễn.');
    }

    public function toggleStatus(Banner $banner)
    {
        $newStatus = $banner->status === 'active' ? 'inactive' : 'active';
        $banner->update(['status' => $newStatus]);
        return back()->with('success', "Đã thay đổi trạng thái banner <strong>{$banner->title}</strong> thành <strong>{$newStatus}</strong>.");
    }
}
