<?php

namespace App\Filament\User\Resources\Service\Tables;

use App\Filament\User\Resources\Service\Forms\ServiceForm;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->poll('5s')
            ->searchable()
            ->striped()
            ->paginated([50, 100, 200])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->withMax('activity', 'id')
                    ->where('years', now('Asia/Jakarta')->format('Y'))
                    ->whereRelation('status', fn ($q) => $q->whereBetween('order', [2, 10]))
                    ->orderByDesc('updated_at')
            )
            ->columns(ServiceForm::columns())
            ->filters(ServiceForm::filters())
            ->recordAction('view')
            ->recordActions([])
            ->toolbarActions(ServiceForm::bulkActions());
    }
}
