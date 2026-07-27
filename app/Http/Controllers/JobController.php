<?php

namespace App\Http\Controllers;

use App\Actions\Jobs\CreateJobAction;
use App\Actions\Jobs\PublishJobAction;
use App\Models\Department;
use App\Models\JobCategory;
use App\Models\JobPosting;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function index(Request $request): Response
    {
        $jobs = JobPosting::query()
            ->with(['department', 'location', 'createdBy'])
            ->withCount('applications')
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->input('department_id'), fn ($query, $id) => $query->where('department_id', $id))
            ->when($request->input('location_id'), fn ($query, $id) => $query->where('location_id', $id))
            ->when($request->input('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'filters' => $request->only(['status', 'department_id', 'location_id', 'search']),
            'departments' => fn () => Department::orderBy('name')->get(),
            'locations' => fn () => Location::orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Jobs/Create', [
            'departments' => Department::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'categories' => JobCategory::whereNull('parent_id')->with('children')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, CreateJobAction $action)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'category_id' => ['nullable', 'exists:job_categories,id'],
            'employment_type' => ['required', 'string', 'in:full_time,part_time,contract,internship,freelance'],
            'experience_level' => ['nullable', 'string', 'in:entry,mid,senior,lead,executive'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'salary_currency' => ['nullable', 'string', 'max:3'],
            'show_salary' => ['boolean'],
            'closes_at' => ['nullable', 'date', 'after:today'],
            'skills' => ['nullable', 'array'],
            'skills.*.name' => ['required_with:skills', 'string', 'max:100'],
            'skills.*.is_required' => ['boolean'],
        ]);

        $job = $action->execute($validated);

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Offre d\'emploi créée avec succès.');
    }

    public function show(JobPosting $job): Response
    {
        $job->load([
            'skills',
            'department',
            'location',
            'category',
            'createdBy',
            'applications.candidate',
            'applications.pipelineStage',
        ]);

        return Inertia::render('Jobs/Show', [
            'job' => $job,
        ]);
    }

    public function edit(JobPosting $job): Response
    {
        $job->load('skills');

        return Inertia::render('Jobs/Edit', [
            'job' => $job,
            'departments' => Department::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'categories' => JobCategory::whereNull('parent_id')->with('children')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, JobPosting $job)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'category_id' => ['nullable', 'exists:job_categories,id'],
            'employment_type' => ['required', 'string', 'in:full_time,part_time,contract,internship,freelance'],
            'experience_level' => ['nullable', 'string', 'in:entry,mid,senior,lead,executive'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'gte:salary_min'],
            'salary_currency' => ['nullable', 'string', 'max:3'],
            'show_salary' => ['boolean'],
            'closes_at' => ['nullable', 'date'],
            'skills' => ['nullable', 'array'],
            'skills.*.name' => ['required_with:skills', 'string', 'max:100'],
            'skills.*.is_required' => ['boolean'],
        ]);

        $skills = $validated['skills'] ?? [];
        unset($validated['skills']);

        $job->update($validated);

        // Sync skills: delete old and recreate
        if ($request->has('skills')) {
            $job->skills()->delete();
            foreach ($skills as $skill) {
                $job->skills()->create([
                    'name' => $skill['name'],
                    'is_required' => $skill['is_required'] ?? false,
                ]);
            }
        }

        return back()->with('success', 'Offre d\'emploi mise à jour avec succès.');
    }

    public function destroy(JobPosting $job)
    {
        $job->delete();

        return redirect()->route('jobs.index')
            ->with('success', 'Offre d\'emploi supprimée avec succès.');
    }

    public function publish(JobPosting $job, PublishJobAction $action)
    {
        $action->execute($job);

        return back()->with('success', 'Offre d\'emploi publiée avec succès.');
    }

    public function close(JobPosting $job)
    {
        $job->update(['status' => 'closed']);

        return back()->with('success', 'Offre d\'emploi clôturée avec succès.');
    }

    public function archive(JobPosting $job)
    {
        $job->update(['status' => 'archived']);

        return back()->with('success', 'Offre d\'emploi archivée avec succès.');
    }
}
