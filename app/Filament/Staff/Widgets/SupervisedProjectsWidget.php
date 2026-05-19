<?php

namespace App\Filament\Staff\Widgets;

use App\Filament\Staff\Pages\EvaluationForm;
use App\Filament\Staff\Pages\ProjectDetail;
use App\Models\Evaluation;
use App\Models\Project;
use App\Services\EvaluationService;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SupervisedProjectsWidget extends TableWidget
{
    protected static ?string $heading = 'Projects I am Supervising';

    private const ACTIONABLE_EVALUATION_STATUSES = ['pending', 'draft'];

    private const EVALUATOR_ROLE = 'Supervisor';

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
                        'evaluations as supervisor_evaluations_count' => fn ($q) => $q
                            ->where('evaluator_id', auth()->id())
                            ->where('evaluator_role', self::EVALUATOR_ROLE),
                        'evaluations as submitted_supervisor_evaluations_count' => fn ($q) => $q
                            ->where('evaluator_id', auth()->id())
                            ->where('evaluator_role', self::EVALUATOR_ROLE)
                            ->where('status', 'submitted'),
                    ])
                    ->withExists([
                        'evaluations as has_pending_evaluation' => fn ($q) => $q
                            ->where('evaluator_id', auth()->id())
                            ->where('evaluator_role', self::EVALUATOR_ROLE)
                            ->whereIn('status', self::ACTIONABLE_EVALUATION_STATUSES),
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
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('evaluation_progress')
                    ->label('Supervisor Progress')
                    ->getStateUsing(fn ($record) => $record->submitted_supervisor_evaluations_count.'/'.$record->supervisor_evaluations_count),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'setup' => 'Setup',
                        'evaluating' => 'Evaluating',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Actions\Action::make('viewProject')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Project $record) => ProjectDetail::getUrl([
                        'project' => $record->id,
                        'context' => 'supervisor',
                    ], panel: 'staff')),
                Actions\Action::make('fillAssessment')
                    ->label('Fill Assessment')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn (Project $record) => $this->getEvaluationFormUrl($record))
                    ->visible(fn (Project $record) => $this->canFillSupervisorEvaluation($record)),
            ])
            ->paginated(false);
    }

    protected function getEvaluationFormUrl(Project $record): string
    {
        $evaluation = $this->getPendingSupervisorEvaluation($record);

        return $evaluation
            ? EvaluationForm::getUrl(['evaluation' => $evaluation->id], panel: 'staff')
            : '#';
    }

    protected function canFillSupervisorEvaluation(Project $record): bool
    {
        if ($record->status !== 'evaluating' || ! $record->has_pending_evaluation) {
            return false;
        }

        $evaluation = $this->getPendingSupervisorEvaluation($record);

        return $evaluation !== null
            && app(EvaluationService::class)->isFillOrderMet($evaluation);
    }

    protected function getPendingSupervisorEvaluation(Project $record): ?Evaluation
    {
        return $record->evaluations()
            ->where('evaluator_id', auth()->id())
            ->where('evaluator_role', self::EVALUATOR_ROLE)
            ->whereIn('status', self::ACTIONABLE_EVALUATION_STATUSES)
            ->orderBy('id')
            ->first();
    }
}
