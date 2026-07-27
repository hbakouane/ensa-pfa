<?php

namespace Database\Factories;

use App\Models\Candidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'headline' => fake()->jobTitle(),
            'summary' => fake()->paragraph(),
            'location' => fake()->city().', '.fake()->stateAbbr(),
            'linkedin_url' => 'https://linkedin.com/in/'.fake()->slug(),
            'source' => fake()->randomElement(['website', 'referral', 'linkedin', 'indeed']),
        ];
    }
}
