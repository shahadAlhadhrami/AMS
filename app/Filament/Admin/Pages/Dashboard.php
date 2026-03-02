<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    protected string $view = 'filament.admin.pages.dashboard';

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\PendingEvaluationsWidget::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\ProjectStatusWidget::class,
            \App\Filament\Admin\Widgets\SubmissionProgressWidget::class,
            \App\Filament\Admin\Widgets\RecentActivityWidget::class,
        ];
    }
}
