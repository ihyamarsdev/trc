<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RoleResource');
    }

    public function view(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('View:RoleResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RoleResource');
    }

    public function update(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Update:RoleResource');
    }

    public function delete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Delete:RoleResource');
    }

    public function restore(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Restore:RoleResource');
    }

    public function forceDelete(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('ForceDelete:RoleResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RoleResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RoleResource');
    }

    public function replicate(AuthUser $authUser, Role $role): bool
    {
        return $authUser->can('Replicate:RoleResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RoleResource');
    }
}
