<?php

namespace App\Livewire;

use Filament\Forms;
use Livewire\Component;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasUser;

class DetailProfile extends Component implements HasForms
{
    use InteractsWithForms;
    use HasSort;
    use HasUser;

    public $userClass;

    public ?array $data = [];

    protected static int $sort = 15;

    public function mount(): void
    {
        $this->user = $this->getUser();

        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only('address', 'number_phone', 'date_joined', 'gender'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Profile')
                    ->aside()
                    ->description('Perbarui Informasi Detail Anda.')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('number_phone')
                            ->label('No Handphone')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_joined')
                            ->label('Tanggal Bergabung')
                            ->hidden($this->user->hasRole(['datacenter', 'academic', 'finance'])),
                        Forms\Components\Radio::make('gender')
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
