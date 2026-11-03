<?php

namespace App\Filament\Components\Support;

use App\Models\Status;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * For the Create/Edit pages: refuse a status change the flow does not allow, and log the change that was saved.
 */
trait GuardsStatusTransition
{
    /**
     * Call from `mutateFormDataBeforeSave()`. The status select is a relationship field, so by then
     * `$this->record` may already carry the newly picked id; the stored one is read from the original attributes.
     *
     * @param  array<string, mixed>  $data
     */
    protected function guardStatusChange(array $data): void
    {
        $to = Status::find($data['status_id'] ?? null);
        $storedStatusId = $this->record->getOriginal('status_id');

        if ($to === null || (int) $to->id === (int) $storedStatusId) {
            return;
        }

        $attributes = array_merge($this->record->getOriginal(), Arr::only($data, array_keys(StatusTransition::REQUIRED_FIELDS)));

        $this->refuseUnless(StatusTransition::denial(Status::find($storedStatusId), $to, $attributes));
    }

    /**
     * Call from `mutateFormDataBeforeCreate()`.
     *
     * @param  array<string, mixed>  $data
     */
    protected function guardInitialStatus(array $data): void
    {
        $to = Status::find($data['status_id'] ?? null);

        if ($to !== null) {
            $this->refuseUnless(StatusTransition::initialDenial($to, $data));
        }
    }

    /**
     * Call from `afterSave()` / `afterCreate()`.
     */
    protected function logStatusChange(): void
    {
        StatusTransition::log($this->record, Auth::id());
    }

    /**
     * Call from `afterSave()` / `afterCreate()` of the pages whose form holds the required fields.
     * A status the user just picked in this save is left alone.
     */
    protected function promoteWhenComplete(): void
    {
        if ($this->record->wasRecentlyCreated || ! $this->record->wasChanged('status_id')) {
            StatusTransition::promoteIfComplete($this->record, Auth::id());
        }
    }

    private function refuseUnless(?string $denial): void
    {
        if ($denial === null) {
            return;
        }

        Notification::make()->title('Status tidak dapat diubah')->body($denial)->danger()->send();

        $this->halt();
    }
}
