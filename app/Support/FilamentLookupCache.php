<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Department;
use App\Models\GradingScale;
use App\Models\PhaseTemplate;
use App\Models\Project;
use App\Models\RubricFolder;
use App\Models\Semester;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class FilamentLookupCache
{
    private const TTL_SECONDS = 600;

    public static function pendingCoordinatorApprovals(): int
    {
        return self::remember('pending_coordinator_approvals', fn (): int => User::unapproved()->count());
    }

    public static function forgetPendingCoordinatorApprovals(): void
    {
        Cache::forget(self::key('pending_coordinator_approvals'));
    }

    /**
     * @return array<int, string>
     */
    public static function roleOptions(bool $includePrivileged = true): array
    {
        return self::remember(
            'roles.'.($includePrivileged ? 'all' : 'restricted'),
            function () use ($includePrivileged): array {
                return Role::query()
                    ->when(! $includePrivileged, fn ($query) => $query->whereNotIn('name', ['Super Admin', 'Coordinator']))
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all();
            },
        );
    }

    /**
     * @return array<int, string>
     */
    public static function specializationOptions(): array
    {
        return self::remember('specializations', fn (): array => Specialization::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all());
    }

    /**
     * @return array<int, string>
     */
    public static function departmentOptions(): array
    {
        return self::remember('departments', fn (): array => Department::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all());
    }

    /**
     * @return array<int, string>
     */
    public static function semesterOptions(): array
    {
        return self::remember('semesters', fn (): array => Semester::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all());
    }

    /**
     * @return array<string, string>
     */
    public static function academicYearOptions(): array
    {
        return self::remember('academic_years', fn (): array => Semester::query()
            ->distinct()
            ->orderBy('academic_year')
            ->pluck('academic_year', 'academic_year')
            ->all());
    }

    /**
     * @return array<int, string>
     */
    public static function courseOptions(): array
    {
        return self::remember('courses', fn (): array => Course::query()
            ->orderBy('title')
            ->get(['id', 'code', 'title'])
            ->mapWithKeys(fn (Course $course): array => [
                $course->id => "{$course->code} - {$course->title}",
            ])
            ->all());
    }

    /**
     * @return array<int, string>
     */
    public static function phaseTemplateOptions(): array
    {
        return self::remember('phase_templates', fn (): array => PhaseTemplate::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all());
    }

    /**
     * @return array<int, string>
     */
    public static function supervisorOptions(): array
    {
        return self::remember('supervisors', fn (): array => User::role('Reviewer/Supervisor')
            ->orderBy('name')
            ->get(['id', 'name', 'university_id'])
            ->mapWithKeys(fn (User $user): array => [
                $user->id => "{$user->name} ({$user->university_id})",
            ])
            ->all());
    }

    /**
     * @return array<int, string>
     */
    public static function userNameOptions(): array
    {
        return self::remember('user_names', fn (): array => User::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all());
    }

    /**
     * @return array<int, string>
     */
    public static function projectOptions(): array
    {
        return self::remember('projects', fn (): array => Project::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all());
    }

    /**
     * @return array<int, string>
     */
    public static function rubricFolderOptions(?int $excludeId = null): array
    {
        $folders = self::remember('rubric_folders', fn (): array => RubricFolder::query()
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name'])
            ->map(fn (RubricFolder $folder): array => [
                'id' => $folder->id,
                'parent_id' => $folder->parent_id,
                'name' => $folder->name,
            ])
            ->all());

        $options = [];

        $buildOptions = function (?int $parentId, string $prefix) use (&$buildOptions, $folders, &$options, $excludeId): void {
            foreach ($folders as $folder) {
                if ($folder['parent_id'] !== $parentId || $folder['id'] === $excludeId) {
                    continue;
                }

                $options[$folder['id']] = $prefix.$folder['name'];
                $buildOptions($folder['id'], $prefix.'- ');
            }
        };

        $buildOptions(null, '');

        return $options;
    }

    /**
     * @return array<int, array{min_score: float, max_score: float, letter_grade: string, gpa_equivalent: float}>
     */
    public static function gradingScales(): array
    {
        return self::remember('grading_scales', fn (): array => GradingScale::query()
            ->orderByDesc('min_score')
            ->get(['min_score', 'max_score', 'letter_grade', 'gpa_equivalent'])
            ->map(fn (GradingScale $scale): array => [
                'min_score' => (float) $scale->min_score,
                'max_score' => (float) $scale->max_score,
                'letter_grade' => $scale->letter_grade,
                'gpa_equivalent' => (float) $scale->gpa_equivalent,
            ])
            ->all());
    }

    public static function forgetGradingScales(): void
    {
        Cache::forget(self::key('grading_scales'));
    }

    public static function forgetRubricFolders(): void
    {
        Cache::forget(self::key('rubric_folders'));
    }

    private static function remember(string $key, callable $callback): mixed
    {
        return Cache::remember(self::key($key), now()->addSeconds(self::TTL_SECONDS), $callback);
    }

    private static function key(string $key): string
    {
        return "filament_lookup.{$key}";
    }
}
