# AnyWays AirlineLock

[![Release](https://github.com/anywaysairlines/airlinelock/actions/workflows/release.yml/badge.svg)](https://github.com/anywaysairlines/airlinelock/actions/workflows/release.yml)

A phpVMS 7 module that locks the airline **and the home airport** in the frontend to your configured values.

## Features

- Registration, profile, PIREP and flight search always use the configured airline.
- The home airport is automatically set to your hub.
- Airline and airport dropdowns are disabled and show a lock note.
- Server-side enforcement: manipulated POSTs and query strings are overwritten.
- Model events on `User` and `Pirep` act as a second line of defence.
- The admin panel stays free: admins can still manage airlines, users, hubs and PIREPs.
- `airline-lock:sync` moves existing users to the locked airline and hub.

The full documentation is in [`AirlineLock/README.md`](AirlineLock/README.md).

## Installation

### Via the admin panel

1. Upload the ZIP from the [releases](https://github.com/anywaysairlines/airlinelock/releases) under **Modules**.
2. Activate the `AirlineLock` module.
3. Clear the cache:
   ```bash
   php artisan optimize:clear
   ```

### Activate via Artisan

If the module is not active after uploading or copying it:

```bash
php artisan module:list
php artisan module:enable AirlineLock
php artisan optimize:clear
```

`module:list` shows whether `AirlineLock` is `Enabled` or `Disabled`.

### Manual installation

Copy the `AirlineLock` folder to `phpvms/modules/AirlineLock`, then run:

```bash
php artisan module:enable AirlineLock
php artisan optimize:clear
```

Alternatively, activate it under **Modules** in the admin panel.

## Configuration

By default, the first active airline and the first airport flagged as a hub are used. If you have multiple airlines or hubs, set them in `.env`:

```env
ANYWAYS_AIRLINE_ICAO=AWY
ANYWAYS_HOME_AIRPORT_ICAO=EDDF
```

You can also use `ANYWAYS_AIRLINE_ID` and `ANYWAYS_HOME_AIRPORT_ID`.

Then run:

```bash
php artisan config:clear
php artisan cache:clear
```

Move existing users to the locked values:

```bash
php artisan airline-lock:sync
```

## Release

The [`.github/workflows/release.yml`](.github/workflows/release.yml) workflow builds an installable ZIP on a `vX.Y.Z` tag – or manually via **Actions → Release** with a SemVer version – and attaches it to the GitHub release:

```text
anyways-airline-lock-module-1.0.0.zip
anyways-airline-lock-module-1.0.0.zip.sha256
```

The ZIP contains the `AirlineLock/` folder with `module.json`, `LICENSE` and all module files.

## Development

```text
AirlineLock/
├── module.json
├── composer.json
├── Config/
├── Console/
├── Http/Middleware/
├── Providers/
├── Resources/
└── Support/
```

## License

GPL-3.0 – see [`LICENSE`](LICENSE).