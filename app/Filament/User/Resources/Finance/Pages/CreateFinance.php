<?php

namespace App\Filament\User\Resources\Finance\Pages;

use App\Filament\User\Resources\Finance\FinanceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateFinance extends CreateRecord
{
    protected Width|string|null $maxWidth = Width::Full;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static string $resource = FinanceResource::class;
}
