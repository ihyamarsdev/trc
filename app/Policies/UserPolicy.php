<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:UserResource');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:UserResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:UserResource');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:UserResource');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:UserResource');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:UserResource');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:UserResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:UserResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:UserResource');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:UserResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:UserResource');
    }

}