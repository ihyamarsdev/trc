<?php

namespace App\Imports;

use App\Models\User;
use App\Notifications\NewAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

class UserImport implements ToModel, WithHeadingRow
{
    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        $role = Role::where('name', $row['roles'])->first();
        $password = 12345678;

        // Buat atau update pengguna
        $user = User::updateOrCreate(
            [
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($password),
                'force_renew_password' => true,
            ]
        );

        $user->notify(new NewAccount($password));

        // Jika role ditemukan, assign role ke pengguna
        if ($role) {
            $user->assignRole($role);
        } else {
            $user->assignRole('salesforce');
        }

        return $user;
    }
}
