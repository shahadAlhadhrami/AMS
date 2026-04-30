<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Project;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SupervisedProjectsWidget extends TableWidget
{
    protected static ?string $heading = 'Projects I am Supervising';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->hasRole('Reviewer/Supervisor');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Project::query()
                    ->where('supervisor_id', auth()->id())
                    ->where('status', '!=', 'setup')
                    ->withCount([
                        'students',
                        'evaluations',
                        'evaluations as submitted_evaluations_count' => fn ($q) => $q->where('status', 'submitted'),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('semester.name')
                    ->label('Semester'),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('Students'),
                Tables\Columns\TextColumn::make('evaluation_progress')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => $record->submitted_evaluations_count.'/'.$record->evaluations_count),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'evaluating' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
