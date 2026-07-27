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
        $companyName = $this->offer->company->name ?? 'une entreprise';
        $jobTitle = $this->offer->application->job->title ?? 'un poste';
        $respondUrl = route('offers.respond', $this->offer->token);

        return (new MailMessage)
            ->subject('Vous avez reçu une offre d\'emploi de '.$companyName)
            ->greeting('Félicitations !')
            ->line("Vous avez reçu une offre pour le poste **{$jobTitle}** chez **{$companyName}**.")
            ->line('Veuillez consulter les détails de l\'offre et répondre dans les meilleurs délais.')
            ->action('Voir et répondre à l\'offre', $respondUrl)
            ->line('Si vous avez des questions, n\'hésitez pas à contacter l\'équipe de recrutement.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer_sent',
            'offer_id' => $this->offer->id,
            'message' => 'Offre envoyée pour '.$this->offer->application->job->title ?? 'un poste',
        ];
    }
}
