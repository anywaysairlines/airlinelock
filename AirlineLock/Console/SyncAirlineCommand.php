<?php

namespace Modules\AirlineLock\Console;

use App\Models\Pirep;
use App\Models\User;
use Illuminate\Console\Command;
use Modules\AirlineLock\Support\AirlineLock;

class SyncAirlineCommand extends Command
{
    protected $signature = 'airline-lock:sync
                            {--pireps : Also reassign existing PIREPs to the locked airline}';

    protected $description = 'Assign all existing users to the locked airline and home airport';

    public function handle(): int
    {
        AirlineLock::reset();

        $airline = AirlineLock::airline();
        if ($airline === null) {
            $this->error('No airline found. Set ANYWAYS_AIRLINE_ID or ANYWAYS_AIRLINE_ICAO, or create an active airline.');

            return self::FAILURE;
        }

        $homeAirport = AirlineLock::homeAirport();

        $this->info("Locked airline: {$airline->name} ({$airline->icao}, ID {$airline->id})");

        if ($homeAirport !== null) {
            $this->info("Locked home airport: {$homeAirport->description} ({$homeAirport->id})");
        } else {
            $this->warn('No hub found. Home airports will not be changed. Set ANYWAYS_HOME_AIRPORT_ICAO or flag a hub in the admin.');
        }

        $updates = ['airline_id' => $airline->id];
        if ($homeAirport !== null) {
            $updates['home_airport_id'] = $homeAirport->id;
        }

        $users = User::query()
            ->where(static function ($query) use ($airline, $homeAirport): void {
                $query->whereNull('airline_id')
                    ->orWhere('airline_id', '!=', $airline->id);

                if ($homeAirport !== null) {
                    $query->orWhereNull('home_airport_id')
                        ->orWhere('home_airport_id', '!=', $homeAirport->id);
                }
            })
            ->update($updates);

        $this->info("Updated {$users} user(s).");

        if ($this->option('pireps')) {
            $pireps = Pirep::query()
                ->where(static function ($query) use ($airline): void {
                    $query->whereNull('airline_id')
                        ->orWhere('airline_id', '!=', $airline->id);
                })
                ->update(['airline_id' => $airline->id]);

            $this->info("Updated {$pireps} PIREP(s).");
            $this->warn('PIREP airline changes can affect statistics and finances. Recalculate them if needed.');
        }

        return self::SUCCESS;
    }
}