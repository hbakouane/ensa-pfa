<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'industry' => fake()->randomElement(['Technology', 'Healthcare', 'Finance', 'Education', 'Retail']),
            'size' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'plan_slug' => 'free',
        ];
    }
}
