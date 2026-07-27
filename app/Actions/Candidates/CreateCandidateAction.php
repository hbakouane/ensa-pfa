<?php

namespace App\Actions\Candidates;

use App\Models\Candidate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateCandidateAction
{
    public function execute(array $data): Candidate
    {
        return DB::transaction(function () use ($data) {
            $skills = $data['skills'] ?? [];
            $experiences = $data['experiences'] ?? [];
            $educations = $data['educations'] ?? [];
            unset($data['skills'], $data['experiences'], $data['educations']);

            $candidate = Candidate::create($data);

            // Attach to current company via pivot
            $candidate->companies()->attach(Auth::user()->company_id, [
                'added_by' => Auth::id(),
            ]);

            // Create associated skills
            foreach ($skills as $skill) {
                $candidate->skills()->create([
                    'name' => $skill['name'],
                    'years_of_experience' => $skill['years_of_experience'] ?? null,
                    'proficiency_level' => $skill['proficiency_level'] ?? null,
                ]);
            }

            // Create associated experiences
            foreach ($experiences as $experience) {
                $candidate->experiences()->create($experience);
            }

            // Create associated educations
            foreach ($educations as $education) {
                $candidate->educations()->create($education);
            }

            return $candidate->load(['skills', 'experiences', 'educations']);
        });
    }
}
