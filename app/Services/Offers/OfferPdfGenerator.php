<?php

namespace App\Services\Offers;

use App\Models\Offer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class OfferPdfGenerator
{
    /**
     * Generate a PDF from the offer content and store it.
     *
     * @return string The storage path of the generated PDF.
     */
    public function generate(Offer $offer): string
    {
        $pdf = Pdf::loadHTML($offer->content)
            ->setPaper('a4', 'portrait');

        $filename = 'offers/offer-'.$offer->id.'-'.time().'.pdf';

        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }
}
