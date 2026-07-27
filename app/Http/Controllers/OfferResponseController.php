<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Notifications\OfferRespondedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class OfferResponseController extends Controller
{
    public function show(string $token): Response
    {
        $offer = Offer::where('token', $token)->firstOrFail();

        if (! $offer->canBeResponded()) {
            return Inertia::render('Public/OfferExpired', [
                'offer' => $offer->load(['application.job']),
            ]);
        }

        $offer->load(['application.candidate', 'application.job', 'company']);

        return Inertia::render('Public/OfferResponse', [
            'offer' => $offer,
        ]);
    }

    public function respond(Request $request, string $token)
    {
        $offer = Offer::where('token', $token)->firstOrFail();

        if (! $offer->canBeResponded()) {
            return back()->with('error', 'Cette offre ne peut plus recevoir de réponse.');
        }

        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:accepted,declined'],
        ]);

        $offer->update([
            'status' => $validated['decision'],
            'responded_at' => now(),
        ]);

        // Notify the hiring team
        $offer->load(['application.candidate', 'application.job', 'createdBy']);

        if ($offer->createdBy) {
            $offer->createdBy->notify(new OfferRespondedNotification($offer));
        }

        $message = $validated['decision'] === 'accepted'
            ? 'Félicitations ! Vous avez accepté l\'offre.'
            : 'Vous avez refusé l\'offre.';

        return redirect()->route('offers.respond', $token)
            ->with('success', $message);
    }
}
