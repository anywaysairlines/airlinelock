# AirlineLock — phpVMS 7 Module

Locks the airline **and the home airport** in the frontend. Whether it is registration, profile, PIREP, flight search or a manipulated POST: the configured airline and your hub are enforced everywhere.

## What the module does

- **Registration:** only the locked airline and hub are offered.
- **Profile:** the airline and home airport can no longer be changed.
- **PIREP:** new and edited PIREPs always belong to the locked airline.
- **Flight search:** the airline filter only contains the locked airline.
- **Server-side:** `ForceAirline` overwrites `airline_id` on every frontend/API request, and `ForceHomeAirport` sets `home_airport_id` on every write. Model events on `User` and `Pirep` additionally enforce the values when saving.
- **Visuals:** airline and airport dropdowns are disabled by an injected script and show a lock note. This happens automatically; no theme changes are required.
- **Admin panel stays free:** admins can still manage airlines, users, hubs and PIREPs in the admin panel.

## Installation

### Option A: Upload through the admin panel

1. In the phpVMS admin, upload `anyways-airline-lock-module.zip` under **Modules**.
2. Activate the `AirlineLock` module under **Modules**. If it does not appear there:
   ```bash
   php artisan module:enable AirlineLock
   ```
3. Clear the cache:
   ```bash
   php artisan optimize:clear
   ```

Without SSH: delete `bootstrap/cache/config.php` and reload the admin page.

### Option B: Manual upload via FTP/file manager

1. Upload the `AirlineLock` folder from the ZIP to `phpvms/modules/AirlineLock`.
2. Activate the module via command:
   ```bash
   php artisan module:list
   php artisan module:enable AirlineLock
   ```
3. Clear the cache:
   ```bash
   php artisan optimize:clear
   ```

Without SSH: delete `bootstrap/cache/config.php` and reload the admin page.

## Activate via command

To check whether the module is detected and active:

```bash
php artisan module:list
```

Activate it:

```bash
php artisan module:enable AirlineLock
```

Deactivate it:

```bash
php artisan module:disable AirlineLock
```

After either command, run:

```bash
php artisan optimize:clear
```

`module:list` shows `Enabled` or `Disabled`. If `AirlineLock` is not listed at all, the folder is in the wrong place or `module.json` is missing – see Troubleshooting.

## Locking the airline

By default, the module uses the **first active airline**. If you only have one airline, no configuration is needed.

If you have multiple airlines, configure it in `.env`:

```env
# Either the ID:
ANYWAYS_AIRLINE_ID=1

# or the ICAO code:
ANYWAYS_AIRLINE_ICAO=AWY
```

You can find the ID and ICAO code in the admin under **Airlines**.

After a change:

```bash
php artisan config:clear
php artisan cache:clear
```

> If `ANYWAYS_AIRLINE_ID` or `ANYWAYS_AIRLINE_ICAO` is set but cannot be found, the module does **not** lock to a different airline. The selection stays unchanged until the configuration is correct. This prevents accidentally locking to the wrong airline.

## Locking the home airport / hub

By default, the module uses the **first airport flagged as a hub in the admin**. If you only have one hub, no configuration is needed.

If you have multiple hubs, or your hub is not flagged as a hub, set it in `.env`:

```env
# The airport ID is usually the ICAO code:
ANYWAYS_HOME_AIRPORT_ICAO=EDDF

# or:
ANYWAYS_HOME_AIRPORT_ID=EDDF
```

You can find the ICAO code in the admin under **Airports**. The airport must exist.

After a change:

```bash
php artisan config:clear
php artisan cache:clear
```

> If an airport is explicitly configured but cannot be found, **no** other airport is used. The selection stays unchanged until the configuration is correct.

## Updating existing users

Existing users are automatically moved to the locked airline and hub on their next login/save. To update all users at once:

```bash
php artisan airline-lock:sync
```

The command sets the airline **and** the home airport. Optionally, update all existing PIREPs as well:

```bash
php artisan airline-lock:sync --pireps
```

> Warning: Reassigning PIREP airlines can affect statistics and finances. Only use this if that is what you want.

## Disabling

Deactivate the `AirlineLock` module under **Modules** in the admin panel. Airline and airport selection will then behave normally again.

## Troubleshooting

- **Module does not appear in the admin panel or in `module:list`:** the folder must contain `modules/AirlineLock/module.json`. Then delete `bootstrap/cache/config.php` or run `php artisan optimize:clear`.
- **Module is listed but Disabled:** run `php artisan module:enable AirlineLock`, then `php artisan optimize:clear`.
- **Wrong airline is locked:** check `ANYWAYS_AIRLINE_ID` or `ANYWAYS_AIRLINE_ICAO`, then run `php artisan config:clear` and `php artisan cache:clear`.
- **Wrong hub is locked:** check `ANYWAYS_HOME_AIRPORT_ICAO` or `ANYWAYS_HOME_AIRPORT_ID`. Without configuration, the module uses the first airport with the hub flag.
- **Home airport is not locked:** check whether a hub is flagged under **Admin → Airports**, or set `ANYWAYS_HOME_AIRPORT_ICAO`; then clear the cache.
- **Dropdowns are not disabled:** check that `ANYWAYS_AIRLINE_LOCK_DISABLE_SELECTS` is `true` and the module is active. Server-side enforcement still works without the visual lock.
- **Existing users still have a different airline or home airport:** run `php artisan airline-lock:sync`.

## Technical details

| File | Purpose |
| --- | --- |
| `Http/Middleware/ForceAirline.php` | Overwrites `airline_id` on frontend/API requests |
| `Http/Middleware/ForceHomeAirport.php` | Overwrites `home_airport_id` on frontend/API writes |
| `Http/Middleware/InjectAirlineLockScript.php` | Injects the lock note into frontend HTML |
| `Support/AirlineLock.php` | Resolves the airline and hub and checks the context |
| `Providers/AirlineLockServiceProvider.php` | Registers middleware, view composers, model events and the command |
| `Console/SyncAirlineCommand.php` | `airline-lock:sync` for existing data |
| `Resources/views/script.blade.php` | Disables airline/airport selects and shows the notes |

## Configuration

| `.env` variable | Default | Purpose |
| --- | --- | --- |
| `ANYWAYS_AIRLINE_ID` | empty | ID of the airline to lock |
| `ANYWAYS_AIRLINE_ICAO` | empty | ICAO of the airline to lock |
| `ANYWAYS_HOME_AIRPORT_ICAO` | empty | ICAO of the hub to lock |
| `ANYWAYS_HOME_AIRPORT_ID` | empty | Airport ID of the hub to lock |
| `ANYWAYS_LOCK_HOME_AIRPORT` | `true` | Enable home airport locking |
| `ANYWAYS_AIRLINE_LOCK_DISABLE_SELECTS` | `true` | Disable dropdowns visually |

## Requirements

- phpVMS 7
- The `AirlineLock` module must be enabled in the admin panel
- For multiple airlines: set `ANYWAYS_AIRLINE_ID` or `ANYWAYS_AIRLINE_ICAO`
- For multiple hubs or no hub flag: set `ANYWAYS_HOME_AIRPORT_ICAO` or `ANYWAYS_HOME_AIRPORT_ID`

## License

GPL-3.0 – see `LICENSE`.