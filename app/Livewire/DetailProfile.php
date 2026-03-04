<?php

namespace App\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\View\View;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasUser;
use Livewire\Component;

class DetailProfile extends Component implements HasActions, HasForms
{
    use HasSort;
    use HasUser;
    use InteractsWithActions;
    use InteractsWithForms;

    public $userClass;

    public ?array $data = [];

    protected static int $sort = 15;

    public function mount(): void
    {
        $this->user = $this->getUser();

        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only('address', 'number_phone', 'date_joined', 'gender'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Profile')
                    ->aside()
                    ->description('Perbarui Informasi Detail Anda.')
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('number_phone')
                            ->label('No Handphone')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date_joined')
                            ->label('Tanggal Bergabung')
                            ->hidden($this->user->hasRole(['datacenter', 'academic', 'finance'])),
                        Radio::make('gender')
                            ->label('Jenis Kelamin')
                            ->hidden($this->user->hasRole(['datacenter', 'academic', 'finance']))
                            ->options([
                                'men' => 'Laki - Laki',
                                'women' => 'Perempuan',
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $this->user->update($data);
        } catch (Halt $exception) {
            return;
        }

        Notification::make()
            ->success()
            ->title(__('filament-edit-profile::default.saved_successfully'))
            ->send();
    }

    public function render(): View
    {
        return view('livewire.detail-profile');
    }
}
