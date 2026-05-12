<?php

namespace App\Filament\Staff\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static bool $shouldRegisterNavigation = false;

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

        if ($user->hasRole('Reviewer/Supervisor')) {
            $widgets[] = \App\Filament\Staff\Widgets\SupervisedProjectsWidget::class;
            $widgets[] = \App\Filament\Staff\Widgets\ReviewAssignmentsWidget::class;
        }

        return $widgets;
    }
}
