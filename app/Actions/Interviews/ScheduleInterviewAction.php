<?php

namespace App\Actions\Interviews;

use App\Events\InterviewScheduled;
use App\Models\Activity;
use App\Models\Interview;
use App\Notifications\InterviewScheduledNotification;
use Illuminate\Support\Facades\Auth;

class ScheduleInterviewAction
{
    public function execute(array $data): Interview
    {
        $interview = Interview::create([
            'company_id' => Auth::user()->company_id,
            'application_id' => $data['application_id'],
            'title' => $data['title'],
            'type' => $data['type'],
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'location' => $data['location'] ?? null,
            'meeting_url' => $data['meeting_url'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'scheduled',
            'created_by' => Auth::id(),
        ]);

        // Attach interviewers via pivot
        if (! empty($data['interviewer_ids'])) {
            $interview->interviewers()->attach($data['interviewer_ids']);
        }

        // Log activity
        Activity::log($interview, 'Interview scheduled: '.$interview->title);

        // Load relationships for notifications
        $interview->load(['application.candidate', 'application.job', 'interviewers']);

        // Send notification to each interviewer
        foreach ($interview->interviewers as $interviewer) {
            $interviewer->notify(new InterviewScheduledNotification($interview));
        }

        // Broadcast event
        event(new InterviewScheduled($interview));

        return $interview;
    }
}
