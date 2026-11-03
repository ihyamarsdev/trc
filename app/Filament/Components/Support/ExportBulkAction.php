<?php

namespace App\Filament\Components\Support;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\FilamentExport;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Column;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportBulkAction extends FilamentExportBulkAction
{
    /**
     * @var array<string, array<int, Column>>
     */
    protected array $columnGroups = [];

    /**
     * Export columns split into labelled groups, each with its own select-all toggle.
     *
     * @param  array<string, array<int, Column>>  $groups
     */
    public function groupedColumns(array $groups): static
    {
        $this->columnGroups = $groups;

        return $this->withColumns(array_merge(...array_values($groups)));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $packageForm = $this->form;

        $this->form(fn (): array => array_map(
            fn ($component) => $component instanceof CheckboxList && $component->getName() === 'filter_columns' && $this->columnGroups !== []
                ? $this->groupedColumnList()
                : $component,
            $this->evaluate($packageForm),
        ));

        $this->action(function ($records, array $data): StreamedResponse {
            if ($this->columnGroups !== [] && ! $this->isFilterColumnsDisabled()) {
                $data['filter_columns'] = collect($data['column_groups'] ?? [])->flatten()->values()->all();

                // The package exports every column when none are picked, which is never what "Hapus semua" means.
                if ($data['filter_columns'] === []) {
                    Notification::make()->title('Pilih minimal satu kolom untuk diexport')->danger()->send();

                    $this->halt();
                }
            }

            $this->fillDefaultData($data);

            return FilamentExport::callDownload($this, $records, $data);
        });
    }

    private function groupedColumnList(): Group
    {
        $sections = [];

        foreach (array_values($this->columnGroups) as $index => $columns) {
            $options = collect($columns)->mapWithKeys(fn (Column $column): array => [$column->getName() => $column->getLabel()])->all();

            $sections[] = Section::make(array_keys($this->columnGroups)[$index])
                ->compact()
                ->collapsible()
                ->schema([
                    CheckboxList::make("column_groups.{$index}")
                        ->hiddenLabel()
                        ->options($options)
                        ->default(array_keys($options))
                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                        ->bulkToggleable(),
                ]);
        }

        return Group::make($sections)->hidden($this->isFilterColumnsDisabled());
    }
}
