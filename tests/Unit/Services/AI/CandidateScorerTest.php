<?php

namespace Tests\Unit\Services\AI;

use App\Models\Candidate;
use App\Models\JobPosting;
use App\Services\AI\CandidateScorer;
use App\Services\AI\OpenAIClient;
use App\Services\AI\Schemas\ScoreSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CandidateScorerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_score_returns_data_matching_score_schema(): void
    {
        $expectedResponse = [
            'overall_score' => 78,
            'skills_score' => 85,
            'experience_score' => 72,
            'education_score' => 70,
            'fit_score' => 80,
            'strengths' => [
                'Strong PHP and Laravel experience',
                'Previous experience in similar role',
            ],
            'weaknesses' => [
                'Limited experience with cloud infrastructure',
            ],
            'recommendation' => 'yes',
            'reasoning' => 'The candidate demonstrates strong technical skills and relevant experience.',
        ];

        $mockClient = Mockery::mock(OpenAIClient::class);
        $mockClient->shouldReceive('chat')
            ->once()
            ->withArgs(function ($messages, $responseFormat, $options) {
                return is_array($messages)
                    && $responseFormat === ScoreSchema::schema()
                    && $options['_action'] === 'score_candidate';
            })
            ->andReturn($expectedResponse);

        $candidate = Candidate::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'headline' => 'Full Stack Developer',
            'summary' => 'Experienced developer with 5 years in web development.',
            'location' => 'New York, NY',
        ]);

        $job = JobPosting::factory()->create([
            'title' => 'Senior PHP Developer',
            'description' => 'We are looking for an experienced PHP developer.',
            'requirements' => 'Must have 5+ years of experience with PHP and Laravel.',
            'employment_type' => 'full_time',
            'experience_level' => 'senior',
        ]);

        $scorer = new CandidateScorer($mockClient);
        $result = $scorer->score($candidate, $job);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('overall_score', $result);
        $this->assertArrayHasKey('skills_score', $result);
        $this->assertArrayHasKey('experience_score', $result);
        $this->assertArrayHasKey('education_score', $result);
        $this->assertArrayHasKey('fit_score', $result);
        $this->assertArrayHasKey('strengths', $result);
        $this->assertArrayHasKey('weaknesses', $result);
        $this->assertArrayHasKey('recommendation', $result);
        $this->assertArrayHasKey('reasoning', $result);
    }

    public function test_score_returns_value_between_0_and_100(): void
    {
        $expectedResponse = [
            'overall_score' => 65,
            'skills_score' => 70,
            'experience_score' => 60,
            'education_score' => 55,
            'fit_score' => 75,
            'strengths' => ['Good communication skills'],
            'weaknesses' => ['Lacks specific domain knowledge'],
            'recommendation' => 'maybe',
            'reasoning' => 'Candidate shows potential but lacks specific domain experience.',
        ];

        $mockClient = Mockery::mock(OpenAIClient::class);
        $mockClient->shouldReceive('chat')
            ->once()
            ->andReturn($expectedResponse);

        $candidate = Candidate::factory()->create();
        $job = JobPosting::factory()->create();

        $scorer = new CandidateScorer($mockClient);
        $result = $scorer->score($candidate, $job);

        $this->assertGreaterThanOrEqual(0, $result['overall_score']);
        $this->assertLessThanOrEqual(100, $result['overall_score']);
        $this->assertGreaterThanOrEqual(0, $result['skills_score']);
        $this->assertLessThanOrEqual(100, $result['skills_score']);
        $this->assertGreaterThanOrEqual(0, $result['experience_score']);
        $this->assertLessThanOrEqual(100, $result['experience_score']);
        $this->assertGreaterThanOrEqual(0, $result['education_score']);
        $this->assertLessThanOrEqual(100, $result['education_score']);
        $this->assertGreaterThanOrEqual(0, $result['fit_score']);
        $this->assertLessThanOrEqual(100, $result['fit_score']);
    }

    public function test_score_includes_valid_recommendation(): void
    {
        $expectedResponse = [
            'overall_score' => 90,
            'skills_score' => 95,
            'experience_score' => 88,
            'education_score' => 85,
            'fit_score' => 92,
            'strengths' => ['Excellent skills match', 'Strong leadership'],
            'weaknesses' => [],
            'recommendation' => 'strong_yes',
            'reasoning' => 'Exceptional candidate with outstanding qualifications.',
        ];

        $mockClient = Mockery::mock(OpenAIClient::class);
        $mockClient->shouldReceive('chat')
            ->once()
            ->andReturn($expectedResponse);

        $candidate = Candidate::factory()->create();
        $job = JobPosting::factory()->create();

        $scorer = new CandidateScorer($mockClient);
        $result = $scorer->score($candidate, $job);

        $validRecommendations = ['strong_yes', 'yes', 'maybe', 'no', 'strong_no'];
        $this->assertContains($result['recommendation'], $validRecommendations);
    }
}
