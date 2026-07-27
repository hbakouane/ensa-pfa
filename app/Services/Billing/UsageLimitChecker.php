<?php

namespace App\Services\Billing;

use App\Models\AiUsageLog;
use App\Models\Application;
use App\Models\Company;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class UsageLimitChecker
{
    /**
     * Check if the company can create a new job posting.
     */
    public function canCreateJob(Company $company): bool
    {
        $plan = $this->getPlan($company);
        $limit = $plan->getLimit('max_jobs');

        if ($limit === -1) {
            return true;
        }

        $activeJobs = $company->jobPostings()
            ->whereIn('status', ['draft', 'published'])
            ->count();

        return $activeJobs < $limit;
    }

    /**
     * Check if the company can add a new candidate.
     */
    public function canAddCandidate(Company $company): bool
    {
        $plan = $this->getPlan($company);
        $limit = $plan->getLimit('max_candidates');

        if ($limit === -1) {
            return true;
        }

        $currentCandidates = DB::table('candidate_company')
            ->where('company_id', $company->id)
            ->count();

        return $currentCandidates < $limit;
    }

    /**
     * Check if the company can invite a new user.
     */
    public function canInviteUser(Company $company): bool
    {
        $plan = $this->getPlan($company);
        $limit = $plan->getLimit('max_users');

        if ($limit === -1) {
            return true;
        }

        $currentUsers = $company->users()->count();

        return $currentUsers < $limit;
    }

    /**
     * Check if the company can perform another AI resume parse.
     */
    public function canParseResume(Company $company): bool
    {
        $plan = $this->getPlan($company);
        $limit = $plan->getLimit('ai_parses_per_month');

        if ($limit === -1) {
            return true;
        }

        $currentMonthParses = AiUsageLog::where('company_id', $company->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        return $currentMonthParses < $limit;
    }

    /**
     * Get current usage counts and limits for all features.
     *
     * @return array<string, array{current: int, max: int}>
     */
    public function getUsage(Company $company): array
    {
        $plan = $this->getPlan($company);

        $activeJobs = $company->jobPostings()
            ->whereIn('status', ['draft', 'published'])
            ->count();

        $currentCandidates = DB::table('candidate_company')
            ->where('company_id', $company->id)
            ->count();

        $currentUsers = $company->users()->count();

        $currentMonthParses = AiUsageLog::where('company_id', $company->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        return [
            'jobs' => [
                'current' => $activeJobs,
                'max' => $plan->getLimit('max_jobs'),
            ],
            'candidates' => [
                'current' => $currentCandidates,
                'max' => $plan->getLimit('max_candidates'),
            ],
            'users' => [
                'current' => $currentUsers,
                'max' => $plan->getLimit('max_users'),
            ],
            'ai_parses' => [
                'current' => $currentMonthParses,
                'max' => $plan->getLimit('ai_parses_per_month'),
            ],
        ];
    }

    /**
     * Get the plan for a company.
     */
    private function getPlan(Company $company): Plan
    {
        return $company->plan ?? Plan::where('slug', 'free')->firstOrFail();
    }
}
