<?php

namespace App\Filament\Resources\OptionResource\Pages;

use App\Filament\Enum\OptionGroup;
use App\Filament\Resources\OptionResource;
use App\Models\Option;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOptions extends ListRecords
{
    protected static string $resource = OptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah pilihan')
                ->url(fn (): string => OptionResource::getUrl('create', ['group' => $this->activeTab])),
        ];
    }

    /**
     * One tab per master-data list.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return collect(OptionGroup::cases())
            ->mapWithKeys(fn (OptionGroup $group): array => [
                $group->value => Tab::make($group->label())
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('group', $group->value)),
            ])
            ->all();
    }

    /**
     * Reordering writes with a single bulk query, which skips the model events that clear the cache.
     *
     * @param  array<int|string, int|string>  $order
     */
    public function reorderTable(array $order): void
    {
        parent::reorderTable($order);

        foreach (OptionGroup::cases() as $group) {
            Option::flush($group);
        }
    }
}
