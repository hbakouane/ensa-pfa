<?php

namespace App\Actions\Offers;

use App\Models\Offer;
use App\Services\Offers\OfferPdfGenerator;

class GenerateOfferPdfAction
{
    public function __construct(
        protected OfferPdfGenerator $pdfGenerator,
    ) {}

    public function execute(Offer $offer): string
    {
        return $this->pdfGenerator->generate($offer);
    }
}
