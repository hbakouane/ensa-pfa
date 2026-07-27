<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferRespondedNotification extends Notification implements ShouldQueue
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
        $candidateName = $this->offer->application->candidate->full_name ?? 'The candidate';
        $jobTitle = $this->offer->application->job->title ?? 'a position';
        $status = ucfirst($this->offer->status);

        return (new MailMessage)
            ->subject("Offer {$status}: {$jobTitle}")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("{$candidateName} has **{$this->offer->status}** the offer for **{$jobTitle}**.")
            ->action('View Offer', route('offers.show', $this->offer))
            ->line('Please take any necessary follow-up actions.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer_responded',
            'offer_id' => $this->offer->id,
            'status' => $this->offer->status,
            'message' => 'Offer '.$this->offer->status.' by '.$this->offer->application->candidate->full_name ?? 'the candidate',
        ];
    }
}
