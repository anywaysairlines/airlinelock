@php
    $lockedAirline = \Modules\AirlineLock\Support\AirlineLock::airline();
    $lockedHomeAirport = \Modules\AirlineLock\Support\AirlineLock::homeAirport();

    $lockedAirlineData = $lockedAirline ? [
        'id' => (string) $lockedAirline->id,
        'message' => __('airline-lock::messages.locked', ['airline' => $lockedAirline->name]),
    ] : null;

    $lockedHomeAirportData = $lockedHomeAirport ? [
        'id' => (string) $lockedHomeAirport->id,
        'message' => __('airline-lock::messages.home_airport_locked', ['airport' => $lockedHomeAirport->description]),
    ] : null;
@endphp

@if ($lockedAirline || $lockedHomeAirport)
    <!-- data-anyways-airline-lock -->
    <style>
        .anyways-airline-locked {
            opacity: .72;
        }

        .anyways-airline-lock-note {
            display: flex;
            align-items: center;
            gap: .35rem;
            margin-top: .4rem;
            color: var(--aw-magenta, #E5007D);
            font-size: .76rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .anyways-airline-lock-note i {
            font-size: .8rem;
        }

        [data-bs-theme="dark"] .anyways-airline-lock-note {
            color: var(--aw-magenta-light, #FF7CC0);
        }
    </style>

    <script>
        (function () {
            'use strict';

            var lockedAirline = @json($lockedAirlineData);

            var lockedHomeAirport = @json($lockedHomeAirportData);

            function lockSelect(select, locked, fieldName) {
                if (!select || !locked) {
                    return;
                }

                select.value = locked.id;
                select.disabled = true;
                select.classList.add('anyways-airline-locked');

                var wrapper = select.tomselect
                    ? select.tomselect.wrapper
                    : select.closest('.ts-wrapper');
                if (wrapper) {
                    wrapper.classList.add('anyways-airline-locked');
                }

                // TomSelect uses its own visible control.
                if (select.tomselect) {
                    try {
                        select.tomselect.setValue(locked.id, true);
                        select.tomselect.disable();
                    } catch (error) {
                        // Ignore plugin-specific errors.
                    }
                }

                if (select.getAttribute('data-anyways-locked') === '1') {
                    return;
                }

                select.setAttribute('data-anyways-locked', '1');

                // A disabled select is not submitted. Keep the locked value in a
                // hidden field so validation and controllers still receive it.
                if (select.form) {
                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = fieldName;
                    hidden.value = locked.id;
                    select.form.appendChild(hidden);
                }

                var container = select.closest('.col-md-6, .col-sm-4, .col-sm-6, .col-12, .mb-3, .form-group')
                    || select.parentNode;

                if (container) {
                    var note = document.createElement('div');
                    note.className = 'anyways-airline-lock-note';
                    note.innerHTML = '<i class="bi bi-lock-fill" aria-hidden="true"></i><span></span>';
                    note.querySelector('span').textContent = locked.message;
                    container.appendChild(note);
                }
            }

            function lockAll() {
                if (lockedAirline) {
                    document.querySelectorAll('select[name="airline_id"], select#airline_id')
                        .forEach(function (select) {
                            lockSelect(select, lockedAirline, 'airline_id');
                        });
                }

                if (lockedHomeAirport) {
                    document.querySelectorAll('select[name="home_airport_id"], select#home_airport_id')
                        .forEach(function (select) {
                            lockSelect(select, lockedHomeAirport, 'home_airport_id');
                        });
                }
            }

            function start() {
                lockAll();
                // Catch selects that are initialised slightly later (TomSelect etc.).
                window.setTimeout(lockAll, 300);
                window.setTimeout(lockAll, 1200);
            }

            if (document.readyState === 'complete') {
                start();
            } else {
                window.addEventListener('load', start);
            }
        })();
    </script>
@endif