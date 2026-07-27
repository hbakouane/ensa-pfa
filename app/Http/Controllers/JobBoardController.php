<?php

namespace App\Http\Controllers;

use App\Actions\Applications\CreateApplicationAction;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class JobBoardController extends Controller
{
    public function index(Company $company): Response
    {
        $jobs = JobPosting::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->published()
            ->with(['department', 'location'])
            ->latest('published_at')
            ->paginate(15);

        return Inertia::render('Public/JobBoard', [
            'company' => $company,
            'jobs' => $jobs,
        ]);
    }

    public function show(Company $company, JobPosting $job): Response
    {
        $job = JobPosting::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('id', $job->id)
            ->published()
            ->with(['department', 'location', 'skills', 'category'])
            ->firstOrFail();

        return Inertia::render('Public/JobDetail', [
            'company' => $company,
            'job' => $job,
        ]);
    }

    public function apply(Company $company, JobPosting $job): Response
    {
        $job = JobPosting::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('id', $job->id)
            ->published()
            ->firstOrFail();

        return Inertia::render('Public/ApplicationForm', [
            'company' => $company,
            'job' => $job,
        ]);
    }

    public function submitApplication(Request $request, Company $company, JobPosting $job)
    {
        $job = JobPosting::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('id', $job->id)
            ->published()
            ->firstOrFail();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
        }

        DB::transaction(function () use ($validated, $job, $company, $resumePath) {
            // Find or create candidate by email
            $candidate = Candidate::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'phone' => $validated['phone'] ?? null,
                ]
            );

            // Attach to company if not already attached
            if (! $candidate->companies()->where('company_id', $company->id)->exists()) {
                $candidate->companies()->attach($company->id);
            }

            // Create the application
            $action = new CreateApplicationAction();

            // Temporarily set company context for the action
            $application = $action->execute(
                jobId: $job->id,
                candidateId: $candidate->id,
                coverLetter: $validated['cover_letter'] ?? null,
                resumePath: $resumePath,
            );

            // Ensure correct company_id since this is a public route (no auth)
            $application->update(['company_id' => $company->id]);

            // Also set the first pipeline stage for this company
            $firstStage = $company->pipelineStages()->orderBy('position')->first();
            if ($firstStage) {
                $application->update(['pipeline_stage_id' => $firstStage->id]);
            }
        });

        return redirect()->route('careers.show', [$company->slug, $job->slug])
            ->with('success', 'Votre candidature a été soumise avec succès !');
    }
}
