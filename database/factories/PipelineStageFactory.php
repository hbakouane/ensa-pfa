<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PipelineStage>
 */
class PipelineStageFactory extends Factory
{
    protected $model = PipelineStage::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Applied', 'Screening', 'Interview', 'Offer', 'Hired']),
            'slug' => fake()->slug(1),
            'color' => fake()->hexColor(),
            'position' => fake()->numberBetween(0, 10),
            'is_default' => false,
        ];
    }
}
