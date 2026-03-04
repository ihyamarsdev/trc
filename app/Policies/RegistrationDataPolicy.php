<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RegistrationData;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegistrationDataPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TimelineResource');
    }

    public function view(AuthUser $authUser, RegistrationData $registrationData): bool
    {
        return $authUser->can('View:TimelineResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TimelineResource');
    }

    public function update(AuthUser $authUser, RegistrationData $registrationData): bool
    {
        return $authUser->can('Update:TimelineResource');
    }

    public function delete(AuthUser $authUser, RegistrationData $registrationData): bool
    {
        return $authUser->can('Delete:TimelineResource');
    }

    public function restore(AuthUser $authUser, RegistrationData $registrationData): bool
    {
        return $authUser->can('Restore:TimelineResource');
    }

    public function forceDelete(AuthUser $authUser, RegistrationData $registrationData): bool
    {
        return $authUser->can('ForceDelete:TimelineResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TimelineResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TimelineResource');
    }

    public function replicate(AuthUser $authUser, RegistrationData $registrationData): bool
    {
        return $authUser->can('Replicate:TimelineResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TimelineResource');
    }

}