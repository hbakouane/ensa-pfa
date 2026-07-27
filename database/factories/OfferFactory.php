<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'application_id' => Application::factory(),
            'salary' => fake()->numberBetween(50000, 200000),
            'salary_currency' => 'USD',
            'salary_period' => 'yearly',
            'start_date' => now()->addDays(30),
            'expiry_date' => now()->addDays(7),
            'content' => fake()->paragraphs(3, true),
            'status' => 'draft',
            'token' => Str::random(64),
            'created_by' => User::factory(),
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
