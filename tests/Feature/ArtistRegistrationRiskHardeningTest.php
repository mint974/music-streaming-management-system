<?php

namespace Tests\Feature;

use App\Models\ArtistPackage;
use App\Models\ArtistProfile;
use App\Models\ArtistRegistration;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ArtistProfileCompletionRequired;
use App\Notifications\MembershipExpiringSoonNotification;
use App\Services\VnpayPaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

class ArtistRegistrationRiskHardeningTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithRoles;

    private function createActivePackage(string $name = 'Artist Basic', int $price = 100000): ArtistPackage
    {
        return ArtistPackage::query()->create([
            'name' => $name,
            'description' => 'Basic package',
            'price' => $price,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    private function makeVnpSecureHash(array $payload): string
    {
        /** @var VnpayPaymentService $service */
        $service = app(VnpayPaymentService::class);
        $verification = $service->verifySignature($payload, '');

        return (string) ($verification['expected'] ?? '');
    }

    private function createRegistration(
        User $user,
        ArtistPackage $package,
        string $state,
        array $attributes = [],
        array $payment = []
    ): ArtistRegistration {
        $factory = ArtistRegistration::factory()
            ->for($user)
            ->for($package, 'package');

        $factory = match ($state) {
            ArtistRegistration::STATUS_PENDING_PAYMENT => $factory->pendingPayment($payment),
            ArtistRegistration::STATUS_PENDING_REVIEW => $factory->pendingReview($payment),
            ArtistRegistration::STATUS_REJECTED => $factory->rejected($payment),
            ArtistRegistration::STATUS_APPROVED => $factory->approved($payment),
            default => throw new \InvalidArgumentException("Unsupported registration fixture state: {$state}"),
        };

        return $factory->create($attributes);
    }

    public function test_manual_admin_artist_grant_is_blocked_for_revoked_user(): void
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin_grant@example.com']);
        $user = $this->createUserWithRole('free', ['email' => 'revoked_user@example.com']);

        $package = $this->createActivePackage();
        ArtistProfile::query()->create([
            'user_id' => $user->id,
            'artist_package_id' => $package->id,
            'stage_name' => 'Revoked Artist',
            'status' => ArtistProfile::STATUS_REVOKED,
            'revoked_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.users.changeRole', $user->id), [
            'role' => 'artist',
            'artist_package_id' => $package->id,
        ]);

        $response->assertSessionHas('error');

        $user->refresh();
        $this->assertFalse($user->hasRole('artist'));
    }

    public function test_reject_cooldown_blocks_new_checkout(): void
    {
        $user = $this->createUserWithRole('free', ['email' => 'cooldown_user@example.com']);
        $package = $this->createActivePackage();

        $this->createRegistration($user, $package, ArtistRegistration::STATUS_REJECTED, [
            'submitted_stage_name' => 'Old Artist',
            'reviewed_at' => now()->subHours(2),
            'rejected_at' => now()->subHours(2),
            'rejection_reason' => ArtistRegistration::REJECTION_REASON_OTHER,
            'admin_note' => 'Rejected recently.',
        ]);

        $response = $this->actingAs($user)->post(route('artist-register.checkout', $package->id), [
            'artist_name' => 'Retry Artist',
            'bio' => 'Retry bio',
            'accept_terms' => '1',
        ]);

        $response->assertRedirect(route('artist-register.index'));
        $response->assertSessionHas('error');
    }

    public function test_artist_terms_page_is_available_before_payment(): void
    {
        $response = $this->get(route('artist-register.terms'));

        $response->assertOk();
        $response->assertSee('Điều khoản và dịch vụ dành cho Nghệ sĩ', false);
    }

    public function test_artist_vnpay_callback_rejects_invalid_signature(): void
    {
        $user = $this->createUserWithRole('free');
        $package = $this->createActivePackage();

        $registration = $this->createRegistration(
            $user,
            $package,
            ArtistRegistration::STATUS_PENDING_PAYMENT,
            ['submitted_stage_name' => 'Pending Artist']
        );

        $query = [
            'vnp_TxnRef' => $registration->transaction_code,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => '12345',
            'vnp_PayDate' => '20260412101010',
            'vnp_SecureHash' => 'invalid_hash',
        ];

        $response = $this->get(route('artist-register.vnpay.return', $query));

        $response->assertRedirect(route('artist-register.index'));
        $response->assertSessionHas('error');

        $registration->refresh();
        $this->assertSame(ArtistRegistration::STATUS_PENDING_PAYMENT, $registration->status);
    }

    public function test_artist_vnpay_callback_is_idempotent_for_duplicate_success_callback(): void
    {
        Notification::fake();

        $user = $this->createUserWithRole('free');
        $package = $this->createActivePackage();

        $registration = $this->createRegistration(
            $user,
            $package,
            ArtistRegistration::STATUS_PENDING_PAYMENT,
            ['submitted_stage_name' => 'Pending Artist']
        );

        $payload = [
            'vnp_TxnRef' => $registration->transaction_code,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => '98765',
            'vnp_PayDate' => '20260412121212',
        ];
        $query = $payload;
        $query['vnp_SecureHash'] = $this->makeVnpSecureHash($payload);

        $first = $this->get(route('artist-register.vnpay.return', $query));
        $first->assertRedirect(route('artist.profile.setup'));
        $first->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame(ArtistRegistration::STATUS_PENDING_REVIEW, $registration->status);

        $second = $this->get(route('artist-register.vnpay.return', $query));
        $second->assertRedirect(route('artist-register.index'));
        $second->assertSessionHas('info');

        $registration->refresh();
        $this->assertSame(ArtistRegistration::STATUS_PENDING_REVIEW, $registration->status);
    }

    public function test_successful_artist_payment_redirects_to_profile_setup(): void
    {
        Notification::fake();

        $user = $this->createUserWithRole('free');
        $package = $this->createActivePackage();

        $registration = $this->createRegistration(
            $user,
            $package,
            ArtistRegistration::STATUS_PENDING_PAYMENT,
            ['submitted_stage_name' => 'Setup Artist']
        );

        $payload = [
            'vnp_TxnRef' => $registration->transaction_code,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => '11111',
            'vnp_PayDate' => '20260412181818',
        ];
        $query = $payload;
        $query['vnp_SecureHash'] = $this->makeVnpSecureHash($payload);

        $response = $this->actingAs($user)->get(route('artist-register.vnpay.return', $query));

        $response->assertRedirect(route('artist.profile.setup'));
        $response->assertSessionHas('success');
    }

    public function test_profile_setup_route_is_accessible_for_pending_registration_user(): void
    {
        $user = $this->createUserWithRole('free', ['email' => 'setup_user@example.com']);
        $package = $this->createActivePackage();

        $this->createRegistration(
            $user,
            $package,
            ArtistRegistration::STATUS_PENDING_REVIEW,
            ['submitted_stage_name' => 'Setup Artist']
        );

        $response = $this->actingAs($user)->get(route('artist.profile.setup'));

        $response->assertOk();
        $response->assertSee('app-layout', false);
        $response->assertDontSee('artist-layout', false);
        $response->assertSee('Hồ sơ nghệ sĩ', false);
        $response->assertSee('Vui lòng hoàn thiện hồ sơ nghệ sĩ', false);
    }

    public function test_profile_setup_requires_full_information_before_confirmation(): void
    {
        $user = $this->createUserWithRole('free', ['email' => 'setup_required@example.com']);
        $package = $this->createActivePackage();

        $this->createRegistration(
            $user,
            $package,
            ArtistRegistration::STATUS_PENDING_REVIEW,
            ['submitted_stage_name' => 'Setup Required Artist']
        );

        $response = $this->actingAs($user)->patch(route('artist.profile.setup.update'), [
            'artist_name' => 'Setup Required Artist',
            'bio' => '',
        ]);

        $response->assertSessionHasErrors([
            'bio',
            'avatar',
            'cover_image',
            'social_facebook',
            'social_instagram',
            'social_youtube',
            'social_tiktok',
        ]);
    }

    public function test_artist_register_page_shows_completion_button_when_pending_review_profile_is_incomplete(): void
    {
        $user = $this->createUserWithRole('free', ['email' => 'setup_cta@example.com']);
        $package = $this->createActivePackage();

        $this->createRegistration(
            $user,
            $package,
            ArtistRegistration::STATUS_PENDING_REVIEW,
            ['submitted_stage_name' => 'Setup CTA Artist']
        );

        $response = $this->actingAs($user)->get(route('artist-register.index'));

        $response->assertOk();
        $response->assertSee('Điền thông tin', false);
        $response->assertSee(route('artist.profile.setup'), false);
    }

    public function test_admin_can_request_profile_completion_for_incomplete_pending_review_registration(): void
    {
        Notification::fake();

        $admin = $this->createUserWithRole('admin', ['email' => 'admin_request_completion@example.com']);
        $user = $this->createUserWithRole('free', ['email' => 'user_request_completion@example.com']);
        $package = $this->createActivePackage();

        $registration = $this->createRegistration(
            $user,
            $package,
            ArtistRegistration::STATUS_PENDING_REVIEW,
            ['submitted_stage_name' => 'Request Completion Artist']
        );

        $response = $this->actingAs($admin, 'admin')->post(
            route('admin.artist-registrations.requestProfileCompletion', $registration->id),
            [
                'admin_note' => 'Vui lòng bổ sung đầy đủ các mục hồ sơ còn thiếu trước khi admin xét duyệt tiếp.',
            ]
        );

        $response->assertSessionHas('success');

        Notification::assertSentTo(
            $user,
            ArtistProfileCompletionRequired::class
        );
    }

    public function test_artist_self_cancel_package_expire_and_role_cleanup(): void
    {
        $user = $this->createUserWithRole('artist', ['email' => 'cancel_artist@example.com']);
        $package = $this->createActivePackage('Artist Cancel', 180000);

        $registration = $this->createRegistration($user, $package, ArtistRegistration::STATUS_APPROVED, [
            'submitted_stage_name' => 'Cancel Artist',
            'reviewed_at' => now()->subDays(10),
            'approved_at' => now()->subDays(10),
            'expires_at' => now()->addDays(10),
        ], ['paid_at' => now()->subDays(10), 'amount' => 180000]);

        $response = $this->actingAs($user)->post(route('artist.account.package.cancel', $registration->id));

        $response->assertRedirect(route('artist.account.index'));
        $response->assertSessionHas('success');

        $registration->refresh();
        $user->refresh();

        $this->assertSame(ArtistRegistration::STATUS_EXPIRED, $registration->status);
        $this->assertTrue($user->hasRole('artist'));
        $this->assertTrue($user->isArtistPackageExpired());
    }

    public function test_artist_vnpay_callback_failed_payment_deletes_pending_registration(): void
    {
        $user = $this->createUserWithRole('free');
        $package = $this->createActivePackage();

        $registration = $this->createRegistration(
            $user,
            $package,
            ArtistRegistration::STATUS_PENDING_PAYMENT,
            ['submitted_stage_name' => 'Pending Artist']
        );

        $payload = [
            'vnp_TxnRef' => $registration->transaction_code,
            'vnp_ResponseCode' => '24',
            'vnp_TransactionStatus' => '02',
            'vnp_TransactionNo' => '54321',
            'vnp_PayDate' => '20260412151515',
        ];
        $query = $payload;
        $query['vnp_SecureHash'] = $this->makeVnpSecureHash($payload);

        $response = $this->get(route('artist-register.vnpay.return', $query));

        $response->assertRedirect(route('artist-register.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('artist_registrations', ['id' => $registration->id]);
    }

    public function test_artist_vnpay_callback_handles_missing_registration_transaction_code(): void
    {
        $payload = [
            'vnp_TxnRef' => 'ART_NOT_FOUND_99999',
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => '00000',
            'vnp_PayDate' => '20260412170000',
        ];
        $query = $payload;
        $query['vnp_SecureHash'] = $this->makeVnpSecureHash($payload);

        $response = $this->get(route('artist-register.vnpay.return', $query));

        $response->assertRedirect(route('artist-register.index'));
        $response->assertSessionHas('error');
    }

    public function test_expire_command_expires_artist_registration_and_removes_artist_role(): void
    {
        Notification::fake();

        $user = $this->createUserWithRole('artist', ['email' => 'expire_artist@example.com']);
        $package = $this->createActivePackage('Artist Pro', 200000);

        $registration = $this->createRegistration($user, $package, ArtistRegistration::STATUS_APPROVED, [
            'submitted_stage_name' => 'Expiring Artist',
            'reviewed_at' => now()->subDays(40),
            'approved_at' => now()->subDays(40),
            'expires_at' => now()->subDay(),
        ], ['paid_at' => now()->subDays(40), 'amount' => 200000]);

        Artisan::call('subscription:expire');

        $registration->refresh();
        $user->refresh();

        $this->assertSame(ArtistRegistration::STATUS_EXPIRED, $registration->status);
        $this->assertFalse($user->hasRole('artist'));
    }

    public function test_reminder_command_sends_artist_expiring_tomorrow_notification(): void
    {
        Notification::fake();

        Carbon::setTestNow('2026-04-12 09:00:00');

        $user = $this->createUserWithRole('artist', ['email' => 'reminder_artist@example.com']);
        $package = $this->createActivePackage('Artist Reminder', 150000);

        $this->createRegistration($user, $package, ArtistRegistration::STATUS_APPROVED, [
            'submitted_stage_name' => 'Reminder Artist',
            'reviewed_at' => now()->subDays(29),
            'approved_at' => now()->subDays(29),
            'expires_at' => Carbon::tomorrow(),
        ], ['paid_at' => now()->subDays(29), 'amount' => 150000]);

        Artisan::call('subscription:remind');

        Notification::assertSentTo($user, MembershipExpiringSoonNotification::class);

    }

    public function test_admin_routes_are_blocked_when_logged_in_user_loses_admin_role(): void
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin_lost_role@example.com']);

        // Simulate role revoked after session already exists.
        $adminRoleId = Role::query()->where('slug', 'admin')->value('id');
        if ($adminRoleId) {
            $admin->roles()->detach($adminRoleId);
        }
        $admin->refresh();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('error');
    }
}
