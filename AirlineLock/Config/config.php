<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Locked airline
    |--------------------------------------------------------------------------
    |
    | Set either the numeric ID or the ICAO code of the airline that should be
    | forced everywhere. If both are empty, the first active airline is used.
    |
    | Examples in .env:
    |   ANYWAYS_AIRLINE_ID=1
    |   ANYWAYS_AIRLINE_ICAO=AWY
    |
    */

    'airline_id' => env('ANYWAYS_AIRLINE_ID'),

    'airline_icao' => env('ANYWAYS_AIRLINE_ICAO', ''),

    /*
    |--------------------------------------------------------------------------
    | Locked home airport / hub
    |--------------------------------------------------------------------------
    |
    | Set the airport ID (ICAO) or the ICAO code of your hub. If both are
    | empty, the first airport flagged as "hub" in the admin is used.
    |
    | Examples in .env:
    |   ANYWAYS_HOME_AIRPORT_ID=EDDF
    |   ANYWAYS_HOME_AIRPORT_ICAO=EDDF
    |
    */

    'home_airport_id' => env('ANYWAYS_HOME_AIRPORT_ID'),

    'home_airport_icao' => env('ANYWAYS_HOME_AIRPORT_ICAO', ''),

    'lock_home_airport' => filter_var(
        env('ANYWAYS_LOCK_HOME_AIRPORT', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Disable the airline dropdowns
    |--------------------------------------------------------------------------
    |
    | The module injects a tiny script into frontend HTML responses which
    | disables every airline select and shows a small lock note. Server-side
    | enforcement always happens, even if this is disabled.
    |
    */

    'disable_selects' => filter_var(
        env('ANYWAYS_AIRLINE_LOCK_DISABLE_SELECTS', true),
        FILTER_VALIDATE_BOOLEAN
    ),
];