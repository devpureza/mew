<?php

namespace Database\Factories;

use App\Enums\UserRole;
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
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'cpf' => fake()->unique()->numerify('###########'),
            'birth_date' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'address_line' => fake()->streetAddress(),
            'address_line_two' => null,
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'postal_code' => fake()->postcode(),
            'country' => 'BR',
            'photo_path' => null,
            'role' => UserRole::Guest,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
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

    public function admin(): static
    {
        return $this->state(fn () => ['role' => UserRole::Admin]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['role' => UserRole::SuperAdmin]);
    }

    public function couple(): static
    {
        return $this->state(fn () => ['role' => UserRole::Couple]);
    }
}
