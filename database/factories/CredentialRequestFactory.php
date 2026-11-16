<?php

namespace Database\Factories;

use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CredentialRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employer_id' => Employer::factory(),
            'candidate_email' => fake()->safeEmail(),
            'scopes' => ['degree', 'employment'],
            'status' => 'pending',
            'expires_at' => now()->addDay(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subHour()]);
    }
}
