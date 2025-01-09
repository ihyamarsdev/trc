<?php

namespace App\Imports;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $role = Role::where('name', $row['roles'])->first();

        // Buat atau update pengguna
        $user = User::updateOrCreate(
            [
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($row['password']),
            ]
        );

        // Jika role ditemukan, assign role ke pengguna
        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }
}
