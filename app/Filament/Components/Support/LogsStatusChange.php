<?php

namespace App\Filament\Components\Support;

use Illuminate\Support\Facades\Auth;

/**
 * For the Create/Edit pages: log the status that was saved, and turn a complete red record yellow.
 */
trait LogsStatusChange
{
    /**
     * Call from `afterSave()` / `afterCreate()`.
     */
    protected function logStatusChange(): void
    {
        StatusTransition::log($this->record, Auth::id());
    }

    /**
     * Call from `afterSave()` / `afterCreate()` of the pages whose form holds the required fields.
     * A status picked in this very save is left alone.
     */
    protected function promoteWhenComplete(): void
    {
        if ($this->record->wasRecentlyCreated || ! $this->record->wasChanged('status_id')) {
            StatusTransition::promoteIfComplete($this->record, Auth::id());
        }
    }
}
