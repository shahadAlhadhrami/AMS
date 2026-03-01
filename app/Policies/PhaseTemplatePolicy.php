<?php

namespace App\Policies;

use App\Models\PhaseTemplate;
use App\Models\User;

class PhaseTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function view(User $user, PhaseTemplate $phaseTemplate): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function update(User $user, PhaseTemplate $phaseTemplate): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function delete(User $user, PhaseTemplate $phaseTemplate): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }
}
