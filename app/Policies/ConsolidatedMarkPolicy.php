<?php

namespace App\Policies;

use App\Models\ConsolidatedMark;
use App\Models\User;

class ConsolidatedMarkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function view(User $user, ConsolidatedMark $consolidatedMark): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasRole('Coordinator')
            && $consolidatedMark->project->coordinator_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ConsolidatedMark $consolidatedMark): bool
    {
        return false;
    }

    public function delete(User $user, ConsolidatedMark $consolidatedMark): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
