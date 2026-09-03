<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AcademicPanelProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\DatacenterPanelProvider;
use App\Providers\Filament\FinancePanelProvider;
use App\Providers\Filament\SalesforcePanelProvider;
use App\Providers\Filament\UserPanelProvider;
use Spatie\Permission\PermissionServiceProvider;

return [
    AppServiceProvider::class,
    PermissionServiceProvider::class,
    AcademicPanelProvider::class,
    AdminPanelProvider::class,
    DatacenterPanelProvider::class,
    FinancePanelProvider::class,
    SalesforcePanelProvider::class,
    UserPanelProvider::class,
];
