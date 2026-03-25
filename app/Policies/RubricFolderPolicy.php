<?php

namespace App\Policies;

use App\Models\RubricFolder;
use App\Models\User;

class RubricFolderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function view(User $user, RubricFolder $rubricFolder): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function update(User $user, RubricFolder $rubricFolder): bool
    {
        return $user->id === $rubricFolder->created_by;
    }

    public function delete(User $user, RubricFolder $rubricFolder): bool
    {
        return $user->id === $rubricFolder->created_by;
    }
}
