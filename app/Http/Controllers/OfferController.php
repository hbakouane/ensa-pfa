<?php

namespace App\Http\Controllers;

use App\Actions\Offers\CreateOfferAction;
use App\Actions\Offers\GenerateOfferPdfAction;
use App\Actions\Offers\SendOfferAction;
use App\Models\Application;
use App\Models\Offer;
use App\Models\OfferTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function index(): Response
    {
        $offers = Offer::query()
            ->with(['application.candidate', 'application.job'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Offers/Index', [
            'offers' => $offers,
        ]);
    }

    public function create(Application $application): Response
    {
        $application->load(['candidate', 'job']);

        $templates = OfferTemplate::orderBy('name')->get();

        return Inertia::render('Offers/Create', [
            'application' => $application,
            'templates' => $templates,
        ]);
    }

    public function store(Request $request, CreateOfferAction $action)
    {
        $validated = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'template_id' => ['nullable', 'exists:offer_templates,id'],
            'salary' => ['required', 'numeric', 'min:0'],
            'salary_currency' => ['nullable', 'string', 'max:3'],
            'salary_period' => ['nullable', 'string', 'in:yearly,monthly,hourly'],
            'start_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'content' => ['required', 'string'],
        ]);

        $offer = $action->execute($validated);

        return redirect()->route('offers.show', $offer)
            ->with('success', 'Offer created successfully.');
    }

    public function show(Offer $offer): Response
    {
        $offer->load([
            'application.candidate',
            'application.job',
            'template',
            'approvals.user',
            'createdBy',
        ]);

        return Inertia::render('Offers/Show', [
            'offer' => $offer,
        ]);
    }

    public function send(Offer $offer, SendOfferAction $action)
    {
        $action->execute($offer);

        return back()->with('success', 'Offer sent to candidate successfully.');
    }

    public function downloadPdf(Offer $offer, GenerateOfferPdfAction $action)
    {
        $path = $action->execute($offer);

        return Storage::disk('local')->download($path, 'offer-'.$offer->id.'.pdf');
    }
}
