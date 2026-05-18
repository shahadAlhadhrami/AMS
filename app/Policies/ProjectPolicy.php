<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $project->coordinator_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function update(User $user, Project $project): bool
    {
        if ($project->semester->is_closed) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $project->coordinator_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        if ($project->status !== 'setup') {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $project->coordinator_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }
}
