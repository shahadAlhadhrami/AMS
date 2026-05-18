<?php

namespace App\Policies;

use App\Models\Semester;
use App\Models\User;

class SemesterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function view(User $user, Semester $semester): bool
    {
        return $user->hasRole('Super Admin');
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

        return $user->hasRole('Super Admin');
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
