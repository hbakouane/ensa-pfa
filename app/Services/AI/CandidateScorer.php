<?php

namespace App\Services\AI;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\JobPosting;
use App\Services\AI\Schemas\ScoreSchema;

class CandidateScorer
{
    public function __construct(
        private OpenAIClient $client,
    ) {}

    /**
     * Score a candidate against a job posting.
     *
     * @return array Score breakdown matching ScoreSchema.
     */
    public function score(Candidate $candidate, JobPosting $job): array
    {
        $candidateData = $this->buildCandidateProfile($candidate);
        $jobData = $this->buildJobProfile($job);

        $prompt = <<<PROMPT
        ## Candidate Profile
        {$candidateData}

        ## Job Requirements
        {$jobData}

        Score this candidate against the job requirements. Consider skills match, experience relevance, education fit, and overall suitability. Use the scoring weights: skills (35%), experience (25%), education (20%), overall fit (20%).
        PROMPT;

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert recruiter. Score this candidate against the job requirements. Be objective and fair.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];

        return $this->client->chat(
            messages: $messages,
            responseFormat: ScoreSchema::schema(),
            options: [
                '_action' => 'score_candidate',
                '_loggable_type' => Application::class,
            ],
        );
    }

    /**
     * Build a text profile of the candidate for the prompt.
     */
    private function buildCandidateProfile(Candidate $candidate): string
    {
        $candidate->load(['skills', 'experiences', 'educations']);

        $skills = $candidate->skills->map(function ($skill) {
            $parts = [$skill->name];
            if ($skill->years_of_experience) {
                $parts[] = $skill->years_of_experience.' years';
            }
            if ($skill->proficiency) {
                $parts[] = '('.$skill->proficiency.')';
            }

            return implode(' - ', $parts);
        })->implode(', ');

        $experiences = $candidate->experiences->map(function ($exp) {
            $period = $exp->start_date?->format('Y-m').($exp->is_current ? ' to present' : ' to '.($exp->end_date?->format('Y-m') ?? 'N/A'));

            return "{$exp->title} at {$exp->company_name} ({$period}): ".($exp->description ?? 'No description');
        })->implode("\n");

        $educations = $candidate->educations->map(function ($edu) {
            return "{$edu->degree} in ".($edu->field_of_study ?? 'N/A')." from {$edu->institution}".($edu->gpa ? " (GPA: {$edu->gpa})" : '');
        })->implode("\n");

        return <<<PROFILE
        Name: {$candidate->full_name}
        Headline: {$candidate->headline}
        Location: {$candidate->location}
        Summary: {$candidate->summary}

        Skills: {$skills}

        Experience:
        {$experiences}

        Education:
        {$educations}
        PROFILE;
    }

    /**
     * Build a text profile of the job for the prompt.
     */
    private function buildJobProfile(JobPosting $job): string
    {
        $job->load('skills');

        $skills = $job->skills->map(function ($skill) {
            $required = $skill->is_required ? '(Required)' : '(Nice to have)';

            return "{$skill->name} {$required}";
        })->implode(', ');

        return <<<JOB
        Title: {$job->title}
        Employment Type: {$job->employment_type}
        Experience Level: {$job->experience_level}

        Description:
        {$job->description}

        Requirements:
        {$job->requirements}

        Required Skills: {$skills}
        JOB;
    }
}
