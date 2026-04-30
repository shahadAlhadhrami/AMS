<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Evaluation;
use App\Models\Semester;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class SubmissionProgressWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Submission Progress by Semester';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $semesterQuery = Semester::where('is_active', true);

        $user = auth()->user();
        if ($user->hasRole('Coordinator') && ! $user->hasRole('Super Admin')) {
            $semesterQuery->whereHas('coordinators', function (Builder $q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $semesters = $semesterQuery->get();

        $labels = [];
        $data = [];

        foreach ($semesters as $semester) {
            $labels[] = $semester->name;

            $total = Evaluation::whereHas('project', fn (Builder $q) => $q->where('semester_id', $semester->id))->count();
            $submitted = Evaluation::whereHas('project', fn (Builder $q) => $q->where('semester_id', $semester->id))
                ->where('status', 'submitted')
                ->count();

            $data[] = $total > 0 ? round(($submitted / $total) * 100) : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Completion %',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Coordinator']) ?? false;
    }
}
