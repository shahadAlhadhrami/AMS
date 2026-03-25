<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    public function getHeaderWidgets(): array
    {
        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            return [
                \App\Filament\Admin\Widgets\SystemOverviewWidget::class,
            ];
        }

        // Coordinator
        return [
            \App\Filament\Admin\Widgets\PendingEvaluationsWidget::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            return [
                \App\Filament\Admin\Widgets\PendingEvaluationsWidget::class,
                \App\Filament\Admin\Widgets\ProjectStatusWidget::class,
                \App\Filament\Admin\Widgets\SubmissionProgressWidget::class,
                \App\Filament\Admin\Widgets\RecentActivityWidget::class,
            ];
        }

        // Coordinator: project table + charts scoped to their semesters
        return [
            \App\Filament\Admin\Widgets\CoordinatorProjectsWidget::class,
            \App\Filament\Admin\Widgets\ProjectStatusWidget::class,
            \App\Filament\Admin\Widgets\SubmissionProgressWidget::class,
        ];
    }
}
