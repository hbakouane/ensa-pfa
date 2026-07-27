<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->company) {
            return redirect()->route('company.register');
        }

        $company = $user->company;

        // Free plan always has access
        if ($company->plan_slug === 'free') {
            return $next($request);
        }

        // Check for active Stripe subscription
        if (! $company->subscribed('default')) {
            return redirect()->route('billing.index')
                ->with('warning', 'Your subscription has expired. Please update your billing information.');
        }

        return $next($request);
    }
}
