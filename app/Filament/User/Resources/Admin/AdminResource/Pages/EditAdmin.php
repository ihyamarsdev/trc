<?php

namespace App\Filament\User\Resources\Admin\AdminResource\Pages;

use App\Filament\Components\Support\GuardsStatusTransition;
use App\Filament\Components\Support\StatusTransition;
use App\Filament\User\Resources\Admin\AdminResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAdmin extends EditRecord
{
    use GuardsStatusTransition;

    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('saveHeader')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action(fn () => $this->save())
                ->keyBindings(['mod+s']),
            Actions\Action::make('overrideStatus')
                ->label('Override Status')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->modalHeading('Override Status')
                ->modalDescription('Melewati aturan alur status (merah → kuning → biru → hijau). Alasan wajib diisi dan tampil di riwayat Activity.')
                ->modalSubmitActionLabel('Override')
                ->form([
                    Select::make('status_id')
                        ->label('Status baru')
                        ->options(fn (): array => Status::query()->orderBy('order')->get()
                            ->mapWithKeys(fn (Status $status): array => [$status->id => "{$status->order}. {$status->name} (".StatusTransition::label($status).')'])
                            ->all())
                        ->searchable()
                        ->required(),
                    Textarea::make('reason')
                        ->label('Alasan')
                        ->required()
                        ->minLength(10)
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $to = Status::findOrFail($data['status_id']);

                    if ((int) $to->id === (int) $this->record->status_id) {
                        Notification::make()->title('Status baru sama dengan status saat ini')->warning()->send();

                        return;
                    }

                    StatusTransition::override($this->record, $to, $data['reason'], Auth::id());

                    $this->refreshFormData(['status_id', 'status_color']);

                    Notification::make()->title("Status di-override ke {$to->name}")->success()->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $this->logStatusChange();
        $this->promoteWhenComplete();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $input = strtotime($data['date_register']);
        $date = getdate($input);
        $data['monthYear'] = $date['month'].' '.$date['year'];

        $this->guardStatusChange($data);

        return $data;
    }
}
