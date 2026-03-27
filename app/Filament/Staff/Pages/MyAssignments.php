<?php

namespace App\Filament\Staff\Pages;

use Filament\Pages\Page;

class MyAssignments extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'My Assignments';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.staff.pages.my-assignments';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Reviewer/Supervisor');
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Staff\Widgets\MyAssignmentsSupervisedWidget::class,
            \App\Filament\Staff\Widgets\MyAssignmentsReviewWidget::class,
        ];
    }
}
