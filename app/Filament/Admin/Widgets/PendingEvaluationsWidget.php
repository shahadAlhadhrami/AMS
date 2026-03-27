<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Evaluation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class PendingEvaluationsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $baseQuery = Evaluation::query()
            ->whereHas('project.semester', fn (Builder $q) => $q->where('is_active', true));

        // Scope to coordinator's semesters if not Super Admin
        $user = auth()->user();
        if ($user->hasRole('Coordinator') && ! $user->hasRole('Super Admin')) {
            $baseQuery->whereHas('project.semester.coordinators', function (Builder $q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $pending = (clone $baseQuery)->where('status', 'pending')->count();
        $draft = (clone $baseQuery)->where('status', 'draft')->count();
        $submitted = (clone $baseQuery)->where('status', 'submitted')->count();

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

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Coordinator']) ?? false;
    }
}
