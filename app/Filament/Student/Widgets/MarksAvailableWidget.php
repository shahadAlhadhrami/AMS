<?php

namespace App\Filament\Student\Widgets;

use App\Models\ConsolidatedMark;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarksAvailableWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $student = auth()->user();
        $hasMarks = ConsolidatedMark::where('student_id', $student->id)->exists();

        return [
            Stat::make('Consolidated Marks', $hasMarks ? 'Available' : 'Not yet finalized')
                ->icon($hasMarks ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                ->color($hasMarks ? 'success' : 'warning'),
        ];
    }
}
