<?php

namespace App\Filament\Student\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyProjectWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $student = auth()->user();
        $project = $student->studentProjects()
            ->with(['supervisor', 'students', 'semester'])
            ->latest('projects.created_at')
            ->first();

        if (! $project) {
            return [
                Stat::make('Project', 'No project assigned')
                    ->icon('heroicon-o-information-circle')
                    ->color('gray'),
            ];
        }

        $teammates = $project->students
            ->where('id', '!=', $student->id)
            ->pluck('name')
            ->join(', ') ?: 'None';

        return [
            Stat::make('Project', $project->title)
                ->icon('heroicon-o-academic-cap'),
            Stat::make('Supervisor', $project->supervisor?->name ?? 'Not assigned')
                ->icon('heroicon-o-user'),
            Stat::make('Teammates', $teammates)
                ->icon('heroicon-o-user-group'),
        ];
    }
}
