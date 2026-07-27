<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $jobs = JobPosting::search($query)
            ->take(5)
            ->get()
            ->map(fn (JobPosting $job) => [
                'id' => $job->id,
                'title' => $job->title,
                'subtitle' => $job->employment_type,
                'status' => $job->status,
                'url' => route('jobs.show', $job),
            ]);

        $candidates = Candidate::search($query)
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
