<?php

namespace Modules\AirlineLock\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AirlineLock\Support\AirlineLock;
use Symfony\Component\HttpFoundation\Response;

class ForceHomeAirport
{
    /**
     * Overwrite home_airport_id on frontend/API writes before validation
     * and before the controller sees the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethodSafe() && !$this->isAdminRequest($request)) {
            if ($homeAirportId = AirlineLock::homeAirportId()) {
                $request->merge(['home_airport_id' => $homeAirportId]);
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