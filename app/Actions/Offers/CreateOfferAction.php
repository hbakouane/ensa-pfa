<?php

namespace App\Actions\Offers;

use App\Models\Activity;
use App\Models\Offer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateOfferAction
{
    public function execute(array $data): Offer
    {
        $offer = Offer::create([
            'company_id' => Auth::user()->company_id,
            'application_id' => $data['application_id'],
            'template_id' => $data['template_id'] ?? null,
            'salary' => $data['salary'],
            'salary_currency' => $data['salary_currency'] ?? 'USD',
            'salary_period' => $data['salary_period'] ?? 'yearly',
            'start_date' => $data['start_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'content' => $data['content'],
            'status' => 'draft',
            'token' => Str::random(64),
            'created_by' => Auth::id(),
        ]);

        // Log activity
        Activity::log($offer, 'Offer created for application #'.$offer->application_id);

        return $offer->load(['application.candidate', 'application.job']);
    }
}
