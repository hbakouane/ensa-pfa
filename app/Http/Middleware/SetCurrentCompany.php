<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->company_id) {
            // Set the team_id for Spatie Permissions team-based features
            setPermissionsTeamId($request->user()->company_id);

            // Share company data with Inertia
            $request->user()->loadMissing('company');
        }

        return $next($request);
    }
}
