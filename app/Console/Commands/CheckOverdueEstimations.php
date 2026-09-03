<?php

namespace App\Console\Commands;

use App\Models\RegistrationData;
use Illuminate\Console\Command;

class CheckOverdueEstimations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-overdue-estimations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ubah data berlabel kuning yang sudah melewati estimasi pelaksana menjadi label merah kembali';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = RegistrationData::updateOverdueYellowStatuses();

        $this->info("Berhasil memperbarui {$count} data yang melewati estimasi pelaksanaan menjadi label merah.");

        return Command::SUCCESS;
    }
}
