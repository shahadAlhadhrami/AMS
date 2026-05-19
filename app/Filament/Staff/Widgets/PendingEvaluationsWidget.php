<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Evaluation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingEvaluationsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        $counts = Evaluation::query()
            ->where('evaluator_id', $userId)
            ->whereHas('project')
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $pending = (int) $counts->get('pending', 0);
        $draft = (int) $counts->get('draft', 0);
        $submitted = (int) $counts->get('submitted', 0);

        return [
            Stat::make('Pending Evaluations', $pending)
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Draft Evaluations', $draft)
                ->icon('heroicon-o-pencil-square')
                ->color('info'),
            Stat::make('Submitted Evaluations', $submitted)
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
