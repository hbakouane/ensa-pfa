<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'job_id' => JobPosting::factory(),
            'candidate_id' => Candidate::factory(),
            'pipeline_stage_id' => PipelineStage::factory(),
            'source' => fake()->randomElement(['website', 'referral', 'linkedin', 'indeed']),
            'applied_at' => now(),
            'position_in_stage' => 0,
        ];
    }
}
