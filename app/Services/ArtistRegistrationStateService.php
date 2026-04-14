<?php

namespace App\Services;

use App\Models\ArtistProfile;
use App\Models\ArtistRegistration;

class ArtistRegistrationStateService
{
    public function moveToPendingReviewAfterPayment(ArtistRegistration $registration): void
    {
        $this->assertCanTransition($registration, ArtistRegistration::STATUS_PENDING_REVIEW);

        $registration->update([
            'status' => ArtistRegistration::STATUS_PENDING_REVIEW,
        ]);

        $registration->user?->artistProfile()?->update([
            'status' => ArtistProfile::STATUS_PENDING_REVIEW,
            'revoked_at' => null,
        ]);
    }

    public function approve(
        ArtistRegistration $registration,
        int $reviewedBy,
        ?string $adminNote,
        int $durationDays
    ): void {
        $this->assertCanTransition($registration, ArtistRegistration::STATUS_APPROVED);

        $registration->update([
            'status' => ArtistRegistration::STATUS_APPROVED,
            'admin_note' => $adminNote,
            'rejection_reason' => null,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'approved_at' => now(),
            'rejected_at' => null,
            'expires_at' => now()->addDays($durationDays),
        ]);

        $registration->user?->artistProfile()?->update([
            'status' => ArtistProfile::STATUS_ACTIVE,
            'start_date' => $registration->approved_at,
            'end_date' => $registration->expires_at,
            'revoked_at' => null,
        ]);
    }

    public function reject(
        ArtistRegistration $registration,
        int $reviewedBy,
        string $adminNote,
        string $reasonCode
    ): void {
        $this->assertCanTransition($registration, ArtistRegistration::STATUS_REJECTED);

        $registration->update([
            'status' => ArtistRegistration::STATUS_REJECTED,
            'admin_note' => $adminNote,
            'rejection_reason' => $reasonCode,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'approved_at' => null,
            'rejected_at' => now(),
        ]);

        $registration->user?->artistProfile()?->update([
            'status' => ArtistProfile::STATUS_INACTIVE,
            'start_date' => null,
            'end_date' => null,
        ]);
    }

    public function expire(ArtistRegistration $registration, ?string $note = null): void
    {
        $this->assertCanTransition($registration, ArtistRegistration::STATUS_EXPIRED);

        $updates = [
            'status' => ArtistRegistration::STATUS_EXPIRED,
            'expires_at' => now(),
        ];

        if ($note !== null) {
            $updates['admin_note'] = $note;
        }

        $registration->update($updates);

        $registration->user?->artistProfile()?->update([
            'status' => ArtistProfile::STATUS_EXPIRED,
            'end_date' => now(),
        ]);
    }

    public function confirmRefund(ArtistRegistration $registration, int $confirmedBy): void
    {
        if (! $registration->isRefundPending()) {
            throw new \Exception('Registration is not in pending refund status.');
        }

        $payment = $registration->payment;

        if (! $payment) {
            throw new \Exception('Payment record not found for this registration.');
        }

        $payment->update([
            'refunded_at' => now(),
        ]);
    }

    public function assertCanTransition(ArtistRegistration $registration, string $targetStatus): void
    {
        if (! $registration->canTransitionTo($targetStatus)) {
            throw new \Exception("Invalid transition: {$registration->status} -> {$targetStatus}");
        }
    }
}
