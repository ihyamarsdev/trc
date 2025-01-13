<?php

namespace App\Livewire;

use Filament\Forms;
use Livewire\Component;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasUser;

class EditProfile extends Component implements HasForms
{
    use InteractsWithForms;
    use HasSort;
    use HasUser;

    protected string $view = 'filament-edit-profile::livewire.edit-profile-form';

    public $userClass;

    public ?array $data = [];

    protected static int $sort = 0;

    public function mount(): void
    {
        $this->user = $this->getUser();

        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only(config('filament-edit-profile.avatar_column', 'avatar_url'), 'name', 'email'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-edit-profile::default.profile_information'))
                ->aside()
                ->description(__('filament-edit-profile::default.profile_information_description'))
                ->schema([
                    FileUpload::make('avatar_url')
                        ->label(__('filament-edit-profile::default.avatar'))
                        ->avatar()
                        ->imageEditor()
                        ->disk(config('filament-edit-profile.disk', 'public'))
                        ->visibility(config('filament-edit-profile.visibility', 'public'))
                        ->directory('avatars')
                        ->preserveFilenames()
                        ->rules('mimes:jpeg,png|max:1024'),
                    TextInput::make('name')
                        ->label(__('filament-edit-profile::default.name'))
                        ->required(),
                    TextInput::make('email')
                        ->label(__('filament-edit-profile::default.email'))
                        ->email()
                        ->required()
                        ->unique($this->userClass, ignorable: $this->user),
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
        return view('livewire.edit-profile');
    }
}
