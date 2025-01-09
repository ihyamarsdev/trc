<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteAvatarsUnused extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-avatars-unused';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info('Command runs every minute.');

        $users = User::pluck('avatars_url')->toArray();

        collect(Storage::disk('public')->allFiles())
            ->reject(fn (string $file) => $file === '.gitignore')
            ->reject(fn (string $file) => in_array($file, $users))
            ->each(fn ($file) => Storage::disk('public')->delete($file));
    }
}
