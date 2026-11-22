<?php

namespace Modules\AirlineLock\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\AirlineLock\Support\AirlineLock;
use Symfony\Component\HttpFoundation\Response;

class InjectAirlineLockScript
{
    /**
     * Add the visual "airline and home airport are locked" script to frontend
     * HTML pages. This is optional: the real enforcement happens in the
     * middleware and the User/Pirep model events.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!config('anyways_airline_lock.disable_selects', true)) {
            return $response;
        }

        if (!$response instanceof Response || $response->isRedirection()) {
            return $response;
        }

        if ($this->isAdminRequest($request)) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || $content === '') {
            return $response;
        }

        // Never inject twice (for example when middleware is registered more than once).
        if (str_contains($content, 'data-anyways-airline-lock')) {
            return $response;
        }

        if (AirlineLock::airline() === null && AirlineLock::homeAirport() === null) {
            return $response;
        }

        $position = strripos($content, '</body>');
        if ($position === false) {
            return $response;
        }

        try {
            $script = view('anyways_airline_lock::script')->render();
        } catch (\Throwable) {
            return $response;
        }

        $response->setContent(
            substr($content, 0, $position).$script.substr($content, $position)
        );

        return $response;
    }

    protected function isAdminRequest(Request $request): bool
    {
        return $request->is(
            'admin',
            'admin/*',
            'api',
            'api/*',
            'install',
            'install/*',
            'update',
            'update/*'
        );
    }
}