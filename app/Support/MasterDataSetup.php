<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Department;
use App\Models\GradingScale;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MasterDataSetup
{
    public const DEFAULT_GRADING_SCALES = [
        ['letter_grade' => 'A', 'min_score' => 90.00, 'max_score' => 100.00, 'gpa_equivalent' => 4.00],
        ['letter_grade' => 'A-', 'min_score' => 85.00, 'max_score' => 89.99, 'gpa_equivalent' => 3.70],
        ['letter_grade' => 'B+', 'min_score' => 80.00, 'max_score' => 84.99, 'gpa_equivalent' => 3.30],
        ['letter_grade' => 'B', 'min_score' => 75.00, 'max_score' => 79.99, 'gpa_equivalent' => 3.00],
        ['letter_grade' => 'C+', 'min_score' => 70.00, 'max_score' => 74.99, 'gpa_equivalent' => 2.70],
        ['letter_grade' => 'C', 'min_score' => 65.00, 'max_score' => 69.99, 'gpa_equivalent' => 2.30],
        ['letter_grade' => 'D', 'min_score' => 60.00, 'max_score' => 64.99, 'gpa_equivalent' => 1.00],
        ['letter_grade' => 'F', 'min_score' => 0.00, 'max_score' => 59.99, 'gpa_equivalent' => 0.00],
    ];

    public static function isComplete(): bool
    {
        return Department::query()->exists()
            && Specialization::query()->exists()
            && Course::query()->exists()
            && GradingScale::query()->exists();
    }

    /**
     * @return array<int, string>
     */
    public static function missingLabels(): array
    {
        return array_values(array_filter([
            Department::query()->exists() ? null : 'Department',
            Specialization::query()->exists() ? null : 'Specialization',
            Course::query()->exists() ? null : 'Course',
            GradingScale::query()->exists() ? null : 'Grading scale',
        ]));
    }

    public static function shouldFocusNavigation(?User $user = null): bool
    {
        $user ??= auth()->user();

        return (bool) $user?->hasRole('Super Admin') && ! static::isComplete();
    }

    public static function ensureDefaultGradingScales(): void
    {
        if (GradingScale::query()->exists()) {
            return;
        }

        foreach (static::DEFAULT_GRADING_SCALES as $scale) {
            GradingScale::query()->create($scale);
        }
    }

    /**
     * @return array<int, array{id: int|null, letter_grade: string, min_score: float, max_score: float, gpa_equivalent: float}>
     */
    public static function gradingScaleRows(): array
    {
        $rows = GradingScale::query()
            ->orderByDesc('min_score')
            ->get(['id', 'letter_grade', 'min_score', 'max_score', 'gpa_equivalent']);

        if ($rows->isEmpty()) {
            return array_map(
                fn (array $scale): array => array_merge(['id' => null], $scale),
                static::DEFAULT_GRADING_SCALES
            );
        }

        return $rows->map(fn (GradingScale $scale): array => [
            'id' => $scale->id,
            'letter_grade' => $scale->letter_grade,
            'min_score' => (float) $scale->min_score,
            'max_score' => (float) $scale->max_score,
            'gpa_equivalent' => (float) $scale->gpa_equivalent,
        ])->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public static function syncGradingScales(array $rows): void
    {
        $rows = collect($rows)
            ->map(fn (array $row): array => [
                'id' => filled($row['id'] ?? null) ? (int) $row['id'] : null,
                'letter_grade' => trim((string) ($row['letter_grade'] ?? '')),
                'min_score' => (float) ($row['min_score'] ?? 0),
                'max_score' => (float) ($row['max_score'] ?? 0),
                'gpa_equivalent' => (float) ($row['gpa_equivalent'] ?? 0),
            ])
            ->filter(fn (array $row): bool => $row['letter_grade'] !== '')
            ->values()
            ->all();

        static::validateGradingScaleRows($rows);

        DB::transaction(function () use ($rows): void {
            $savedIds = [];

            foreach ($rows as $row) {
                $scale = $row['id']
                    ? GradingScale::query()->find($row['id'])
                    : null;

                $attributes = [
                    'letter_grade' => $row['letter_grade'],
                    'min_score' => $row['min_score'],
                    'max_score' => $row['max_score'],
                    'gpa_equivalent' => $row['gpa_equivalent'],
                ];

                if ($scale) {
                    $scale->update($attributes);
                } else {
                    $scale = GradingScale::query()->create($attributes);
                }

                $savedIds[] = $scale->id;
            }

            GradingScale::query()
                ->whereNotIn('id', $savedIds)
                ->get()
                ->each
                ->delete();
        });

        FilamentLookupCache::forgetGradingScales();
    }

    /**
     * @param  array<int, array{id: int|null, letter_grade: string, min_score: float, max_score: float, gpa_equivalent: float}>  $rows
     */
    private static function validateGradingScaleRows(array $rows): void
    {
        if ($rows === []) {
            throw ValidationException::withMessages([
                'data.grading_scales' => 'At least one grading scale row is required.',
            ]);
        }

        foreach ($rows as $row) {
            if ($row['min_score'] < 0 || $row['max_score'] > 100 || $row['min_score'] >= $row['max_score']) {
                throw ValidationException::withMessages([
                    'data.grading_scales' => 'Each grading scale row must stay within 0-100 and have a lower minimum than maximum.',
                ]);
            }
        }

        $sortedRows = collect($rows)->sortBy('min_score')->values();
        $previous = null;

        foreach ($sortedRows as $row) {
            if ($previous && $previous['max_score'] >= $row['min_score']) {
                throw ValidationException::withMessages([
                    'data.grading_scales' => "The {$previous['letter_grade']} range overlaps with {$row['letter_grade']}.",
                ]);
            }

            $previous = $row;
        }
    }
}
