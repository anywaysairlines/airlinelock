<?php

namespace Modules\AirlineLock\Support;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Pirep;
use App\Models\User;

class AirlineLock
{
    protected static ?Airline $airline = null;

    protected static bool $resolved = false;

    protected static ?Airport $homeAirport = null;

    protected static bool $homeAirportResolved = false;

    /**
     * Resolve the airline that is locked for the frontend.
     */
    public static function airline(): ?Airline
    {
        if (self::$resolved) {
            return self::$airline;
        }

        self::$resolved = true;

        try {
            $airlineId = config('anyways_airline_lock.airline_id');
            $airlineIcao = trim((string) config('anyways_airline_lock.airline_icao', ''));

            // An explicitly configured airline must exist. Do not silently fall
            // back to another airline if the configuration is wrong.
            if (filled($airlineId)) {
                self::$airline = Airline::find((int) $airlineId);

                return self::$airline;
            }

            if ($airlineIcao !== '') {
                self::$airline = Airline::where('icao', strtoupper($airlineIcao))->first();

                return self::$airline;
            }

            // No explicit configuration: use the first active airline.
            self::$airline = Airline::where('active', true)->orderBy('id')->first();
        } catch (\Throwable) {
            self::$airline = null;
        }

        return self::$airline;
    }

    public static function id(): ?int
    {
        return self::airline()?->id;
    }

    /**
     * Resolve the home airport / hub that is locked for the frontend.
     */
    public static function homeAirport(): ?Airport
    {
        if (!config('anyways_airline_lock.lock_home_airport', true)) {
            return null;
        }

        if (self::$homeAirportResolved) {
            return self::$homeAirport;
        }

        self::$homeAirportResolved = true;

        try {
            $airportId = trim((string) config('anyways_airline_lock.home_airport_id', ''));
            $airportIcao = trim((string) config('anyways_airline_lock.home_airport_icao', ''));

            // An explicitly configured airport must exist. Do not silently
            // fall back to a different airport if the configuration is wrong.
            if ($airportId !== '') {
                self::$homeAirport = Airport::find(strtoupper($airportId));

                return self::$homeAirport;
            }

            if ($airportIcao !== '') {
                self::$homeAirport = Airport::where('icao', strtoupper($airportIcao))->first();

                return self::$homeAirport;
            }

            // No explicit configuration: use the first airport flagged as hub.
            self::$homeAirport = Airport::where('hub', true)->orderBy('id')->first();
        } catch (\Throwable) {
            self::$homeAirport = null;
        }

        return self::$homeAirport;
    }

    public static function homeAirportId(): ?string
    {
        return self::homeAirport()?->id;
    }

    /**
     * Only enforce on web/API traffic. Admins, installers, updaters and
     * console commands (imports, queue workers) must keep full control.
     */
    public static function shouldEnforce(): bool
    {
        if (app()->runningInConsole() || !app()->bound('request')) {
            return false;
        }

        $request = request();

        return !$request->is(
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

    public static function applyToUser(User $user): void
    {
        if (!self::shouldEnforce()) {
            return;
        }

        if ($airlineId = self::id()) {
            $user->airline_id = $airlineId;
        }

        if ($homeAirportId = self::homeAirportId()) {
            $user->home_airport_id = $homeAirportId;
        }
    }

    public static function applyToPirep(Pirep $pirep): void
    {
        if (!self::shouldEnforce()) {
            return;
        }

        if ($airlineId = self::id()) {
            $pirep->airline_id = $airlineId;
        }
    }

    /**
     * Reset the per-request cache (mainly useful for tests).
     */
    public static function reset(): void
    {
        self::$airline = null;
        self::$resolved = false;
        self::$homeAirport = null;
        self::$homeAirportResolved = false;
    }
}