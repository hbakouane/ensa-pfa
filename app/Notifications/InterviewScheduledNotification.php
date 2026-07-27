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
        $jobTitle = $this->interview->application->job->title ?? 'un poste';
        $candidateName = $this->interview->application->candidate->full_name ?? 'un candidat';
        $scheduledAt = $this->interview->scheduled_at->format('l j F Y \à H:i');

        return (new MailMessage)
            ->subject('Entretien planifié : '.$jobTitle)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Un entretien a été planifié avec les détails suivants :')
            ->line('**Candidat :** '.$candidateName)
            ->line('**Poste :** '.$jobTitle)
            ->line('**Date et heure :** '.$scheduledAt)
            ->line('**Durée :** '.$this->interview->duration_minutes.' minutes')
            ->when($this->interview->location, fn ($mail) => $mail->line('**Lieu :** '.$this->interview->location))
            ->when($this->interview->meeting_url, fn ($mail) => $mail->action('Rejoindre la réunion', $this->interview->meeting_url))
            ->line('Veuillez confirmer votre disponibilité.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'interview_scheduled',
            'interview_id' => $this->interview->id,
            'message' => 'Entretien "'.$this->interview->title.'" planifié le '.$this->interview->scheduled_at->format('d/m/Y H:i'),
        ];
    }
}
