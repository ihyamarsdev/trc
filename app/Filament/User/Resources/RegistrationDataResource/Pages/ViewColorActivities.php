<?php

namespace App\Filament\User\Resources\RegistrationDataResource\Pages;

use Filament\Resources\Pages\Page;
use JaOcero\ActivityTimeline\Pages\ActivityTimelinePage;
use App\Filament\User\Resources\RegistrationDataResource;
use Illuminate\Support\HtmlString;

class ViewColorActivities extends ActivityTimelinePage
{
    protected static string $resource = RegistrationDataResource::class;

    // protected static string $view = 'filament.user.resources.registration-data-resource.pages.view-color-activities';

    
}
