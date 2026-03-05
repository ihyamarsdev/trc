<?php

namespace App\Filament\User\Resources\Finance\Monitoring\Pages;

use App\Filament\User\Resources\Finance\Monitoring\AllProgramFinanceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateAllProgramFinance extends CreateRecord
{
    protected Width|string|null $maxWidth = Width::Full;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static string $resource = AllProgramFinanceResource::class;
}
