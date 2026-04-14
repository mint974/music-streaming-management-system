<?php

namespace Tests\Feature;

use App\Models\ArtistPackage;
use App\Models\ArtistRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ArtistRegistrationHttpFlowsTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $role = 'free', array $attributes = []): User
    {
        $defaults = [
            'name' => 'Test User',
            'email' => 'user_' . uniqid() . '@example.com',
            'password' => 'password',
            'status' => 'Đang hoạt động',
            'deleted' => false,
        ];

        $user = User::query()->create(array_merge($defaults, $attributes));
        $user->assignRole($role);

        return $user;
    }

    private function createActivePackage(): ArtistPackage
    {
        return ArtistPackage::query()->create([
            'name' => 'Artist Basic',
            'description' => 'Basic package',
            'price' => 100000,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    private function createPendingReviewRegistration(User $user, ArtistPackage $package): ArtistRegistration
    {
        return ArtistRegistration::query()->create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'artist_name' => 'Artist Test',
            'bio' => 'Artist bio',
            'status' => ArtistRegistration::STATUS_PENDING_REVIEW,
            'amount_paid' => 100000,
            'transaction_code' => 'ART_TXN_' . uniqid(),
            'paid_at' => now(),
        ]);
    }

    public function test_checkout_is_blocked_without_accept_terms(): void
    {
        $user = $this->createUserWithRole('free');
        $package = $this->createActivePackage();

        $response = $this->actingAs($user)->post(route('artist-register.checkout', $package->id), [
            'artist_name' => 'Singer Name',
            'bio' => 'Bio content',
        ]);

        $response->assertSessionHasErrors(['accept_terms']);
    }

    public function test_admin_reject_requires_rejection_reason_code(): void
    {
        Notification::fake();

        $admin = $this->createUserWithRole('admin', ['email' => 'admin1@example.com']);
        $user = $this->createUserWithRole('free', ['email' => 'listener1@example.com']);
        $package = $this->createActivePackage();
        $registration = $this->createPendingReviewRegistration($user, $package);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.artist-registrations.reject', $registration->id), [
                'admin_note' => 'Ly do tu choi hop le tren 10 ky tu',
            ]);

        $response->assertSessionHasErrors(['rejection_reason_code']);
    }

    public function test_admin_reject_requires_admin_note_min_length(): void
    {
        Notification::fake();

        $admin = $this->createUserWithRole('admin', ['email' => 'admin2@example.com']);
        $user = $this->createUserWithRole('free', ['email' => 'listener2@example.com']);
        $package = $this->createActivePackage();
        $registration = $this->createPendingReviewRegistration($user, $package);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.artist-registrations.reject', $registration->id), [
                'rejection_reason_code' => ArtistRegistration::REJECTION_REASON_OTHER,
                'admin_note' => 'ngan',
            ]);

        $response->assertSessionHasErrors(['admin_note']);
    }

    public function test_admin_reject_saves_rejection_reason_code(): void
    {
        Notification::fake();

        $admin = $this->createUserWithRole('admin', ['email' => 'admin3@example.com']);
        $user = $this->createUserWithRole('free', ['email' => 'listener3@example.com']);
        $package = $this->createActivePackage();
        $registration = $this->createPendingReviewRegistration($user, $package);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.artist-registrations.reject', $registration->id), [
                'rejection_reason_code' => ArtistRegistration::REJECTION_REASON_COPYRIGHT_RISK,
                'admin_note' => 'Noi dung co dau hieu rui ro ban quyen, can bo sung chung minh quyen so huu.',
            ])
            ->assertSessionHasNoErrors();

        $registration->refresh();

        $this->assertSame(ArtistRegistration::STATUS_REJECTED, $registration->status);
        $this->assertSame(ArtistRegistration::REJECTION_REASON_COPYRIGHT_RISK, $registration->rejection_reason_code);
    }

    public function test_user_sees_rejection_guidance_after_rejection(): void
    {
        $user = $this->createUserWithRole('free', ['email' => 'listener4@example.com']);
        $package = $this->createActivePackage();

        ArtistRegistration::query()->create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'artist_name' => 'Rejected Artist',
            'bio' => 'Old bio',
            'status' => ArtistRegistration::STATUS_REJECTED,
            'amount_paid' => 100000,
            'transaction_code' => 'ART_TXN_REJECT_' . uniqid(),
            'paid_at' => now()->subDay(),
            'reviewed_at' => now(),
            'rejection_reason_code' => ArtistRegistration::REJECTION_REASON_PROFILE_INCOMPLETE,
            'admin_note' => 'Ho so thieu thong tin can thiet.',
        ]);

        $response = $this->actingAs($user)->get(route('artist-register.index'));

        $response->assertOk();
        $response->assertSee('Nhóm lý do:', false);
        $response->assertSee('Hồ sơ nghệ sĩ chưa đầy đủ', false);
        $response->assertSee('Gợi ý tiếp theo:', false);
    }

    public function test_pending_review_can_be_approved(): void
    {
        Notification::fake();

        $admin = $this->createUserWithRole('admin', ['email' => 'admin4@example.com']);
        $user = $this->createUserWithRole('free', ['email' => 'listener5@example.com']);
        $user->update([
            'artist_name' => 'Artist Test',
            'bio' => 'Artist bio đầy đủ để xét duyệt.',
            'avatar' => '/storage/avatars/test-avatar.jpg',
            'cover_image' => '/storage/covers/test-cover.jpg',
        ]);
        foreach (['facebook', 'instagram', 'youtube', 'tiktok'] as $platform) {
            DB::table('user_social_links')->insert([
                'user_id' => $user->id,
                'platform' => $platform,
                'url' => 'https://example.com/' . $platform,
            ]);
        }

        $package = $this->createActivePackage();
        $registration = $this->createPendingReviewRegistration($user, $package);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.artist-registrations.approve', $registration->id), [
                'admin_note' => 'Ho so hop le va duoc phe duyet.',
            ])
            ->assertSessionHasNoErrors();

        $registration->refresh();
        $user->refresh();

        $this->assertSame(ArtistRegistration::STATUS_APPROVED, $registration->status);
        $this->assertTrue($user->hasRole('artist'));
    }

    public function test_pending_review_can_be_rejected(): void
    {
        Notification::fake();

        $admin = $this->createUserWithRole('admin', ['email' => 'admin5@example.com']);
        $user = $this->createUserWithRole('free', ['email' => 'listener6@example.com']);
        $package = $this->createActivePackage();
        $registration = $this->createPendingReviewRegistration($user, $package);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.artist-registrations.reject', $registration->id), [
                'rejection_reason_code' => ArtistRegistration::REJECTION_REASON_OTHER,
                'admin_note' => 'Thong tin ho so hien tai chua dat tieu chuan xet duyet.',
            ])
            ->assertSessionHasNoErrors();

        $registration->refresh();

        $this->assertSame(ArtistRegistration::STATUS_REJECTED, $registration->status);
    }

    public function test_admin_can_see_profile_preview_data_before_approval(): void
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin_preview@example.com']);
        $user = $this->createUserWithRole('free', ['email' => 'listener_preview@example.com']);

        $user->update([
            'artist_name' => 'Updated Preview Artist',
            'bio' => 'Bio duoc cap nhat boi user truoc khi admin duyet.',
            'avatar' => '/storage/avatars/preview-avatar.jpg',
            'cover_image' => '/storage/covers/preview-cover.jpg',
        ]);

        DB::table('user_social_links')->insert([
            'user_id' => $user->id,
            'platform' => 'facebook',
            'url' => 'https://example.com/facebook-preview',
        ]);

        $package = $this->createActivePackage();
        $this->createPendingReviewRegistration($user, $package);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.artist-registrations.index', ['tab' => 'pending_review']));

        $response->assertOk();
        $response->assertSee('Xem trang cá nhân', false);
        $response->assertSee('Updated Preview Artist', false);
        $response->assertSee('Bio duoc cap nhat boi user truoc khi admin duyet.', false);
    }

    public function test_wrong_state_approve_and_reject_are_blocked(): void
    {
        Notification::fake();

        $admin = $this->createUserWithRole('admin', ['email' => 'admin6@example.com']);
        $user = $this->createUserWithRole('artist', ['email' => 'listener7@example.com']);
        $package = $this->createActivePackage();

        $registration = ArtistRegistration::query()->create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'artist_name' => 'Approved Artist',
            'bio' => 'Approved bio',
            'status' => ArtistRegistration::STATUS_APPROVED,
            'amount_paid' => 100000,
            'transaction_code' => 'ART_TXN_APPROVED_' . uniqid(),
            'paid_at' => now()->subDays(5),
            'reviewed_at' => now()->subDays(5),
            'expires_at' => now()->addDays(25),
        ]);

        $approveResponse = $this->actingAs($admin, 'admin')
            ->post(route('admin.artist-registrations.approve', $registration->id), [
                'admin_note' => 'Thu lai phe duyet',
            ]);

        $rejectResponse = $this->actingAs($admin, 'admin')
            ->post(route('admin.artist-registrations.reject', $registration->id), [
                'rejection_reason_code' => ArtistRegistration::REJECTION_REASON_OTHER,
                'admin_note' => 'Thu lai tu choi voi trang thai sai.',
            ]);

        $approveResponse->assertSessionHas('error');
        $rejectResponse->assertSessionHas('error');

        $registration->refresh();
        $this->assertSame(ArtistRegistration::STATUS_APPROVED, $registration->status);
    }

    public function test_refund_confirmation_only_works_when_refund_status_is_pending(): void
    {
        Notification::fake();

        $admin = $this->createUserWithRole('admin', ['email' => 'admin7@example.com']);
        $user = $this->createUserWithRole('free', ['email' => 'listener8@example.com']);
        $package = $this->createActivePackage();

        $registration = ArtistRegistration::query()->create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'artist_name' => 'Refund Artist',
            'bio' => 'Refund bio',
            'status' => ArtistRegistration::STATUS_REJECTED,
            'amount_paid' => 100000,
            'transaction_code' => 'ART_TXN_REFUND_' . uniqid(),
            'paid_at' => now()->subDays(2),
            'reviewed_at' => now()->subDays(2),
            'refund_amount' => 100000,
            'refund_status' => 'completed',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.artist-registrations.confirmRefund', $registration->id));

        $response->assertSessionHas('error');

        $registration->refresh();
        $this->assertSame('completed', $registration->refund_status);
    }
}
