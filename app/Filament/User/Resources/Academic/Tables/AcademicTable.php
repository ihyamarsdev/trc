<?php

namespace App\Filament\User\Resources\Academic\Tables;

use App\Filament\User\Resources\Academic\Forms\AcademicForm;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AcademicTable
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
            ->columns(AcademicForm::columns())
            ->filters(AcademicForm::filters())
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->recordAction('view')
            ->recordActions([])
            ->toolbarActions(AcademicForm::bulkActions());
    }
}
