<?php

namespace App\Filament\Imports;

use App\Models\User;
use App\Notifications\NewAccount;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('email')
                ->label('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('roles')
                ->label('roles')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?User
    {
        return User::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    protected function beforeSave(): void
    {
        if (! $this->record->exists) {
            $password = 12345678;
            $this->record->password = Hash::make($password);
            $this->record->force_renew_password = true;
            $this->record->notify(new NewAccount($password));
        }
    }

    protected function afterSave(): void
    {
        $roleName = $this->data['roles'] ?? 'salesforce';
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $this->record->assignRole($role);
        } else {
            $this->record->assignRole('salesforce');
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
