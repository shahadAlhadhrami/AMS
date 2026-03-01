<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function view(User $user, Course $course): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }
}
