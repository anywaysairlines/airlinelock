# AnyWays AirlineLock

[![Release](https://github.com/anywaysairlines/airlinelock/actions/workflows/release.yml/badge.svg)](https://github.com/anywaysairlines/airlinelock/actions/workflows/release.yml)

phpVMS-7-Modul, das die Airline **und den Heimatflughafen** im Frontend auf eure Werte festnagelt.

## Features

- Registrierung, Profil, PIREP und Flugsuche verwenden immer dieselbe Airline.
- Der Home-Airport wird automatisch auf euren Hub gesetzt.
- Die Dropdowns werden deaktiviert und mit einem Schloss-Hinweis versehen.
- Serverseitige Durchsetzung: auch manipulierte POSTs oder Query-Strings werden überschrieben.
- Model-Events auf `User` und `Pirep` als zweite Absicherung.
- Der Adminbereich bleibt frei: Admins können Airlines, User, Hubs und PIREPs weiterhin verwalten.
- `airline-lock:sync` stellt bestehende User auf Airline und Hub um.

Die vollständige Dokumentation steht in [`AirlineLock/README.md`](AirlineLock/README.md).

## Installation

### Über den Adminbereich

1. Unter **Module** die ZIP aus dem [Release](https://github.com/anywaysairlines/airlinelock/releases) hochladen.
2. Modul `AirlineLock` aktivieren.
3. Cache leeren:
   ```bash
   php artisan optimize:clear
   ```

### Manuell

Den Ordner `AirlineLock` nach `phpvms/modules/AirlineLock` kopieren, im Admin unter **Module** aktivieren und den Cache leeren.

## Konfiguration

Standardmäßig wird die erste aktive Airline und der erste als Hub markierte Flughafen verwendet. Bei mehreren Airlines/Hubs in der `.env` setzen:

```env
ANYWAYS_AIRLINE_ICAO=AWY
ANYWAYS_HOME_AIRPORT_ICAO=EDDF
```

Alternativ `ANYWAYS_AIRLINE_ID` und `ANYWAYS_HOME_AIRPORT_ID`.

Danach:

```bash
php artisan config:clear
php artisan cache:clear
```

Bestehende User umstellen:

```bash
php artisan airline-lock:sync
```

## Release

Der Workflow [`.github/workflows/release.yml`](.github/workflows/release.yml) baut bei einem Tag `vX.Y.Z` – oder manuell über **Actions → Release** mit einer SemVer-Version – ein installierbares ZIP und hängt es an das GitHub-Release:

```text
anyways-airline-lock-module-1.0.0.zip
anyways-airline-lock-module-1.0.0.zip.sha256
```

Das ZIP enthält den Ordner `AirlineLock/` mit `module.json`, `LICENSE` und allen Moduldateien.

## Entwicklung

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

## Lizenz

GPL-3.0 – siehe [`LICENSE`](LICENSE).