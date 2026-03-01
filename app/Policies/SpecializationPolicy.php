<?php

namespace App\Policies;

use App\Models\Specialization;
use App\Models\User;

class SpecializationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function view(User $user, Specialization $specialization): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function update(User $user, Specialization $specialization): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function delete(User $user, Specialization $specialization): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }
}
