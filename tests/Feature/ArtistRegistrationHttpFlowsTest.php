<?php

namespace Tests\Feature;

use App\Models\ArtistPackage;
use App\Models\ArtistProfile;
use App\Models\ArtistRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

class ArtistRegistrationHttpFlowsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithRoles;

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
        return ArtistRegistration::factory()
            ->for($user)
            ->for($package, 'package')
            ->pendingReview()
            ->create(['submitted_stage_name' => 'Artist Test']);
    }

    private function createCompleteArtistProfile(User $user, ArtistPackage $package, string $stageName): ArtistProfile
    {
        $profile = ArtistProfile::query()->create([
            'user_id' => $user->id,
            'artist_package_id' => $package->id,
            'stage_name' => $stageName,
            'bio' => 'Artist bio đầy đủ để xét duyệt.',
            'avatar' => '/storage/avatars/test-avatar.jpg',
            'cover_image' => '/storage/covers/test-cover.jpg',
            'status' => ArtistProfile::STATUS_PENDING_REVIEW,
        ]);

        foreach (['facebook', 'instagram', 'youtube', 'tiktok'] as $platform) {
            $profile->socialLinks()->create([
                'platform' => $platform,
                'url' => 'https://example.com/'.$platform,
            ]);
        }

        return $profile;
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

    public function test_admin_reject_saves_rejection_reason(): void
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
        $this->assertSame(ArtistRegistration::REJECTION_REASON_COPYRIGHT_RISK, $registration->rejection_reason);
        $this->assertSame($admin->id, $registration->reviewed_by);
        $this->assertSame(
            'Noi dung co dau hieu rui ro ban quyen, can bo sung chung minh quyen so huu.',
            $registration->admin_note
        );
    }

    public function test_user_sees_rejection_guidance_after_rejection(): void
    {
        $user = $this->createUserWithRole('free', ['email' => 'listener4@example.com']);
        $package = $this->createActivePackage();

        ArtistRegistration::factory()
            ->for($user)
            ->for($package, 'package')
            ->rejected()
            ->create([
            'submitted_stage_name' => 'Rejected Artist',
            'reviewed_at' => now(),
            'rejection_reason' => ArtistRegistration::REJECTION_REASON_PROFILE_INCOMPLETE,
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
        $package = $this->createActivePackage();
        $this->createCompleteArtistProfile($user, $package, 'Artist Test');
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

        $package = $this->createActivePackage();
        $profile = $this->createCompleteArtistProfile($user, $package, 'Updated Preview Artist');
        $profile->update([
            'bio' => 'Bio duoc cap nhat boi user truoc khi admin duyet.',
            'avatar' => '/storage/avatars/preview-avatar.jpg',
            'cover_image' => '/storage/covers/preview-cover.jpg',
        ]);
        $profile->socialLinks()->where('platform', 'facebook')->update([
            'platform' => 'facebook',
            'url' => 'https://example.com/facebook-preview',
        ]);

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

        $registration = ArtistRegistration::factory()
            ->for($user)
            ->for($package, 'package')
            ->approved(['paid_at' => now()->subDays(5)])
            ->create([
            'submitted_stage_name' => 'Approved Artist',
            'reviewed_at' => now()->subDays(5),
            'approved_at' => now()->subDays(5),
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

        $registration = ArtistRegistration::factory()
            ->for($user)
            ->for($package, 'package')
            ->rejected([
                'paid_at' => now()->subDays(2),
                'refund_amount' => 100000,
                'refunded_at' => now()->subDay(),
            ])
            ->create([
            'submitted_stage_name' => 'Refund Artist',
            'reviewed_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.artist-registrations.confirmRefund', $registration->id));

        $response->assertSessionHas('error');

        $registration->refresh();
        $this->assertSame('completed', $registration->refund_status);
    }
}
