<?php

namespace App\Policies;

use App\Models\RubricTemplate;
use App\Models\User;

class RubricTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function view(User $user, RubricTemplate $rubricTemplate): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function update(User $user, RubricTemplate $rubricTemplate): bool
    {
        if ($rubricTemplate->is_locked) {
            return false;
        }

        return $user->id === $rubricTemplate->created_by;
    }

    public function delete(User $user, RubricTemplate $rubricTemplate): bool
    {
        if ($rubricTemplate->is_locked) {
            return false;
        }

        return $user->id === $rubricTemplate->created_by;
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }
}
