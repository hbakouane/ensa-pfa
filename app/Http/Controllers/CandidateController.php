<?php

namespace App\Http\Controllers;

use App\Actions\Candidates\CreateCandidateAction;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CandidateController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = Auth::user()->company_id;

        $candidates = Candidate::whereHas('companies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['skills', 'tags'])
            ->withCount(['applications' => function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            }])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Candidates/Index', [
            'candidates' => $candidates,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Candidates/Create');
    }

    public function store(Request $request, CreateCandidateAction $action)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'headline' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'skills' => ['nullable', 'array'],
            'skills.*.name' => ['required_with:skills', 'string', 'max:100'],
            'skills.*.years_of_experience' => ['nullable', 'integer', 'min:0'],
            'skills.*.proficiency_level' => ['nullable', 'string', 'in:beginner,intermediate,advanced,expert'],
            'experiences' => ['nullable', 'array'],
            'experiences.*.company_name' => ['required_with:experiences', 'string', 'max:255'],
            'experiences.*.title' => ['required_with:experiences', 'string', 'max:255'],
            'experiences.*.start_date' => ['required_with:experiences', 'date'],
            'experiences.*.end_date' => ['nullable', 'date', 'after:experiences.*.start_date'],
            'experiences.*.is_current' => ['boolean'],
            'experiences.*.description' => ['nullable', 'string'],
            'educations' => ['nullable', 'array'],
            'educations.*.institution' => ['required_with:educations', 'string', 'max:255'],
            'educations.*.degree' => ['required_with:educations', 'string', 'max:255'],
            'educations.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'educations.*.start_date' => ['nullable', 'date'],
            'educations.*.end_date' => ['nullable', 'date'],
        ]);

        $candidate = $action->execute($validated);

        return redirect()->route('candidates.show', $candidate)
            ->with('success', 'Candidate created successfully.');
    }

    public function show(Candidate $candidate): Response
    {
        $companyId = Auth::user()->company_id;

        $candidate->load([
            'skills',
            'experiences' => fn ($query) => $query->orderByDesc('start_date'),
            'educations' => fn ($query) => $query->orderByDesc('start_date'),
            'tags',
            'applications' => function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->with(['job', 'pipelineStage']);
            },
        ]);

        return Inertia::render('Candidates/Show', [
            'candidate' => $candidate,
        ]);
    }
}
