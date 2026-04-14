<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $profileCompletionMode = $request->routeIs('artist.profile.setup');
        $user = $request->user()->loadMissing('artistProfile', 'socialLinks');

        return view('artist.profile.edit', [
            'user' => $user,
            'profileCompletionMode' => $profileCompletionMode,
            'profileUpdateRoute' => $profileCompletionMode ? 'artist.profile.setup.update' : 'artist.profile.update',
            'backRoute' => $profileCompletionMode ? 'artist-register.index' : 'artist.dashboard',
        ]);
    }

    /**
     * Cập nhật hồ sơ nghệ sĩ.
     * PATCH /artist/profile
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user()->loadMissing('artistProfile');
        $profileCompletionMode = $request->routeIs('artist.profile.setup.update');

        $currentAvatar = trim((string) ($user->artistProfile?->avatar
            ?: (($user->avatar && $user->avatar !== '/storage/avt.jpg') ? $user->avatar : '')));
        $currentCoverImage = trim((string) ($user->artistProfile?->cover_image ?? ''));

        $validationRules = [
            'artist_name' => ['required', 'string', 'max:100', 'min:2'],
            'bio' => $profileCompletionMode ? ['required', 'string', 'max:1000'] : ['nullable', 'string', 'max:1000'],
            'avatar' => ($profileCompletionMode && $currentAvatar === '')
                ? ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:3072']
                : ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:3072'],
            'cover_image' => ($profileCompletionMode && $currentCoverImage === '')
                ? ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']
                : ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'social_facebook' => $profileCompletionMode ? ['required', 'url', 'max:255'] : ['nullable', 'url', 'max:255'],
            'social_instagram' => $profileCompletionMode ? ['required', 'url', 'max:255'] : ['nullable', 'url', 'max:255'],
            'social_youtube' => $profileCompletionMode ? ['required', 'url', 'max:255'] : ['nullable', 'url', 'max:255'],
            'social_tiktok' => $profileCompletionMode ? ['required', 'url', 'max:255'] : ['nullable', 'url', 'max:255'],
        ];

        $validated = $request->validate($validationRules, [
            'artist_name.required' => 'Vui lòng nhập tên nghệ danh.',
            'artist_name.min'      => 'Tên nghệ danh phải có ít nhất 2 ký tự.',
            'artist_name.max'      => 'Tên nghệ danh không được vượt quá 100 ký tự.',
            'bio.required'         => 'Vui lòng nhập tiểu sử nghệ sĩ.',
            'bio.max'              => 'Tiểu sử không được vượt quá 1000 ký tự.',
            'avatar.required'      => 'Vui lòng tải lên ảnh đại diện.',
            'avatar.image'         => 'Ảnh đại diện phải là hình ảnh.',
            'avatar.mimes'         => 'Ảnh đại diện chỉ hỗ trợ JPG, PNG, WEBP, GIF.',
            'avatar.max'           => 'Ảnh đại diện không được vượt quá 3MB.',
            'cover_image.required' => 'Vui lòng tải lên ảnh bìa.',
            'cover_image.image'    => 'Ảnh bìa phải là hình ảnh.',
            'cover_image.mimes'    => 'Ảnh bìa chỉ hỗ trợ JPG, PNG, WEBP.',
            'cover_image.max'      => 'Ảnh bìa không được vượt quá 5MB.',
            'social_facebook.required'  => 'Vui lòng nhập link Facebook.',
            'social_facebook.url'  => 'Link Facebook không hợp lệ.',
            'social_instagram.required' => 'Vui lòng nhập link Instagram.',
            'social_instagram.url' => 'Link Instagram không hợp lệ.',
            'social_youtube.required'   => 'Vui lòng nhập link YouTube.',
            'social_youtube.url'   => 'Link YouTube không hợp lệ.',
            'social_tiktok.required'    => 'Vui lòng nhập link TikTok.',
            'social_tiktok.url'    => 'Link TikTok không hợp lệ.',
        ]);

        $updateData = [
            'stage_name' => $validated['artist_name'],
            'bio'        => $validated['bio'] ?? null,
        ];

        // Xử lý upload ảnh đại diện
        if ($request->hasFile('avatar')) {
            if ($user->artistProfile?->avatar && str_starts_with($user->artistProfile->avatar, '/storage/avatars/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->artistProfile->avatar));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = Storage::url($path);
        }

        // Xử lý upload ảnh bìa
        if ($request->hasFile('cover_image')) {
            if ($user->artistProfile?->cover_image && str_starts_with($user->artistProfile->cover_image, '/storage/covers/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->artistProfile->cover_image));
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $updateData['cover_image'] = Storage::url($path);
        }

        // Xóa ảnh bìa
        if ($request->boolean('remove_cover_image') && !$request->hasFile('cover_image')) {
            if ($user->artistProfile?->cover_image && str_starts_with($user->artistProfile->cover_image, '/storage/covers/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->artistProfile->cover_image));
            }
            $updateData['cover_image'] = null;
        }

        $this->userRepository->updateArtistProfile($user, $updateData);

        $user->refresh()->loadMissing('artistProfile');
        $artistProfileId = (int) ($user->artistProfile?->id ?? 0);

        if ($artistProfileId <= 0) {
            return back()->with('error', 'Không tìm thấy hồ sơ nghệ sĩ để cập nhật liên kết mạng xã hội.');
        }

        // Cập nhật social links vào bảng chuẩn hóa (1NF)
        $platforms = ['facebook', 'instagram', 'youtube', 'tiktok'];
        DB::transaction(function () use ($user, $validated, $platforms, $artistProfileId) {
            foreach ($platforms as $platform) {
                $url = trim((string) ($validated['social_' . $platform] ?? ''));
                if ($url !== '') {
                    DB::table('user_social_links')->updateOrInsert(
                        ['artist_profile_id' => $artistProfileId, 'platform' => $platform],
                        ['url' => $url]
                    );
                } else {
                    DB::table('user_social_links')
                        ->where('artist_profile_id', $artistProfileId)
                        ->where('platform', $platform)
                        ->delete();
                }
            }
        });

        $profileCompletionMode = $request->routeIs('artist.profile.setup.update');

        return redirect()->route($profileCompletionMode ? 'artist.profile.setup' : 'artist.profile.edit')
            ->with('success', $profileCompletionMode
                ? 'Hồ sơ nghệ sĩ đã được hoàn thiện. Đơn của bạn sẽ được admin xem xét.'
                : 'Hồ sơ nghệ sĩ đã được cập nhật thành công.');
    }

}
