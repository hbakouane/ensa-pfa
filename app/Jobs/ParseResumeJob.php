<?php

namespace App\Jobs;

use App\Events\ResumeParseCompleted;
use App\Models\Candidate;
use App\Services\AI\ResumeParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ParseResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        private int $candidateId,
    ) {
        $this->onQueue(config('ai.queue'));
    }

    /**
     * Execute the job.
     */
    public function handle(ResumeParser $parser): void
    {
        $candidate = Candidate::findOrFail($this->candidateId);

        if (! $candidate->resume_path) {
            Log::warning('ParseResumeJob: No resume file for candidate', ['candidate_id' => $this->candidateId]);

            return;
        }

        $filePath = Storage::disk('local')->path($candidate->resume_path);

        $parsedData = $parser->parse($filePath);

        // Update candidate's parsed_resume field
        $candidate->update([
            'parsed_resume' => $parsedData,
            'first_name' => $parsedData['first_name'] ?? $candidate->first_name,
            'last_name' => $parsedData['last_name'] ?? $candidate->last_name,
            'email' => $parsedData['email'] ?? $candidate->email,
            'phone' => $parsedData['phone'] ?? $candidate->phone,
            'headline' => $parsedData['headline'] ?? $candidate->headline,
            'summary' => $parsedData['summary'] ?? $candidate->summary,
            'location' => $parsedData['location'] ?? $candidate->location,
            'linkedin_url' => $parsedData['linkedin_url'] ?? $candidate->linkedin_url,
        ]);

        // Sync skills
        if (! empty($parsedData['skills'])) {
            $candidate->skills()->delete();
            foreach ($parsedData['skills'] as $skill) {
                $candidate->skills()->create([
                    'name' => $skill['name'],
                    'years_of_experience' => $skill['years_of_experience'],
                    'proficiency' => $skill['proficiency'],
                ]);
            }
        }

        // Sync experiences
        if (! empty($parsedData['experiences'])) {
            $candidate->experiences()->delete();
            foreach ($parsedData['experiences'] as $experience) {
                $candidate->experiences()->create([
                    'company_name' => $experience['company_name'],
                    'title' => $experience['title'],
                    'description' => $experience['description'],
                    'start_date' => $experience['start_date'],
                    'end_date' => $experience['end_date'],
                    'is_current' => $experience['is_current'],
                ]);
            }
        }

        // Sync educations
        if (! empty($parsedData['educations'])) {
            $candidate->educations()->delete();
            foreach ($parsedData['educations'] as $education) {
                $candidate->educations()->create([
                    'institution' => $education['institution'],
                    'degree' => $education['degree'],
                    'field_of_study' => $education['field_of_study'],
                    'start_date' => $education['start_date'],
                    'end_date' => $education['end_date'],
                    'gpa' => $education['gpa'],
                ]);
            }
        }

        event(new ResumeParseCompleted($candidate->fresh(), $parsedData));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ParseResumeJob failed', [
            'candidate_id' => $this->candidateId,
            'error' => $exception->getMessage(),
        ]);
    }
}
