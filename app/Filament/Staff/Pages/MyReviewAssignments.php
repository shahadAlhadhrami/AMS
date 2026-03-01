<?php

namespace App\Filament\Staff\Pages;

use App\Models\Evaluation;
use App\Services\EvaluationService;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyReviewAssignments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'My Review Assignments';

    protected static string|\UnitEnum|null $navigationGroup = 'Reviews';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.staff.pages.my-review-assignments';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Reviewer');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Evaluation::query()
                    ->where('evaluator_id', auth()->id())
                    ->where('evaluator_role', 'Reviewer')
                    ->with(['project.semester', 'project.phaseTemplate.phaseRubricRules', 'rubricTemplate'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.semester.name')
                    ->label('Semester'),
                Tables\Columns\TextColumn::make('rubricTemplate.name')
                    ->label('Rubric'),
                Tables\Columns\TextColumn::make('fill_order')
                    ->label('Fill Order')
                    ->getStateUsing(fn (Evaluation $record) => $this->getFillOrder($record)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'draft' => 'info',
                        'submitted' => 'success',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('fillAssessment')
                    ->label('Fill Assessment')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(fn (Evaluation $record) => EvaluationForm::getUrl(['evaluation' => $record->id]))
                    ->visible(fn (Evaluation $record) => in_array($record->status, ['pending', 'draft'])
                        && app(EvaluationService::class)->isFillOrderMet($record)
                    ),
                Tables\Actions\Action::make('viewAssessment')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Evaluation $record) => EvaluationForm::getUrl(['evaluation' => $record->id]))
                    ->visible(fn (Evaluation $record) => $record->status === 'submitted'),
                Tables\Actions\Action::make('viewProject')
                    ->label('Project Detail')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Evaluation $record) => ProjectDetail::getUrl(['project' => $record->project_id])),
            ]);
    }

    protected function getFillOrder(Evaluation $evaluation): ?int
    {
        return $evaluation->project->phaseTemplate
            ?->phaseRubricRules
            ->where('rubric_template_id', $evaluation->rubric_template_id)
            ->where('evaluator_role', $evaluation->evaluator_role)
            ->first()
            ?->fill_order;
    }
}
