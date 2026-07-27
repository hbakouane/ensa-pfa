<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferApprovalRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Offer $offer,
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
        $candidateName = $this->offer->application->candidate->full_name ?? 'a candidate';
        $jobTitle = $this->offer->application->job->title ?? 'a position';
        $salary = number_format($this->offer->salary, 2).' '.$this->offer->salary_currency.' ('.$this->offer->salary_period.')';

        return (new MailMessage)
            ->subject('Offer approval requested')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An offer requires your approval:')
            ->line('**Candidate:** '.$candidateName)
            ->line('**Position:** '.$jobTitle)
            ->line('**Salary:** '.$salary)
            ->action('Review Offer', route('offers.show', $this->offer))
            ->line('Please review and approve or reject this offer.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer_approval_requested',
            'offer_id' => $this->offer->id,
            'message' => 'Offer approval requested for '.$this->offer->application->candidate->full_name ?? 'a candidate',
        ];
    }
}
