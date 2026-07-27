<?php

namespace App\Http\Controllers;

use App\Jobs\BulkScoreCandidatesJob;
use App\Jobs\GenerateCandidateSummaryJob;
use App\Jobs\GenerateInterviewQuestionsJob;
use App\Jobs\ParseResumeJob;
use App\Jobs\ScoreCandidateJob;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIController extends Controller
{
    /**
     * Dispatch a job to parse a candidate's resume.
     */
    public function parseResume(Candidate $candidate): JsonResponse
    {
        ParseResumeJob::dispatch($candidate->id);

        return response()->json([
            'message' => 'Analyse du CV lancée.',
            'job_dispatched' => true,
        ]);
    }

    /**
     * Dispatch a job to score a candidate against their applied job.
     */
    public function scoreCandidate(Application $application): JsonResponse
    {
        ScoreCandidateJob::dispatch($application->id);

        return response()->json([
            'message' => 'Évaluation du candidat lancée.',
            'job_dispatched' => true,
        ]);
    }

    /**
     * Dispatch a job to bulk score all candidates for a job posting.
     */
    public function bulkScore(JobPosting $job): JsonResponse
    {
        BulkScoreCandidatesJob::dispatch($job->id);

        return response()->json([
            'message' => 'Évaluation groupée lancée pour toutes les candidatures.',
            'job_dispatched' => true,
        ]);
    }

    /**
     * Dispatch a job to generate an AI summary for a candidate.
     */
    public function summarize(Candidate $candidate): JsonResponse
    {
        GenerateCandidateSummaryJob::dispatch($candidate->id);

        return response()->json([
            'message' => 'Génération du résumé du candidat lancée.',
            'job_dispatched' => true,
        ]);
    }

    /**
     * Dispatch a job to generate tailored interview questions.
     */
    public function generateQuestions(Application $application, Request $request): JsonResponse
    {
        $count = $request->input('count', 10);

        GenerateInterviewQuestionsJob::dispatch($application->id, (int) $count);

        return response()->json([
            'message' => 'Génération des questions d\'entretien lancée.',
            'job_dispatched' => true,
        ]);
    }
}
