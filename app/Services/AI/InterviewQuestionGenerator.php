<?php

namespace App\Services\AI;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\JobPosting;

class InterviewQuestionGenerator
{
    public function __construct(
        private OpenAIClient $client,
    ) {}

    /**
     * Generate tailored interview questions for a candidate-job pairing.
     *
     * @param  int  $count  Number of questions to generate.
     * @return array Array of question objects.
     */
    public function generate(Candidate $candidate, JobPosting $job, int $count = 10): array
    {
        $candidate->load(['skills', 'experiences', 'educations']);
        $job->load('skills');

        $prompt = $this->buildPrompt($candidate, $job, $count);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert interviewer. Generate tailored interview questions based on the candidate\'s background and the job requirements. Mix technical, behavioral, situational, and cultural fit questions. Vary the difficulty levels.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];

        $schema = [
            'name' => 'interview_questions',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'questions' => [
                        'type' => 'array',
                        'description' => 'List of interview questions.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'question' => [
                                    'type' => 'string',
                                    'description' => 'The interview question.',
                                ],
                                'category' => [
                                    'type' => 'string',
                                    'description' => 'Question category.',
                                    'enum' => ['technical', 'behavioral', 'situational', 'cultural_fit'],
                                ],
                                'difficulty' => [
                                    'type' => 'string',
                                    'description' => 'Question difficulty level.',
                                    'enum' => ['easy', 'medium', 'hard'],
                                ],
                                'what_to_look_for' => [
                                    'type' => 'string',
                                    'description' => 'Guidance on what a good answer should include.',
                                ],
                            ],
                            'required' => ['question', 'category', 'difficulty', 'what_to_look_for'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
                'required' => ['questions'],
                'additionalProperties' => false,
            ],
        ];

        $response = $this->client->chat(
            messages: $messages,
            responseFormat: $schema,
            options: [
                '_action' => 'generate_questions',
                '_loggable_type' => Application::class,
            ],
        );

        return $response['questions'] ?? [];
    }

    /**
     * Build the prompt for question generation.
     */
    private function buildPrompt(Candidate $candidate, JobPosting $job, int $count): string
    {
        $skills = $candidate->skills->pluck('name')->implode(', ');
        $jobSkills = $job->skills->pluck('name')->implode(', ');

        $experiences = $candidate->experiences->map(function ($exp) {
            return "{$exp->title} at {$exp->company_name}";
        })->implode(', ');

        return <<<PROMPT
        Generate exactly {$count} interview questions for this candidate-job pairing.

        ## Candidate
        Name: {$candidate->full_name}
        Headline: {$candidate->headline}
        Skills: {$skills}
        Experience: {$experiences}

        ## Job
        Title: {$job->title}
        Description: {$job->description}
        Requirements: {$job->requirements}
        Required Skills: {$jobSkills}
        Experience Level: {$job->experience_level}
        PROMPT;
    }
}
