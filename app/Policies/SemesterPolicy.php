<?php

namespace App\Policies;

use App\Models\Semester;
use App\Models\User;

class SemesterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function view(User $user, Semester $semester): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $semester->coordinators()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function update(User $user, Semester $semester): bool
    {
        if ($semester->is_closed) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $semester->coordinators()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Semester $semester): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }
}
