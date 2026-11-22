<?php

namespace Modules\AirlineLock\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AirlineLock\Support\AirlineLock;
use Symfony\Component\HttpFoundation\Response;

class ForceAirline
{
    /**
     * Overwrite airline_id on every frontend/API request before validation
     * and before the controller sees it. This also forces flight search
     * filters and crafted query strings to the locked airline.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isAdminRequest($request)) {
            if ($airlineId = AirlineLock::id()) {
                // Flight search, pilot list and live map may read the filter
                // from the query string, so force it there as well.
                if ($request->isMethodSafe() && $request->routeIs(
                    'frontend.flights.*',
                    'frontend.pilots.*',
                    'frontend.users.*',
                    'frontend.livemap.*'
                )) {
                    $request->query->set('airline_id', $airlineId);
                }

                $request->merge(['airline_id' => $airlineId]);
            }
        }

        return $next($request);
    }

    protected function isAdminRequest(Request $request): bool
    {
        return $request->is(
            'admin',
            'admin/*',
            'api/admin',
            'api/admin/*',
            'install',
            'install/*',
            'update',
            'update/*'
        );
    }
}