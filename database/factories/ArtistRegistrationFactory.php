<?php

namespace Database\Factories;

use App\Models\ArtistPackage;
use App\Models\ArtistRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtistRegistration>
 */
class ArtistRegistrationFactory extends Factory
{
    protected $model = ArtistRegistration::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'package_id' => fn (): int => ArtistPackage::query()->create([
                'name' => 'Artist Package '.$this->faker->unique()->numerify('###'),
                'description' => 'Package created for automated testing.',
                'price' => 100000,
                'duration_days' => 30,
                'is_active' => true,
            ])->id,
            'submitted_stage_name' => $this->faker->unique()->userName(),
            'status' => ArtistRegistration::STATUS_PENDING_PAYMENT,
        ];
    }

    public function pendingPayment(array $payment = []): static
    {
        return $this->state([
            'status' => ArtistRegistration::STATUS_PENDING_PAYMENT,
        ])->withPayment(array_merge([
            'status' => 'pending',
            'paid_at' => null,
        ], $payment));
    }

    public function pendingReview(array $payment = []): static
    {
        return $this->state([
            'status' => ArtistRegistration::STATUS_PENDING_REVIEW,
        ])->withPayment(array_merge([
            'status' => 'paid',
            'paid_at' => now(),
        ], $payment));
    }

    public function rejected(array $payment = []): static
    {
        return $this->state([
            'status' => ArtistRegistration::STATUS_REJECTED,
            'reviewed_at' => now(),
            'rejected_at' => now(),
            'rejection_reason' => ArtistRegistration::REJECTION_REASON_OTHER,
            'admin_note' => 'Registration rejected during automated testing.',
        ])->withPayment(array_merge([
            'status' => 'paid',
            'paid_at' => now()->subDay(),
        ], $payment));
    }

    public function approved(array $payment = []): static
    {
        return $this->state([
            'status' => ArtistRegistration::STATUS_APPROVED,
            'reviewed_at' => now(),
            'approved_at' => now(),
            'expires_at' => now()->addDays(30),
        ])->withPayment(array_merge([
            'status' => 'paid',
            'paid_at' => now()->subDay(),
        ], $payment));
    }

    public function withPayment(array $attributes = []): static
    {
        return $this->afterCreating(function (ArtistRegistration $registration) use ($attributes): void {
            $registration->payment()->create(array_merge([
                'user_id' => $registration->user_id,
                'provider' => 'vnpay',
                'method' => 'VNPAY',
                'amount' => $registration->package?->price ?? 100000,
                'status' => 'pending',
                'transaction_code' => 'ART_TEST_'.$this->faker->unique()->numerify('########'),
            ], $attributes));
        });
    }
}
