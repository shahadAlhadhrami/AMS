<?php

namespace App\Filament\Admin\Resources\ProjectResource\Pages;

use App\Filament\Admin\Pages\ProxyEvaluationForm;
use App\Filament\Admin\Resources\ProjectResource;
use App\Models\ConsolidatedMark;
use App\Models\Evaluation;
use App\Models\GradingScale;
use App\Notifications\EvaluationUnlockedNotification;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('unlock_evaluation')
                ->label('Unlock an Evaluation')
                ->icon('heroicon-o-lock-open')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Unlock Evaluation')
                ->modalDescription('Select the evaluation to unlock, allowing the evaluator to re-edit their marks.')
                ->form([
                    \Filament\Forms\Components\Select::make('evaluation_id')
                        ->label('Evaluation')
                        ->options(function () {
                            return $this->record->evaluations()
                                ->where('status', 'submitted')
                                ->with('evaluator', 'rubricTemplate')
                                ->get()
                                ->mapWithKeys(fn (Evaluation $e) => [
                                    $e->id => "{$e->evaluator->name} — {$e->rubricTemplate->name}",
                                ]);
                        })
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $evaluation = Evaluation::findOrFail($data['evaluation_id']);
                    $evaluation->update([
                        'status' => 'draft',
                        'unlocked_by' => auth()->id(),
                    ]);
                    $evaluation->project->consolidatedMarks()->delete();
                    $evaluation->project->update(['status' => 'evaluating']);
                    $evaluation->evaluator->notify(new EvaluationUnlockedNotification($evaluation));

                    Notification::make()->title('Evaluation unlocked.')->success()->send();
                })
                ->visible(fn (): bool => $this->record->evaluations()->where('status', 'submitted')->exists()),
            Actions\Action::make('proxy_entry')
                ->label('Proxy Mark Entry')
                ->icon('heroicon-o-user-circle')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('evaluation_id')
                        ->label('Evaluation')
                        ->options(function () {
                            return $this->record->evaluations()
                                ->whereIn('status', ['pending', 'draft'])
                                ->with('evaluator', 'rubricTemplate')
                                ->get()
                                ->mapWithKeys(fn (Evaluation $e) => [
                                    $e->id => "{$e->evaluator->name} — {$e->rubricTemplate->name} ({$e->status})",
                                ]);
                        })
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect(ProxyEvaluationForm::getUrl(['evaluation' => $data['evaluation_id']]));
                })
                ->visible(fn (): bool => $this->record->evaluations()->whereIn('status', ['pending', 'draft'])->exists()),
            Actions\Action::make('override_mark')
                ->label('Override Mark')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->form([
                    Forms\Components\Select::make('mark_id')
                        ->label('Student')
                        ->options(function () {
                            return $this->record->consolidatedMarks()
                                ->with('student')
                                ->get()
                                ->mapWithKeys(fn (ConsolidatedMark $m) => [
                                    $m->id => "{$m->student->name} ({$m->student->university_id})",
                                ]);
                        })
                        ->required(),
                    Forms\Components\TextInput::make('override_score')
                        ->numeric()
                        ->required()
                        ->label('Override Score'),
                    Forms\Components\Textarea::make('override_reason')
                        ->required()
                        ->label('Reason for Override'),
                ])
                ->action(function (array $data): void {
                    $mark = ConsolidatedMark::findOrFail($data['mark_id']);
                    $mark->update([
                        'override_score' => $data['override_score'],
                        'override_reason' => $data['override_reason'],
                    ]);
                    Notification::make()->title('Mark overridden.')->success()->send();
                })
                ->visible(fn (): bool => $this->record->consolidatedMarks()->exists()),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Project Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')
                            ->columnSpan(2),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'setup'      => 'gray',
                                'evaluating' => 'warning',
                                'completed'  => 'success',
                                default      => 'gray',
                            }),
                        TextEntry::make('semester.name')
                            ->label('Semester'),
                        TextEntry::make('course.code')
                            ->label('Course'),
                        TextEntry::make('specialization.name')
                            ->label('Specialization'),
                        TextEntry::make('supervisor.name')
                            ->label('Supervisor'),
                        TextEntry::make('phaseTemplate.name')
                            ->label('Phase Template'),
                        TextEntry::make('previousPhaseProject.title')
                            ->label('Previous Phase Project')
                            ->placeholder('—'),
                    ]),

                Section::make('Rubric Assignments')
                    ->description('Rubric rules configured for this phase template.')
                    ->schema([
                        RepeatableEntry::make('phaseTemplate.phaseRubricRules')
                            ->label('')
                            ->schema([
                                TextEntry::make('rubricTemplate.name')
                                    ->label('Rubric'),
                                TextEntry::make('evaluator_role')
                                    ->label('Role')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Supervisor' => 'info',
                                        'Reviewer' => 'warning',
                                        default => 'gray',
                                    }),
                                TextEntry::make('fill_order')
                                    ->label('Fill Order'),
                                TextEntry::make('rubricTemplate.total_marks')
                                    ->label('Max Marks'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Evaluation Status')
                    ->schema([
                        RepeatableEntry::make('evaluations')
                            ->label('')
                            ->schema([
                                TextEntry::make('evaluator.name')
                                    ->label('Evaluator'),
                                TextEntry::make('evaluator_role')
                                    ->label('Role')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Supervisor' => 'info',
                                        'Reviewer'   => 'warning',
                                        default      => 'gray',
                                    }),
                                TextEntry::make('rubricTemplate.name')
                                    ->label('Rubric'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending'   => 'gray',
                                        'draft'     => 'warning',
                                        'submitted' => 'success',
                                        default     => 'gray',
                                    }),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Consolidated Marks')
                    ->schema([
                        RepeatableEntry::make('consolidatedMarks')
                            ->label('')
                            ->schema([
                                TextEntry::make('student.name')
                                    ->label('Student'),
                                TextEntry::make('student.university_id')
                                    ->label('ID'),
                                TextEntry::make('total_calculated_score')
                                    ->label('Calculated Score')
                                    ->numeric(decimalPlaces: 2),
                                TextEntry::make('override_score')
                                    ->label('Override Score')
                                    ->numeric(decimalPlaces: 2)
                                    ->placeholder('—'),
                                TextEntry::make('override_reason')
                                    ->label('Override Reason')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                                TextEntry::make('final_mark')
                                    ->label('Final Mark')
                                    ->getStateUsing(fn (ConsolidatedMark $record): string => number_format((float) ($record->override_score ?? $record->total_calculated_score), 2)),
                                TextEntry::make('grade')
                                    ->label('Grade')
                                    ->getStateUsing(function (ConsolidatedMark $record): string {
                                        $score = (float) ($record->override_score ?? $record->total_calculated_score);

                                        return GradingScale::getLetterGrade($score) ?? '--';
                                    }),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }

    public function getRelationManagers(): array
    {
        return [
            \App\Filament\Admin\Resources\ProjectResource\RelationManagers\StudentsRelationManager::class,
            \App\Filament\Admin\Resources\ProjectResource\RelationManagers\ReviewersRelationManager::class,
        ];
    }
}
