<?php

namespace Modules\AirlineLock\Providers;

use App\Models\Pirep;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\AirlineLock\Http\Middleware\ForceAirline;
use Modules\AirlineLock\Http\Middleware\ForceHomeAirport;
use Modules\AirlineLock\Http\Middleware\InjectAirlineLockScript;
use Modules\AirlineLock\Support\AirlineLock;

class AirlineLockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'anyways_airline_lock'
        );

        // If the config cache was built before the module was enabled, the key
        // may be missing. Set the defaults so the module still works.
        if ($this->app['config']->get('anyways_airline_lock') === null) {
            $this->app['config']->set(
                'anyways_airline_lock',
                require __DIR__.'/../Config/config.php'
            );
        }
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'anyways_airline_lock');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'airline-lock');

        $this->registerMiddleware();
        $this->registerViewComposers();
        $this->registerModelEvents();
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        // Force airline_id on every frontend/API request, even crafted requests.
        $router->pushMiddlewareToGroup('web', ForceAirline::class);
        $router->pushMiddlewareToGroup('api', ForceAirline::class);

        // Force home_airport_id on every frontend/API write.
        $router->pushMiddlewareToGroup('web', ForceHomeAirport::class);
        $router->pushMiddlewareToGroup('api', ForceHomeAirport::class);

        // Disable the visible airline/airport selects on frontend pages.
        $router->pushMiddlewareToGroup('web', InjectAirlineLockScript::class);
    }

    protected function registerViewComposers(): void
    {
        $views = [
            'auth.register',
            'profile.edit',
            'profile.fields',
            'flights.search',
            'pireps.create',
            'pireps.edit',
            'pireps.fields',
        ];

        View::composer($views, static function ($view): void {
            $airline = AirlineLock::airline();
            if ($airline === null) {
                return;
            }

            $list = [$airline->id => $airline->name];

            $view->with('airlines', $list);
            $view->with('airline_list', $list);
        });

        View::composer([
            'auth.register',
            'profile.edit',
            'profile.fields',
        ], static function ($view): void {
            $airport = AirlineLock::homeAirport();
            if ($airport === null) {
                return;
            }

            $view->with('airports', [
                $airport->id => $airport->description,
            ]);
        });
    }

    protected function registerModelEvents(): void
    {
        // Last line of defence: even if a request bypasses the middleware,
        // every frontend user is saved with the locked airline and hub, and
        // every PIREP with the locked airline.
        User::saving(static function (User $user): void {
            AirlineLock::applyToUser($user);
        });

        Pirep::saving(static function (Pirep $pirep): void {
            AirlineLock::applyToPirep($pirep);
        });
    }
}