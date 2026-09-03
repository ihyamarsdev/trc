<?php

namespace App\Console\Commands;

use App\Models\RegistrationData;
use App\Models\RegistrationStatus;
use App\Models\Status;
use Illuminate\Console\Command;

class FixGreenRecordsWithRed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-green-records-with-red';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengembalikan data berstatus hijau yang tidak sengaja ketambahan status merah setelahnya';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $greenStatusIds = Status::query()->where('color', 'green')->pluck('id')->toArray();

        if (empty($greenStatusIds)) {
            $this->warn('Tidak ada status dengan warna hijau di database.');

            return Command::SUCCESS;
        }

        $redStatusIds = Status::query()->where('color', 'red')->pluck('id')->toArray();

        $registrationIds = RegistrationStatus::query()
            ->whereIn('status_id', $greenStatusIds)
            ->distinct()
            ->pluck('registration_id');

        $fixedCount = 0;

        foreach ($registrationIds as $regId) {
            $latestGreenLog = RegistrationStatus::query()
                ->where('registration_id', $regId)
                ->whereIn('status_id', $greenStatusIds)
                ->latest('id')
                ->first();

            if (! $latestGreenLog) {
                continue;
            }

            $erroneousRedLogs = RegistrationStatus::query()
                ->where('registration_id', $regId)
                ->where('id', '>', $latestGreenLog->id)
                ->whereIn('status_id', $redStatusIds)
                ->get();

            if ($erroneousRedLogs->isNotEmpty()) {
                RegistrationStatus::query()
                    ->whereIn('id', $erroneousRedLogs->pluck('id'))
                    ->delete();

                $currentLatestLog = RegistrationStatus::query()
                    ->where('registration_id', $regId)
                    ->latest('id')
                    ->first();

                if ($currentLatestLog) {
                    $registration = RegistrationData::find($regId);

                    if ($registration) {
                        $greenStatus = Status::find($currentLatestLog->status_id);

                        $registration->status_id = $currentLatestLog->status_id;
                        $registration->status_color = $greenStatus?->color ?? 'green';
                        $registration->saveQuietly();

                        $fixedCount++;
                    }
                }
            }
        }

        $this->info("Berhasil mengembalikan {$fixedCount} data hijau yang ketambahan status merah.");

        return Command::SUCCESS;
    }
}
