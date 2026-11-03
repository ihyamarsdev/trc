<?php

namespace App\Filament\Components\Support;

use App\Models\Status;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Move every selected record to one status, for a whole district that is run on the same day.
 * Each record gets its own entry in the activity log.
 */
class ChangeStatusBulkAction extends BulkAction
{
    protected ?Closure $scopeStatuses = null;

    public static function getDefaultName(): ?string
    {
        return 'changeStatus';
    }

    /**
     * Limit the statuses offered, e.g. to the range a role works in.
     *
     * @param  Closure(Builder): mixed  $scope
     */
    public function statuses(Closure $scope): static
    {
        $this->scopeStatuses = $scope;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Ubah Status')
            ->icon('heroicon-o-arrow-path')
            ->modalHeading('Ubah Status Massal')
            ->modalDescription(fn (Collection $records): string => "{$records->count()} data dipilih akan dipindahkan ke status yang sama dan tercatat di riwayat Activity.")
            ->modalSubmitActionLabel('Ubah Status')
            ->form([
                Select::make('status_id')
                    ->label('Status baru')
                    ->options(fn (): array => $this->statusOptions())
                    ->in(fn (): array => array_keys($this->statusOptions()))
                    ->searchable()
                    ->required(),
                Textarea::make('note')
                    ->label('Catatan (opsional)')
                    ->helperText('Misalnya: pelaksanaan serentak Kab. Bogor. Muncul di riwayat tiap data.')
                    ->maxLength(500),
            ])
            ->action(function (Collection $records, array $data): void {
                $to = Status::findOrFail($data['status_id']);
                $result = StatusTransition::changeMany($records, $to, Auth::id(), $data['note'] ?? null);

                Notification::make()
                    ->title("{$result['changed']} data diubah ke {$to->name}")
                    ->body($result['unchanged'] > 0 ? "{$result['unchanged']} data sudah berstatus sama." : null)
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * @return array<int, string>
     */
    private function statusOptions(): array
    {
        return Status::query()
            ->when($this->scopeStatuses, fn (Builder $query) => ($this->scopeStatuses)($query))
            ->orderBy('order')
            ->get()
            ->mapWithKeys(fn (Status $status): array => [$status->id => "{$status->order}. {$status->name} (".StatusTransition::label($status).')'])
            ->all();
    }
}
