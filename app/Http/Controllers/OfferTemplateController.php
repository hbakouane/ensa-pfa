<?php

namespace App\Http\Controllers;

use App\Models\OfferTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfferTemplateController extends Controller
{
    public function index(): Response
    {
        $templates = OfferTemplate::orderBy('name')->get();

        return Inertia::render('Offers/Templates', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_default' => ['boolean'],
        ]);

        OfferTemplate::create($validated);

        return back()->with('success', 'Offer template created successfully.');
    }

    public function update(OfferTemplate $template, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_default' => ['boolean'],
        ]);

        $template->update($validated);

        return back()->with('success', 'Offer template updated successfully.');
    }

    public function destroy(OfferTemplate $template)
    {
        $template->delete();

        return back()->with('success', 'Offer template deleted successfully.');
    }
}
