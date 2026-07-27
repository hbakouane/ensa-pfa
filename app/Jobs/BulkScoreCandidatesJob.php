<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkScoreCandidatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        private int $jobId,
    ) {
        $this->onQueue(config('ai.queue'));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $job = JobPosting::findOrFail($this->jobId);

        $applications = Application::where('job_id', $job->id)->get();

        foreach ($applications as $application) {
            ScoreCandidateJob::dispatch($application->id);
        }

        Log::info('BulkScoreCandidatesJob: dispatched scoring for all applications', [
            'job_id' => $this->jobId,
            'application_count' => $applications->count(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('BulkScoreCandidatesJob failed', [
            'job_id' => $this->jobId,
            'error' => $exception->getMessage(),
        ]);
    }
}
