<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

use function encrypt;
use function fake;
use function json_encode;
use function now;
use function resolve;

/**
 * @extends Factory<User>
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
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication enabled.
     */
    public function withTwoFactor(): static
    {
        return $this->state(function (): array {
            $provider = resolve(TwoFactorAuthenticationProvider::class);

            return [
                'two_factor_secret' => encrypt($provider->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode([
                    Str::random(10).'-'.Str::random(10),
                    Str::random(10).'-'.Str::random(10),
                ])),
                'two_factor_confirmed_at' => now(),
            ];
        });
    }
}
