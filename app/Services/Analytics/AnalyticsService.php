<?php

namespace App\Services\Analytics;

use App\Models\Application;
use App\Models\Company;
use App\Models\JobPosting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get an overview of analytics for a company within a date range.
     *
     * @return array{
     *     total_jobs: int,
     *     active_jobs: int,
     *     total_candidates: int,
     *     total_applications: int,
     *     applications_this_period: int,
     *     hires_this_period: int,
     *     average_score: float|null,
     *     conversion_rate: float,
     * }
     */
    public function overview(Company $company, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subDays(30);
        $to = $to ?? now();

        $totalJobs = $company->jobPostings()->count();

        $activeJobs = $company->jobPostings()->active()->count();

        $totalCandidates = DB::table('candidate_company')
            ->where('company_id', $company->id)
            ->count();

        $totalApplications = Application::where('company_id', $company->id)->count();

        $applicationsThisPeriod = Application::where('company_id', $company->id)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $hiresThisPeriod = DB::table('application_stage_history')
            ->join('pipeline_stages', 'application_stage_history.to_stage_id', '=', 'pipeline_stages.id')
            ->join('applications', 'application_stage_history.application_id', '=', 'applications.id')
            ->where('applications.company_id', $company->id)
            ->where('pipeline_stages.slug', 'hired')
            ->whereBetween('application_stage_history.created_at', [$from, $to])
            ->count();

        $averageScore = Application::where('company_id', $company->id)
            ->whereNotNull('ai_score')
            ->avg('ai_score');

        $conversionRate = $totalApplications > 0
            ? round(($hiresThisPeriod / $totalApplications) * 100, 2)
            : 0;

        return [
            'total_jobs' => $totalJobs,
            'active_jobs' => $activeJobs,
            'total_candidates' => $totalCandidates,
            'total_applications' => $totalApplications,
            'applications_this_period' => $applicationsThisPeriod,
            'hires_this_period' => $hiresThisPeriod,
            'average_score' => $averageScore ? round($averageScore, 1) : null,
            'conversion_rate' => $conversionRate,
        ];
    }
}
