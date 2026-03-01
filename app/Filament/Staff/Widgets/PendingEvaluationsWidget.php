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

        $pending = Evaluation::where('evaluator_id', $userId)
            ->where('status', 'pending')
            ->count();

        $draft = Evaluation::where('evaluator_id', $userId)
            ->where('status', 'draft')
            ->count();

        $submitted = Evaluation::where('evaluator_id', $userId)
            ->where('status', 'submitted')
            ->count();

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
