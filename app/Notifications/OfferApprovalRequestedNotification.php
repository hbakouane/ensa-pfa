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
        $candidateName = $this->offer->application->candidate->full_name ?? 'un candidat';
        $jobTitle = $this->offer->application->job->title ?? 'un poste';
        $salary = number_format($this->offer->salary, 2).' '.$this->offer->salary_currency.' ('.$this->offer->salary_period.')';

        return (new MailMessage)
            ->subject('Approbation d\'offre demandée')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Une offre nécessite votre approbation :')
            ->line('**Candidat :** '.$candidateName)
            ->line('**Poste :** '.$jobTitle)
            ->line('**Salaire :** '.$salary)
            ->action('Examiner l\'offre', route('offers.show', $this->offer))
            ->line('Veuillez examiner et approuver ou rejeter cette offre.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer_approval_requested',
            'offer_id' => $this->offer->id,
            'message' => 'Approbation d\'offre demandée pour '.$this->offer->application->candidate->full_name ?? 'un candidat',
        ];
    }
}
