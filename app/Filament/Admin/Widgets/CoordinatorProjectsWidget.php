<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Project;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CoordinatorProjectsWidget extends BaseWidget
{
    protected static ?string $heading = 'My Active Projects';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(
                Project::query()
                    ->whereHas('semester', fn (Builder $q) => $q->where('is_active', true))
                    ->when(
                        $user->hasRole('Coordinator') && ! $user->hasRole('Super Admin'),
                        fn (Builder $q) => $q->whereHas('semester.coordinators', fn (Builder $inner) => $inner->where('users.id', $user->id))
                    )
                    ->withCount(['evaluations', 'students'])
                    ->withCount(['evaluations as submitted_evaluations_count' => fn (Builder $q) => $q->where('status', 'submitted')])
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(35),
                Tables\Columns\TextColumn::make('semester.name')
                    ->label('Semester'),
                Tables\Columns\TextColumn::make('supervisor.name')
                    ->label('Supervisor'),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('Students')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'setup'      => 'gray',
                        'evaluating' => 'warning',
                        'completed'  => 'success',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('submitted_evaluations_count')
                    ->label('Submitted / Total Evals')
                    ->formatStateUsing(fn ($state, Project $record): string => $state . ' / ' . $record->evaluations_count)
                    ->alignCenter(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Project $record): string => \App\Filament\Admin\Resources\ProjectResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([10, 25])
            ->defaultSort('status');
    }
}
