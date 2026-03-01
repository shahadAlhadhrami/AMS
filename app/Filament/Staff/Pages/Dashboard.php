<?php

namespace App\Filament\Staff\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    protected string $view = 'filament.staff.pages.dashboard';

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Staff\Widgets\PendingEvaluationsWidget::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        $widgets = [];
        $user = auth()->user();

        if ($user->hasRole('Supervisor')) {
            $widgets[] = \App\Filament\Staff\Widgets\SupervisedProjectsWidget::class;
        }

        if ($user->hasRole('Reviewer')) {
            $widgets[] = \App\Filament\Staff\Widgets\ReviewAssignmentsWidget::class;
        }

        return $widgets;
    }
}
