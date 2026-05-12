<?php

namespace App\Support;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class StudentProjectReassignment
{
    /**
     * @param array<int, int> $studentIds
     * @return array<int, array<int, Project>>
     */
    public static function existingAssignmentsForSemester(array $studentIds, int $semesterId, ?int $excludeProjectId = null): array
    {
        $studentIds = array_values(array_unique(array_filter($studentIds)));

        if (empty($studentIds)) {
            return [];
        }

        $projects = Project::query()
            ->with([
                'semester:id,name,academic_year',
                'course:id,code,title',
                'supervisor:id,name,university_id',
                'students' => fn ($query) => $query
                    ->whereIn('users.id', $studentIds)
                    ->select('users.id', 'users.name', 'users.university_id'),
            ])
            ->where('semester_id', $semesterId)
            ->when($excludeProjectId !== null, fn (Builder $query) => $query->where('id', '!=', $excludeProjectId))
            ->whereHas('students', fn (Builder $query) => $query->whereIn('users.id', $studentIds))
            ->get();

        $assignments = [];
        foreach ($projects as $project) {
            foreach ($project->students as $student) {
                $assignments[$student->id][] = $project;
            }
        }

        return $assignments;
    }

    public static function firstExistingAssignmentForSemester(int $studentId, int $semesterId, ?int $excludeProjectId = null): ?Project
    {
        return self::existingAssignmentsForSemester([$studentId], $semesterId, $excludeProjectId)[$studentId][0] ?? null;
    }

    /**
     * @param array<int, int> $studentIds
     */
    public static function detachFromSemester(array $studentIds, int $semesterId, ?int $excludeProjectId = null): void
    {
        $studentIds = array_values(array_unique(array_filter($studentIds)));

        if (empty($studentIds)) {
            return;
        }

        Project::query()
            ->where('semester_id', $semesterId)
            ->when($excludeProjectId !== null, fn (Builder $query) => $query->where('id', '!=', $excludeProjectId))
            ->whereHas('students', fn (Builder $query) => $query->whereIn('users.id', $studentIds))
            ->get()
            ->each(fn (Project $project) => $project->students()->detach($studentIds));
    }

    public static function warningMessage(User $student, Project $existingProject, string $newProjectTitle): string
    {
        return "Student '{$student->university_id}' ({$student->name}) is already assigned to project "
            . self::projectDetails($existingProject)
            . ". Proceeding will move the student to project '{$newProjectTitle}'.";
    }

    public static function projectDetails(Project $project): string
    {
        $details = ["ID {$project->id}"];

        if ($project->semester) {
            $details[] = "semester '{$project->semester->name}'";
        }

        if ($project->course) {
            $details[] = "course '{$project->course->code}'";
        }

        if ($project->supervisor) {
            $details[] = "supervisor '{$project->supervisor->name} ({$project->supervisor->university_id})'";
        }

        return "'{$project->title}' (" . implode(', ', $details) . ')';
    }
}
