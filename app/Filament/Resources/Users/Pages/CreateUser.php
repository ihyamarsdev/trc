<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Notifications\NewAccount;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected Width|string|null $maxWidth = Width::Full;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static string $resource = UserResource::class;

    protected string $password;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->password = '12345678'; // generate a default password with length of 12 caracters
        $data['password'] = $this->password;
        $data['force_renew_password'] = true; // to force user to renew password on next login

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $user */
        $user = parent::handleRecordCreation($data); // handle the creation of the new user

        $user->notify(new NewAccount($this->password)); // notify the new user with account details

        return $user;
    }
}
