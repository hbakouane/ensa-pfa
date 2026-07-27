<?php

namespace App\Http\Middleware;

use App\Services\Billing\UsageLimitChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUsageLimits
{
    public function __construct(
        private UsageLimitChecker $checker,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  string  $feature  The feature to check: jobs, candidates, users, ai_parses
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $company = $request->user()?->company;

        if (! $company) {
            return $next($request);
        }

        $allowed = match ($feature) {
            'jobs' => $this->checker->canCreateJob($company),
            'candidates' => $this->checker->canAddCandidate($company),
            'users' => $this->checker->canInviteUser($company),
            'ai_parses' => $this->checker->canParseResume($company),
            default => true,
        };

        if (! $allowed) {
            $featureLabel = str_replace('_', ' ', $feature);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "You have reached your plan's limit for {$featureLabel}. Please upgrade your plan to continue.",
                ], 403);
            }

            return back()->with('error', "You have reached your plan's limit for {$featureLabel}. Please upgrade your plan to continue.");
        }

        return $next($request);
    }
}
