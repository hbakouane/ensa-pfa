<?php

namespace App\Http\Controllers;

use App\Actions\Applications\MoveApplicationStageAction;
use App\Actions\Applications\RejectApplicationAction;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\PipelineStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PipelineController extends Controller
{
    public function show(JobPosting $job): Response
    {
        $job->load([
            'skills',
            'department',
            'location',
        ]);

        $stages = PipelineStage::orderBy('position')
            ->get();

        $applications = Application::where('job_id', $job->id)
            ->with(['candidate', 'pipelineStage'])
            ->orderBy('position_in_stage')
            ->get()
            ->groupBy('pipeline_stage_id');

        return Inertia::render('Pipeline/Show', [
            'job' => $job,
            'stages' => $stages,
            'applications' => $applications,
        ]);
    }

    public function move(Request $request, Application $application, MoveApplicationStageAction $action): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => ['required', 'exists:pipeline_stages,id'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $targetStage = PipelineStage::findOrFail($validated['stage_id']);

        $application = $action->execute(
            $application,
            $targetStage,
            $validated['position'] ?? null,
        );

        return response()->json([
            'message' => 'Application moved successfully.',
            'application' => $application,
        ]);
    }

    public function reject(Request $request, Application $application, RejectApplicationAction $action): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason_id' => ['nullable', 'exists:rejection_reasons,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $application = $action->execute(
            $application,
            $validated['rejection_reason_id'] ?? null,
            $validated['notes'] ?? null,
        );

        return response()->json([
            'message' => 'Application rejected.',
            'application' => $application,
        ]);
    }
}
