<?php

namespace App\Jobs;

use App\Events\InterviewQuestionsGenerated;
use App\Models\Application;
use App\Services\AI\InterviewQuestionGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateInterviewQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        private int $applicationId,
        private int $count = 10,
    ) {
        $this->onQueue(config('ai.queue'));
    }

    /**
     * Execute the job.
     */
    public function handle(InterviewQuestionGenerator $generator): void
    {
        $application = Application::with(['candidate', 'job'])->findOrFail($this->applicationId);

        $questions = $generator->generate(
            candidate: $application->candidate,
            job: $application->job,
            count: $this->count,
        );

        // Broadcast the generated questions for now
        event(new InterviewQuestionsGenerated($application, $questions));
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateInterviewQuestionsJob failed', [
            'application_id' => $this->applicationId,
            'count' => $this->count,
            'error' => $exception->getMessage(),
        ]);
    }
}
