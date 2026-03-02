<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class ProjectStatusWidget extends ChartWidget
{
    protected ?string $heading = 'Project Status Distribution';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $query = Project::query();

        $user = auth()->user();
        if ($user->hasRole('Coordinator') && ! $user->hasRole('Super Admin')) {
            $query->whereHas('semester.coordinators', function (Builder $q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'datasets' => [
                [
                    'data' => [
                        $counts->get('setup', 0),
                        $counts->get('evaluating', 0),
                        $counts->get('completed', 0),
                    ],
                    'backgroundColor' => ['#9ca3af', '#f59e0b', '#10b981'],
                ],
            ],
            'labels' => ['Setup', 'Evaluating', 'Completed'],
        ];
    }
}
