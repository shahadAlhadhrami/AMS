<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    protected string $view = 'filament.student.pages.dashboard';

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Student\Widgets\MyProjectWidget::class,
            \App\Filament\Student\Widgets\MarksAvailableWidget::class,
        ];
    }
}
