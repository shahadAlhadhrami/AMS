<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\User;

class EvaluationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function view(User $user, Evaluation $evaluation): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Evaluation $evaluation): bool
    {
        return false;
    }

    public function delete(User $user, Evaluation $evaluation): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
