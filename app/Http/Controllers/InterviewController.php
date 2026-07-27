<?php

namespace App\Http\Controllers;

use App\Actions\Interviews\ScheduleInterviewAction;
use App\Models\Interview;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InterviewController extends Controller
{
    public function index(Request $request): Response
    {
        $interviews = Interview::query()
            ->with(['application.candidate', 'interviewers'])
            ->when($request->input('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->input('from'), fn ($query, $from) => $query->where('scheduled_at', '>=', $from))
            ->when($request->input('to'), fn ($query, $to) => $query->where('scheduled_at', '<=', $to))
            ->latest('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Interviews/Index', [
            'interviews' => $interviews,
            'filters' => $request->only(['status', 'from', 'to']),
        ]);
    }

    public function show(Interview $interview): Response
    {
        $interview->load([
            'application.candidate',
            'application.job',
            'application.pipelineStage',
            'interviewers',
            'scorecards.user',
            'scorecards.criteria',
            'createdBy',
        ]);

        return Inertia::render('Interviews/Show', [
            'interview' => $interview,
        ]);
    }

    public function store(Request $request, ScheduleInterviewAction $action)
    {
        $validated = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:phone_screen,video,onsite,panel,technical,cultural'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'interviewer_ids' => ['nullable', 'array'],
            'interviewer_ids.*' => ['exists:users,id'],
        ]);

        $action->execute($validated);

        return back()->with('success', 'Entretien planifié avec succès.');
    }

    public function update(Interview $interview, Request $request)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'in:phone_screen,video,onsite,panel,technical,cultural'],
            'scheduled_at' => ['sometimes', 'required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:scheduled,confirmed,completed,cancelled,no_show'],
            'feedback' => ['nullable', 'string'],
        ]);

        $interview->update($validated);

        return back()->with('success', 'Entretien mis à jour avec succès.');
    }

    public function destroy(Interview $interview)
    {
        $interview->update(['status' => 'cancelled']);
        $interview->delete();

        return back()->with('success', 'Entretien annulé avec succès.');
    }
}
