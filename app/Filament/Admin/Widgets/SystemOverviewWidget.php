<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Evaluation;
use App\Models\Project;
use App\Models\Semester;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeProjects = Project::whereHas('semester', fn ($q) => $q->where('is_active', true))->count();
        $activeSemesters = Semester::where('is_active', true)->count();
        $pendingEvaluations = Evaluation::whereHas('project.semester', fn ($q) => $q->where('is_active', true))
            ->whereIn('status', ['pending', 'draft'])
            ->count();
        $totalUsers = User::count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description(
                    'Students: ' . User::role('Student')->count()
                    . '  |  Staff: ' . (User::role('Supervisor')->count() + User::role('Reviewer')->count())
                )
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Active Semesters', $activeSemesters)
                ->icon('heroicon-o-calendar')
                ->color('info'),
            Stat::make('Active Projects', $activeProjects)
                ->icon('heroicon-o-briefcase')
                ->color('warning'),
            Stat::make('Awaiting Submission', $pendingEvaluations)
                ->description('Pending + Draft evaluations')
                ->icon('heroicon-o-clock')
                ->color('danger'),
        ];
    }
}
