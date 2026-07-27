<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferSentNotification extends Notification implements ShouldQueue
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
        $companyName = $this->offer->company->name ?? 'a company';
        $jobTitle = $this->offer->application->job->title ?? 'a position';
        $respondUrl = route('offers.respond', $this->offer->token);

        return (new MailMessage)
            ->subject("You've received a job offer from ".$companyName)
            ->greeting('Congratulations!')
            ->line("You have received an offer for the **{$jobTitle}** position at **{$companyName}**.")
            ->line('Please review the offer details and respond at your earliest convenience.')
            ->action('View & Respond to Offer', $respondUrl)
            ->line('If you have any questions, please reach out to the hiring team.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer_sent',
            'offer_id' => $this->offer->id,
            'message' => 'Offer sent for '.$this->offer->application->job->title ?? 'a position',
        ];
    }
}
