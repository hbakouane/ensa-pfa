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
        $candidateName = $this->offer->application->candidate->full_name ?? 'Le candidat';
        $jobTitle = $this->offer->application->job->title ?? 'un poste';
        $statusMap = ['accepted' => 'acceptée', 'declined' => 'refusée'];
        $statusFr = $statusMap[$this->offer->status] ?? $this->offer->status;
        $statusLabel = ucfirst($statusFr);

        return (new MailMessage)
            ->subject("Offre {$statusLabel} : {$jobTitle}")
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("{$candidateName} a **{$statusFr}** l'offre pour **{$jobTitle}**.")
            ->action('Voir l\'offre', route('offers.show', $this->offer))
            ->line('Veuillez effectuer les actions de suivi nécessaires.');
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
            'message' => 'Offre '.$this->offer->status.' par '.$this->offer->application->candidate->full_name ?? 'le candidat',
        ];
    }
}
