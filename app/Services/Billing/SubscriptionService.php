<?php

namespace App\Services\Billing;

use App\Models\Company;
use App\Models\Plan;
use Laravel\Cashier\Subscription;

class SubscriptionService
{
    /**
     * Subscribe a company to a plan via Stripe.
     */
    public function subscribe(
        Company $company,
        string $planSlug,
        string $paymentMethod,
        string $interval = 'monthly',
    ): Subscription {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        $priceId = $interval === 'yearly'
            ? $plan->stripe_yearly_price_id
            : $plan->stripe_monthly_price_id;

        // Update the default payment method
        $company->updateDefaultPaymentMethod($paymentMethod);

        // Create the subscription
        $subscription = $company->newSubscription('default', $priceId)->create($paymentMethod);

        // Update the company's plan slug
        $company->update(['plan_slug' => $planSlug]);

        return $subscription;
    }

    /**
     * Change a company's subscription to a different plan.
     */
    public function changePlan(
        Company $company,
        string $newPlanSlug,
        string $interval = 'monthly',
    ): Subscription {
        $plan = Plan::where('slug', $newPlanSlug)->where('is_active', true)->firstOrFail();

        $priceId = $interval === 'yearly'
            ? $plan->stripe_yearly_price_id
            : $plan->stripe_monthly_price_id;

        $subscription = $company->subscription('default');
        $subscription->swap($priceId);

        // Update the company's plan slug
        $company->update(['plan_slug' => $newPlanSlug]);

        return $subscription->refresh();
    }

    /**
     * Cancel a company's subscription at the end of the current billing period.
     */
    public function cancel(Company $company): void
    {
        $subscription = $company->subscription('default');

        if ($subscription && ! $subscription->canceled()) {
            $subscription->cancel();
        }
    }

    /**
     * Resume a previously cancelled subscription.
     */
    public function resume(Company $company): void
    {
        $subscription = $company->subscription('default');

        if ($subscription && $subscription->canceled() && $subscription->onGracePeriod()) {
            $subscription->resume();
        }
    }
}
