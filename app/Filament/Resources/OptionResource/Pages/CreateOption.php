<?php

namespace App\Filament\Resources\OptionResource\Pages;

use App\Filament\Enum\OptionGroup;
use App\Filament\Resources\OptionResource;
use App\Models\Option;
use Filament\Resources\Pages\CreateRecord;

class CreateOption extends CreateRecord
{
    protected static string $resource = OptionResource::class;

    /**
     * Opens with the list the admin came from, taken from `?group=`.
     */
    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill([
            'group' => OptionGroup::tryFrom((string) request()->query('group'))?->value,
            'is_active' => true,
        ]);

        $this->callHook('afterFill');
    }

    /**
     * New choices go to the end of their list.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = ((int) Option::query()->where('group', $data['group'])->max('sort_order')) + 1;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return OptionResource::getUrl('index', ['activeTab' => $this->record->group->value]);
    }
}
