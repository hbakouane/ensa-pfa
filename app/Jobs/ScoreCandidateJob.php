<?php

namespace App\Jobs;

use App\Models\Application;
use App\Services\AI\CandidateScorer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScoreCandidateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        private int $applicationId,
    ) {
        $this->onQueue(config('ai.queue'));
    }

    /**
     * Execute the job.
     */
    public function handle(CandidateScorer $scorer): void
    {
        $application = Application::with(['candidate', 'job'])->findOrFail($this->applicationId);

        $scoreData = $scorer->score($application->candidate, $application->job);

        $application->update([
            'ai_score' => $scoreData['overall_score'],
            'ai_score_breakdown' => $scoreData,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ScoreCandidateJob failed', [
            'application_id' => $this->applicationId,
            'error' => $exception->getMessage(),
        ]);
    }
}
