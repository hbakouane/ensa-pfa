<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\Billing\SubscriptionService;
use App\Services\Billing\UsageLimitChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private UsageLimitChecker $usageLimitChecker,
    ) {}

    /**
     * Billing dashboard: current plan, usage, subscription status.
     */
    public function index(Request $request): Response
    {
        $company = $request->user()->company;
        $subscription = $company->subscription('default');
        $currentPlan = $company->plan;
        $usage = $this->usageLimitChecker->getUsage($company);
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('Settings/Billing', [
            'company' => $company,
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'usage' => $usage,
            'subscription' => $subscription,
            'intent' => fn () => $company->createSetupIntent(),
        ]);
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan_slug' => ['required', 'string', 'exists:plans,slug'],
            'payment_method' => ['required', 'string'],
            'interval' => ['sometimes', 'string', 'in:monthly,yearly'],
        ]);

        $company = $request->user()->company;

        $this->subscriptionService->subscribe(
            $company,
            $validated['plan_slug'],
            $validated['payment_method'],
            $validated['interval'] ?? 'monthly',
        );

        return back()->with('success', 'Abonnement créé avec succès.');
    }

    /**
     * Change the subscription plan.
     */
    public function changePlan(Request $request)
    {
        $validated = $request->validate([
            'new_plan_slug' => ['required', 'string', 'exists:plans,slug'],
            'interval' => ['sometimes', 'string', 'in:monthly,yearly'],
        ]);

        $company = $request->user()->company;

        $this->subscriptionService->changePlan(
            $company,
            $validated['new_plan_slug'],
            $validated['interval'] ?? 'monthly',
        );

        return back()->with('success', 'Forfait modifié avec succès.');
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(Request $request)
    {
        $company = $request->user()->company;

        $this->subscriptionService->cancel($company);

        return back()->with('success', 'Abonnement résilié. Vous conserverez l\'accès jusqu\'à la fin de la période de facturation.');
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(Request $request)
    {
        $company = $request->user()->company;

        $this->subscriptionService->resume($company);

        return back()->with('success', 'Abonnement repris avec succès.');
    }

    /**
     * List invoices.
     */
    public function invoices(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        $invoices = $company->invoices()->map(fn ($invoice) => [
            'id' => $invoice->id,
            'date' => $invoice->date()->toFormattedDateString(),
            'total' => $invoice->total(),
            'status' => $invoice->status,
            'pdf_url' => $invoice->invoicePdfUrl(),
        ]);

        return response()->json(['invoices' => $invoices]);
    }

    /**
     * Update payment method.
     */
    public function updatePaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string'],
        ]);

        $company = $request->user()->company;

        $company->updateDefaultPaymentMethod($validated['payment_method']);

        return back()->with('success', 'Moyen de paiement mis à jour avec succès.');
    }
}
