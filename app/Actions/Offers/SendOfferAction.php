<?php

namespace App\Actions\Offers;

use App\Models\Offer;
use App\Notifications\OfferSentNotification;
use Illuminate\Support\Facades\Notification;

class SendOfferAction
{
    public function execute(Offer $offer): Offer
    {
        $offer->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Load the candidate relationship
        $offer->load(['application.candidate', 'application.job', 'company']);

        // Send notification to the candidate via their email
        $candidate = $offer->application->candidate;

        Notification::route('mail', $candidate->email)
            ->notify(new OfferSentNotification($offer));

        return $offer;
    }
}
