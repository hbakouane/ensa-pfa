<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use App\Models\InterviewScorecard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScorecardController extends Controller
{
    public function store(Interview $interview, Request $request)
    {
        $validated = $request->validate([
            'overall_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'recommendation' => ['required', 'string', 'in:strong_yes,yes,maybe,no,strong_no'],
            'strengths' => ['nullable', 'string'],
            'concerns' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'criteria' => ['nullable', 'array'],
            'criteria.*.name' => ['required_with:criteria', 'string', 'max:255'],
            'criteria.*.rating' => ['required_with:criteria', 'integer', 'min:1', 'max:5'],
            'criteria.*.notes' => ['nullable', 'string'],
        ]);

        $scorecard = InterviewScorecard::updateOrCreate(
            [
                'interview_id' => $interview->id,
                'user_id' => Auth::id(),
            ],
            [
                'overall_rating' => $validated['overall_rating'],
                'recommendation' => $validated['recommendation'],
                'strengths' => $validated['strengths'] ?? null,
                'concerns' => $validated['concerns'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'submitted_at' => now(),
            ],
        );

        // Sync criteria
        if (! empty($validated['criteria'])) {
            $scorecard->criteria()->delete();

            foreach ($validated['criteria'] as $criterion) {
                $scorecard->criteria()->create([
                    'name' => $criterion['name'],
                    'rating' => $criterion['rating'],
                    'notes' => $criterion['notes'] ?? null,
                ]);
            }
        }

        return back()->with('success', 'Fiche d\'évaluation soumise avec succès.');
    }
}
