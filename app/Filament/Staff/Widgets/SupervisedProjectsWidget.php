<?php

namespace App\Filament\Staff\Widgets;

use App\Filament\Staff\Pages\EvaluationForm;
use App\Filament\Staff\Pages\ProjectDetail;
use App\Models\Project;
use Filament\Actions;
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
                    ->with(['semester', 'course', 'phaseTemplate', 'students'])
                    ->withCount([
                        'students',
                        'evaluations',
                        'evaluations as submitted_evaluations_count' => fn ($q) => $q->where('status', 'submitted'),
                    ])
                    ->withExists([
                        'evaluations as has_pending_evaluation' => fn ($q) => $q
                            ->where('evaluator_id', auth()->id())
                            ->whereIn('status', ['pending', 'draft']),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('students_list')
                    ->label('Students')
                    ->getStateUsing(fn ($record) => $record->students->pluck('name')->join(', '))
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('semester.name')
                    ->label('Semester')
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.code')
                    ->label('Course')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phaseTemplate.name')
                    ->label('Phase')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'evaluating' => 'warning',
                        'completed'  => 'success',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('evaluation_progress')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => $record->submitted_evaluations_count.'/'.$record->evaluations_count),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'setup'      => 'Setup',
                        'evaluating' => 'Evaluating',
                        'completed'  => 'Completed',
                    ]),
            ])
            ->actions([
                Actions\Action::make('viewProject')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Project $record) => ProjectDetail::getUrl(['project' => $record->id])),
                Actions\Action::make('fillAssessment')
                    ->label('Fill Assessment')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn (Project $record) => $this->getEvaluationFormUrl($record))
                    ->visible(fn (Project $record) => $record->status === 'evaluating'
                        && $record->has_pending_evaluation
                    ),
            ])
            ->paginated(false);
    }

    protected function getEvaluationFormUrl(Project $record): string
    {
        $evaluation = $record->evaluations()
            ->where('evaluator_id', auth()->id())
            ->whereIn('status', ['pending', 'draft'])
            ->orderBy('id')
            ->first();

        return $evaluation
            ? EvaluationForm::getUrl(['evaluation' => $evaluation->id])
            : '#';
    }
}
