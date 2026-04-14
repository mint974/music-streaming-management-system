<?php

namespace Tests\Unit;

use App\Models\ArtistRegistration;
use App\Services\ArtistRegistrationStateService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArtistRegistrationStateServiceTest extends TestCase
{
    private function makeInMemoryRegistration(array $attributes = []): ArtistRegistration
    {
        return new class($attributes) extends ArtistRegistration {
            public bool $saved = false;

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);
            }

            public function update(array $attributes = [], array $options = []): bool
            {
                $this->fill($attributes);
                $this->saved = true;
                return true;
            }
        };
    }

    #[Test]
    public function it_moves_pending_payment_to_pending_review_after_payment(): void
    {
        Carbon::setTestNow('2026-04-12 09:00:00');

        $service = new ArtistRegistrationStateService();
        $registration = $this->makeInMemoryRegistration([
            'status' => ArtistRegistration::STATUS_PENDING_PAYMENT,
        ]);

        $service->moveToPendingReviewAfterPayment($registration, [
            'vnp_TransactionNo' => 'TXN-001',
            'vnp_PayDate' => '20260412090000',
        ]);

        $this->assertSame(ArtistRegistration::STATUS_PENDING_REVIEW, $registration->status);
        $this->assertSame('TXN-001', $registration->vnp_transaction_no);
        $this->assertSame('20260412090000', $registration->vnp_pay_date);
        $this->assertNotNull($registration->paid_at);
        $this->assertTrue($registration->saved);

        Carbon::setTestNow();
    }

    #[Test]
    public function it_rejects_with_reason_code_and_note(): void
    {
        Carbon::setTestNow('2026-04-12 10:00:00');

        $service = new ArtistRegistrationStateService();
        $registration = $this->makeInMemoryRegistration([
            'status' => ArtistRegistration::STATUS_PENDING_REVIEW,
        ]);

        $service->reject(
            $registration,
            99,
            'Thiếu thông tin giới thiệu nghệ sĩ.',
            ArtistRegistration::REJECTION_REASON_PROFILE_INCOMPLETE,
            50000
        );

        $this->assertSame(ArtistRegistration::STATUS_REJECTED, $registration->status);
        $this->assertSame(99, $registration->reviewed_by);
        $this->assertSame(ArtistRegistration::REJECTION_REASON_PROFILE_INCOMPLETE, $registration->rejection_reason_code);
        $this->assertSame('Thiếu thông tin giới thiệu nghệ sĩ.', $registration->admin_note);
        $this->assertSame(50000, $registration->refund_amount);
        $this->assertSame('pending', $registration->refund_status);
        $this->assertNotNull($registration->reviewed_at);
        $this->assertTrue($registration->saved);

        Carbon::setTestNow();
    }

    #[Test]
    public function it_blocks_invalid_state_transition(): void
    {
        $this->expectException('DomainException');

        $service = new ArtistRegistrationStateService();
        $registration = $this->makeInMemoryRegistration([
            'status' => ArtistRegistration::STATUS_APPROVED,
        ]);

        $service->moveToPendingReviewAfterPayment($registration, []);
    }

    #[Test]
    public function it_exposes_reason_labels_guidance_and_transition_rules(): void
    {
        $registration = new ArtistRegistration([
            'status' => ArtistRegistration::STATUS_REJECTED,
            'rejection_reason_code' => ArtistRegistration::REJECTION_REASON_POLICY_VIOLATION,
        ]);

        $this->assertSame('Nội dung hồ sơ chưa phù hợp chính sách', $registration->rejectionReasonLabel());
        $this->assertNotEmpty($registration->rejectionNextStepGuidance());

        $this->assertFalse($registration->canTransitionTo(ArtistRegistration::STATUS_EXPIRED));

        $registration->status = ArtistRegistration::STATUS_PENDING_REVIEW;
        $this->assertTrue($registration->canTransitionTo(ArtistRegistration::STATUS_APPROVED));
        $this->assertTrue($registration->canTransitionTo(ArtistRegistration::STATUS_REJECTED));

        $registration->status = ArtistRegistration::STATUS_REJECTED;
        $this->assertFalse($registration->canTransitionTo(ArtistRegistration::STATUS_PENDING_REVIEW));
    }
}
