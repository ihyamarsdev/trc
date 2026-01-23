<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UsersObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->register(Spatie\Permission\PermissionServiceProvider::class);
        // $this->app->register(Maatwebsite\Excel\ExcelServiceProvider::class);
        // $this->app->register(Barryvdh\DomPDF\ServiceProvider::class);
        // $this->app->register(Barryvdh\Snappy\ServiceProvider::class);
        // $this->app->register(HayderHatem\FilamentExcelImport\FilamentExcelImportServiceProvider::class);

        if ($this->app->environment('APP_ENV') == 'local') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('APP_ENV') == 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
