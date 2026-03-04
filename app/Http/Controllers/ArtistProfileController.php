<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArtistProfileController extends Controller
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Hiển thị trang chỉnh sửa hồ sơ nghệ sĩ.
     * GET /artist/profile
     */
    public function edit(Request $request): View
    {
        return view('artist.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Cập nhật hồ sơ nghệ sĩ.
     * PATCH /artist/profile
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'artist_name'  => ['required', 'string', 'max:100', 'min:2'],
            'bio'          => ['nullable', 'string', 'max:1000'],
            'avatar'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:3072'],
            'cover_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            // Mạng xã hội
            'social_facebook'  => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_youtube'   => ['nullable', 'url', 'max:255'],
            'social_tiktok'    => ['nullable', 'url', 'max:255'],
            'social_spotify'   => ['nullable', 'url', 'max:255'],
            'social_website'   => ['nullable', 'url', 'max:255'],
        ], [
            'artist_name.required' => 'Vui lòng nhập tên nghệ danh.',
            'artist_name.min'      => 'Tên nghệ danh phải có ít nhất 2 ký tự.',
            'artist_name.max'      => 'Tên nghệ danh không được vượt quá 100 ký tự.',
            'bio.max'              => 'Tiểu sử không được vượt quá 1000 ký tự.',
            'avatar.image'         => 'Ảnh đại diện phải là hình ảnh.',
            'avatar.mimes'         => 'Ảnh đại diện chỉ hỗ trợ JPG, PNG, WEBP, GIF.',
            'avatar.max'           => 'Ảnh đại diện không được vượt quá 3MB.',
            'cover_image.image'    => 'Ảnh bìa phải là hình ảnh.',
            'cover_image.mimes'    => 'Ảnh bìa chỉ hỗ trợ JPG, PNG, WEBP.',
            'cover_image.max'      => 'Ảnh bìa không được vượt quá 5MB.',
            'social_facebook.url'  => 'Link Facebook không hợp lệ.',
            'social_instagram.url' => 'Link Instagram không hợp lệ.',
            'social_youtube.url'   => 'Link YouTube không hợp lệ.',
            'social_tiktok.url'    => 'Link TikTok không hợp lệ.',
            'social_spotify.url'   => 'Link Spotify không hợp lệ.',
            'social_website.url'   => 'Địa chỉ website không hợp lệ.',
        ]);

        $updateData = [
            'artist_name' => $validated['artist_name'],
            'bio'         => $validated['bio'] ?? null,
            'social_links' => [
                'facebook'  => $validated['social_facebook']  ?? null,
                'instagram' => $validated['social_instagram'] ?? null,
                'youtube'   => $validated['social_youtube']   ?? null,
                'tiktok'    => $validated['social_tiktok']    ?? null,
                'spotify'   => $validated['social_spotify']   ?? null,
                'website'   => $validated['social_website']   ?? null,
            ],
        ];

        // Xử lý upload ảnh đại diện
        if ($request->hasFile('avatar')) {
            if ($user->avatar && str_starts_with($user->avatar, '/storage/avatars/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = Storage::url($path);
        }

        // Xử lý upload ảnh bìa
        if ($request->hasFile('cover_image')) {
            if ($user->cover_image && str_starts_with($user->cover_image, '/storage/covers/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->cover_image));
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $updateData['cover_image'] = Storage::url($path);
        }

        // Xóa ảnh bìa
        if ($request->boolean('remove_cover_image') && !$request->hasFile('cover_image')) {
            if ($user->cover_image && str_starts_with($user->cover_image, '/storage/covers/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->cover_image));
            }
            $updateData['cover_image'] = null;
        }

        $this->userRepository->updateArtistProfile($user, $updateData);

        return redirect()->route('artist.profile.edit')
            ->with('success', 'Hồ sơ nghệ sĩ đã được cập nhật thành công.');
    }
}
