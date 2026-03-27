<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Evaluation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ReviewAssignmentsWidget extends TableWidget
{
    protected static ?string $heading = 'My Review Assignments';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->hasRole('Reviewer/Supervisor');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Evaluation::query()
                    ->where('evaluator_id', auth()->id())
                    ->where('evaluator_role', 'Reviewer')
                    ->with(['project.semester', 'rubricTemplate'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->limit(40),
                Tables\Columns\TextColumn::make('project.semester.name')
                    ->label('Semester'),
                Tables\Columns\TextColumn::make('rubricTemplate.name')
                    ->label('Rubric'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'draft' => 'info',
                        'submitted' => 'success',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
