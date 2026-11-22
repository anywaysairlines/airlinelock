# AirlineLock — phpVMS 7 Modul

Sperrt die Airline **und den Home-Airport** im Frontend. Egal ob Registrierung, Profil, PIREP, Flugsuche oder ein manipulierter POST: Überall werden die konfigurierte Airline und euer Hub erzwungen.

## Was das Modul macht

- **Registrierung:** Es werden nur noch die gesperrte Airline und der Hub angeboten.
- **Profil:** Airline und Heimatflughafen können nicht mehr gewechselt werden.
- **PIREP:** Neue und bearbeitete PIREPs gehören immer zur gesperrten Airline.
- **Flugsuche:** Der Airline-Filter enthält nur noch diese Airline.
- **Serverseitig:** `ForceAirline` überschreibt `airline_id` bei jedem Frontend-/API-Zugriff, `ForceHomeAirport` setzt `home_airport_id` bei jedem Schreibzugriff. Zusätzlich erzwingen Model-Events auf `User` und `Pirep` die Werte beim Speichern.
- **Optik:** Airline- und Flughafen-Dropdowns werden per eingeschleustem Script deaktiviert und mit einem Schloss-Hinweis versehen. Das passiert automatisch, es sind keine Theme-Änderungen nötig.
- **Adminbereich bleibt frei:** Admins können im Admin-Panel weiterhin Airlines, User, Hubs und PIREPs verwalten.

## Installation

### Variante A: Upload im Adminbereich

1. Im phpVMS-Admin unter **Module** das ZIP `anyways-airline-lock-module.zip` hochladen.
2. Danach unter **Module** das Modul `AirlineLock` aktivieren. Falls es dort nicht auftaucht:
   ```bash
   php artisan module:enable AirlineLock
   ```
3. Cache leeren:
   ```bash
   php artisan optimize:clear
   ```

Ohne SSH: Die Datei `bootstrap/cache/config.php` löschen und die Admin-Seite neu laden.

### Variante B: Manuell per FTP/File Manager

1. Den Ordner `AirlineLock` aus dem ZIP nach `phpvms/modules/AirlineLock` hochladen.
2. Modul per Command aktivieren:
   ```bash
   php artisan module:list
   php artisan module:enable AirlineLock
   ```
3. Cache leeren:
   ```bash
   php artisan optimize:clear
   ```

Ohne SSH: Die Datei `bootstrap/cache/config.php` löschen und die Admin-Seite neu laden.

## Aktivieren per Command

Wenn du wissen willst, ob das Modul erkannt und aktiv ist:

```bash
php artisan module:list
```

Aktivieren:

```bash
php artisan module:enable AirlineLock
```

Deaktivieren:

```bash
php artisan module:disable AirlineLock
```

Danach jeweils:

```bash
php artisan optimize:clear
```

`module:list` zeigt `Enabled` bzw. `Disabled`. Wird `AirlineLock` gar nicht gelistet, liegt der Ordner falsch oder `module.json` fehlt – siehe Troubleshooting.

## Airline festlegen

Standardmäßig nimmt das Modul die **erste aktive Airline**. Bei nur einer Airline musst du nichts einstellen.

Bei mehreren Airlines in der `.env` festlegen:

```env
# Entweder die ID:
ANYWAYS_AIRLINE_ID=1

# oder der ICAO-Code:
ANYWAYS_AIRLINE_ICAO=AWY
```

Die ID und den ICAO-Code findest du im Admin unter **Airlines**.

Nach einer Änderung:

```bash
php artisan config:clear
php artisan cache:clear
```

> Wenn `ANYWAYS_AIRLINE_ID` oder `ANYWAYS_AIRLINE_ICAO` gesetzt ist, aber nicht gefunden wird, sperrt das Modul **nicht** auf eine andere Airline. Dann bleibt die Auswahl unverändert, bis die Konfiguration korrekt ist. Das verhindert versehentliches Sperren auf die falsche Airline.

## Home-Airport / Hub festlegen

Standardmäßig nimmt das Modul den **ersten Flughafen, der im Admin als Hub markiert ist**. Bei nur einem Hub musst du nichts einstellen.

Bei mehreren Hubs oder wenn dein Hub nicht als Hub markiert ist, in der `.env` setzen:

```env
# Die Airport-ID ist normalerweise der ICAO-Code:
ANYWAYS_HOME_AIRPORT_ICAO=EDDF

# alternativ:
ANYWAYS_HOME_AIRPORT_ID=EDDF
```

Den ICAO-Code findest du im Admin unter **Airports**. Der Flughafen muss existieren.

Nach einer Änderung:

```bash
php artisan config:clear
php artisan cache:clear
```

> Ist ein Airport explizit konfiguriert, aber nicht vorhanden, wird **kein** anderer Airport eingesetzt. Dann bleibt die Auswahl unverändert, bis die Konfiguration stimmt.

## Bestehende User umstellen

Bestehende User werden beim nächsten Login/Speichern automatisch auf die gesperrte Airline und den Hub gesetzt. Für alle User auf einmal:

```bash
php artisan airline-lock:sync
```

Der Befehl setzt Airline **und** Home-Airport. Optional auch alle bestehenden PIREPs umstellen:

```bash
php artisan airline-lock:sync --pireps
```

> Achtung: Das nachträgliche Ändern von PIREP-Airlines kann Statistiken und Finanzen beeinflussen. Nur verwenden, wenn das gewünscht ist.

## Deaktivieren

Im Admin unter **Module** das Modul `AirlineLock` deaktivieren. Danach sind Airline- und Flughafen-Auswahl wieder normal.

## Troubleshooting

- **Modul taucht im Admin oder bei `module:list` nicht auf:** Der Ordner muss `modules/AirlineLock/module.json` enthalten. Danach `bootstrap/cache/config.php` löschen bzw. `php artisan optimize:clear` ausführen.
- **Modul ist da, aber Disabled:** `php artisan module:enable AirlineLock` ausführen und danach `php artisan optimize:clear`.
- **Falsche Airline wird gesperrt:** `ANYWAYS_AIRLINE_ID` oder `ANYWAYS_AIRLINE_ICAO` prüfen, danach `php artisan config:clear` und `php artisan cache:clear`.
- **Falscher Hub wird gesperrt:** `ANYWAYS_HOME_AIRPORT_ICAO` bzw. `ANYWAYS_HOME_AIRPORT_ID` prüfen. Ohne Konfiguration nimmt das Modul den ersten Airport mit Hub-Flag.
- **Home-Airport wird nicht gesperrt:** Prüfen, ob ein Hub in **Admin → Airports** markiert ist oder `ANYWAYS_HOME_AIRPORT_ICAO` gesetzt wurde; danach Cache leeren.
- **Dropdowns sind nicht deaktiviert:** Prüfen, ob `ANYWAYS_AIRLINE_LOCK_DISABLE_SELECTS` auf `true` steht und das Modul aktiviert ist. Der Server-Schutz funktioniert auch ohne die optische Sperre.
- **Bestehende User haben noch eine andere Airline/einen anderen Home-Airport:** `php artisan airline-lock:sync` ausführen.

## Technische Details

| Datei | Aufgabe |
| --- | --- |
| `Http/Middleware/ForceAirline.php` | Überschreibt `airline_id` bei Frontend-/API-Zugriffen |
| `Http/Middleware/ForceHomeAirport.php` | Überschreibt `home_airport_id` bei Frontend-/API-Schreibzugriffen |
| `Http/Middleware/InjectAirlineLockScript.php` | Fügt den Lock-Hinweis in Frontend-HTML ein |
| `Support/AirlineLock.php` | Ermittelt Airline, Hub und prüft den Kontext |
| `Providers/AirlineLockServiceProvider.php` | Registriert Middleware, View-Composer, Model-Events und Command |
| `Console/SyncAirlineCommand.php` | `airline-lock:sync` für bestehende Daten |
| `Resources/views/script.blade.php` | Deaktiviert Airline-/Flughafen-Selects und zeigt die Hinweise |

## Konfiguration

| `.env`-Variable | Standard | Bedeutung |
| --- | --- | --- |
| `ANYWAYS_AIRLINE_ID` | leer | ID der zu sperrenden Airline |
| `ANYWAYS_AIRLINE_ICAO` | leer | ICAO der zu sperrenden Airline |
| `ANYWAYS_HOME_AIRPORT_ICAO` | leer | ICAO des zu sperrenden Hubs |
| `ANYWAYS_HOME_AIRPORT_ID` | leer | Airport-ID des zu sperrenden Hubs |
| `ANYWAYS_LOCK_HOME_AIRPORT` | `true` | Home-Airport-Sperre aktivieren |
| `ANYWAYS_AIRLINE_LOCK_DISABLE_SELECTS` | `true` | Dropdowns optisch deaktivieren |

## Voraussetzungen

- phpVMS 7
- Modul `AirlineLock` muss im Admin aktiviert sein
- Bei mehreren Airlines: `ANYWAYS_AIRLINE_ID` oder `ANYWAYS_AIRLINE_ICAO` setzen
- Bei mehreren Hubs oder keinem Hub-Flag: `ANYWAYS_HOME_AIRPORT_ICAO` bzw. `ANYWAYS_HOME_AIRPORT_ID` setzen

## Lizenz

GPL-3.0 – siehe `LICENSE`.