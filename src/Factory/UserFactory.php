<?php

namespace Ns\Factory;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ns\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'), // Default password for testing
            'active' => true,
            'author' => 0,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the user is inactive
     */
    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => false,
            ];
        });
    }

    /**
     * Indicate that the user has a specific email
     */
    public function withEmail(string $email): self
    {
        return $this->state(function (array $attributes) use ($email) {
            return [
                'email' => $email,
            ];
        });
    }

    /**
     * Indicate that the user has a specific username
     */
    public function withUsername(string $username): self
    {
        return $this->state(function (array $attributes) use ($username) {
            return [
                'username' => $username,
            ];
        });
    }
}
