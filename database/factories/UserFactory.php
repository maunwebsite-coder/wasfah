<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $email = fake()->unique()->safeEmail();

        return [
            'name' => fake()->name(),
            'email' => $email,
            'google_email' => $email,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => \App\Models\User::ROLE_CUSTOMER,
            'chef_status' => \App\Models\User::CHEF_STATUS_NEEDS_PROFILE,
            'instagram_followers' => 0,
            'youtube_followers' => 0,
            'is_referral_partner' => false,
            'referral_commission_rate' => 5,
            'referral_commission_currency' => config('referrals.default_currency', 'JOD'),
            'referrer_id' => null,
            'referral_partner_since_at' => null,
            'referral_admin_notes' => null,
            'referral_skip_platform_fee' => false,
            'policies_accepted_at' => now(),
            'policies_accepted_ip' => '127.0.0.1',
            'policies_version' => config('legal.policies_version'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

