<?php

namespace App\Actions\Jobs;

use App\Models\JobPosting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateJobAction
{
    public function execute(array $data): JobPosting
    {
        return DB::transaction(function () use ($data) {
            $skills = $data['skills'] ?? [];
            unset($data['skills']);

            $data['created_by'] = Auth::id();
            $data['company_id'] = Auth::user()->company_id;
            $data['status'] = $data['status'] ?? 'draft';

            $job = JobPosting::create($data);

            foreach ($skills as $skill) {
                $job->skills()->create([
                    'name' => $skill['name'],
                    'is_required' => $skill['is_required'] ?? false,
                ]);
            }

            return $job->load('skills');
        });
    }
}
