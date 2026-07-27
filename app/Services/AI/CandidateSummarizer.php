<?php

namespace App\Services\AI;

use App\Models\Candidate;

class CandidateSummarizer
{
    public function __construct(
        private OpenAIClient $client,
    ) {}

    /**
     * Generate a professional summary for a candidate.
     */
    public function summarize(Candidate $candidate): string
    {
        $candidate->load(['skills', 'experiences', 'educations']);

        $candidateData = $this->buildCandidateData($candidate);

        $messages = [
            [
                'role' => 'system',
                'content' => 'Write a concise 2-3 paragraph professional summary of this candidate based on their resume data. Highlight key strengths and notable experience.',
            ],
            [
                'role' => 'user',
                'content' => $candidateData,
            ],
        ];

        $response = $this->client->chat(
            messages: $messages,
            options: [
                '_action' => 'summarize',
                '_loggable_type' => Candidate::class,
                '_loggable_id' => $candidate->id,
            ],
        );

        return $response['content'] ?? '';
    }

    /**
     * Build candidate data string for the prompt.
     */
    private function buildCandidateData(Candidate $candidate): string
    {
        $skills = $candidate->skills->pluck('name')->implode(', ');

        $experiences = $candidate->experiences->map(function ($exp) {
            $period = $exp->start_date?->format('Y-m').($exp->is_current ? ' to present' : ' to '.($exp->end_date?->format('Y-m') ?? 'N/A'));

            return "{$exp->title} at {$exp->company_name} ({$period})".($exp->description ? ": {$exp->description}" : '');
        })->implode("\n");

        $educations = $candidate->educations->map(function ($edu) {
            return "{$edu->degree} in ".($edu->field_of_study ?? 'N/A')." from {$edu->institution}";
        })->implode("\n");

        return <<<DATA
        Name: {$candidate->full_name}
        Headline: {$candidate->headline}
        Location: {$candidate->location}

        Skills: {$skills}

        Experience:
        {$experiences}

        Education:
        {$educations}
        DATA;
    }
}
