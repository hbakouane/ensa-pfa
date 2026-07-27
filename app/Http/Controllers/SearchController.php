<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Global search across jobs and candidates.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'jobs' => [],
                'candidates' => [],
            ]);
        }

        $companyId = Auth::user()->company_id;

        $jobs = JobPosting::where('company_id', $companyId)
            ->where('title', 'like', "%{$query}%")
            ->take(5)
            ->get()
            ->map(fn (JobPosting $job) => [
                'id' => $job->id,
                'title' => $job->title,
                'subtitle' => $job->employment_type,
                'status' => $job->status,
                'url' => route('jobs.show', $job),
            ]);

        $candidates = Candidate::whereHas('companies', fn ($q) => $q->where('companies.id', $companyId))
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(5)
            ->get()
            ->map(fn (Candidate $candidate) => [
                'id' => $candidate->id,
                'name' => $candidate->full_name,
                'subtitle' => $candidate->email,
                'headline' => $candidate->headline,
                'url' => route('candidates.show', $candidate),
            ]);

        return response()->json([
            'jobs' => $jobs,
            'candidates' => $candidates,
        ]);
    }
}
