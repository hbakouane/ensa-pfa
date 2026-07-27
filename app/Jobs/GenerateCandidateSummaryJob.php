<?php

namespace App\Jobs;

use App\Models\Candidate;
use App\Services\AI\CandidateSummarizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateCandidateSummaryJob implements ShouldQueue
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
    public function handle(CandidateSummarizer $summarizer): void
    {
        $candidate = Candidate::findOrFail($this->candidateId);

        $summary = $summarizer->summarize($candidate);

        $candidate->update([
            'summary' => $summary,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateCandidateSummaryJob failed', [
            'candidate_id' => $this->candidateId,
            'error' => $exception->getMessage(),
        ]);
    }
}
