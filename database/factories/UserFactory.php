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
        $name = fake()->name();
        $usernameBase = Str::slug(fake()->unique()->userName(), '_');
        $username = Str::of($usernameBase)->limit(32, '')->trim('_')->value();

        if (strlen((string) $username) < 3) {
            $username = 'user'.fake()->unique()->numberBetween(100, 999999);
            $username = Str::of($username)->limit(32, '')->value();
        }

        return [
            'username' => (string) $username,
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('Password123!'),
            'role' => 'user',
            'phone' => fake()->optional()->e164PhoneNumber(),
            'avatar_path' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this;
    }
}
