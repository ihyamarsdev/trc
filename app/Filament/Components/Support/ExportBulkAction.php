<?php

namespace App\Filament\Components\Support;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use Filament\Forms\Components\CheckboxList;

class ExportBulkAction extends FilamentExportBulkAction
{
    /**
     * Menambahkan tombol pilih semua / hapus semua pada daftar kolom export.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $packageForm = $this->form;

        $this->form(fn (): array => array_map(
            fn ($component) => $component instanceof CheckboxList && $component->getName() === 'filter_columns'
                ? $component->bulkToggleable()
                : $component,
            $this->evaluate($packageForm),
        ));
    }
}
