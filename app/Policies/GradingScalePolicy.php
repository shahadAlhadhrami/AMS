<?php

namespace App\Policies;

use App\Models\GradingScale;
use App\Models\User;

class GradingScalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function view(User $user, GradingScale $gradingScale): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function update(User $user, GradingScale $gradingScale): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function delete(User $user, GradingScale $gradingScale): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }
}
