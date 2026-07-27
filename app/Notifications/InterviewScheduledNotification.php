<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Interview $interview,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->interview->application->job->title ?? 'a position';
        $candidateName = $this->interview->application->candidate->full_name ?? 'a candidate';
        $scheduledAt = $this->interview->scheduled_at->format('l, F j, Y \a\t g:i A');

        return (new MailMessage)
            ->subject('Interview Scheduled: '.$jobTitle)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An interview has been scheduled with the following details:')
            ->line('**Candidate:** '.$candidateName)
            ->line('**Position:** '.$jobTitle)
            ->line('**Date & Time:** '.$scheduledAt)
            ->line('**Duration:** '.$this->interview->duration_minutes.' minutes')
            ->when($this->interview->location, fn ($mail) => $mail->line('**Location:** '.$this->interview->location))
            ->when($this->interview->meeting_url, fn ($mail) => $mail->action('Join Meeting', $this->interview->meeting_url))
            ->line('Please confirm your availability.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'interview_scheduled',
            'interview_id' => $this->interview->id,
            'message' => 'Interview "'.$this->interview->title.'" scheduled for '.$this->interview->scheduled_at->format('M j, Y g:i A'),
        ];
    }
}
