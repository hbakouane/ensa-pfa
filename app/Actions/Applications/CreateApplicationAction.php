<?php

namespace App\Actions\Applications;

use App\Models\Application;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Auth;

class CreateApplicationAction
{
    public function execute(
        int $jobId,
        int $candidateId,
        ?string $coverLetter = null,
        ?string $resumePath = null,
    ): Application {
        $companyId = Auth::check()
            ? Auth::user()->company_id
            : null;

        // Get the first pipeline stage (lowest position) for this company
        $firstStage = PipelineStage::where('company_id', $companyId)
            ->orderBy('position')
            ->first();

        $application = Application::create([
            'job_id' => $jobId,
            'candidate_id' => $candidateId,
            'company_id' => $companyId,
            'pipeline_stage_id' => $firstStage?->id,
            'cover_letter' => $coverLetter,
            'resume_path' => $resumePath,
            'applied_at' => now(),
            'status' => 'active',
        ]);

        return $application->load(['candidate', 'pipelineStage', 'job']);
    }
}
