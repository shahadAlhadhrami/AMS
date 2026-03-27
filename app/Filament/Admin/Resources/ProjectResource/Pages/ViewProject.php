<?php

namespace App\Filament\Admin\Resources\ProjectResource\Pages;

use App\Filament\Admin\Resources\ProjectResource;
use App\Models\ConsolidatedMark;
use App\Models\Evaluation;
use App\Models\GradingScale;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry\TextEntrySize;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                Grid::make(3)->schema([
                    Group::make()->columnSpan(2)->schema([
                        Section::make('Project Details')
                        ->schema([
                            TextEntry::make('title')
                                ->hiddenLabel()
                                ->size('lg')
                                ->weight('bold')
                                ->columnSpanFull(),

                            TextEntry::make('status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'setup'      => 'gray',
                                    'evaluating' => 'warning',
                                    'completed'  => 'success',
                                    default      => 'gray',
                                }),
                            TextEntry::make('semester.name')
                                ->label('Semester')
                                ->icon('heroicon-o-calendar')
                                ->inlineLabel(),
                            TextEntry::make('course.code')
                                ->label('Course')
                                ->icon('heroicon-o-book-open')
                                ->inlineLabel()
                                ->placeholder('—'),
                            TextEntry::make('specialization.name')
                                ->label('Specialization')
                                ->icon('heroicon-o-academic-cap')
                                ->inlineLabel(),
                            TextEntry::make('supervisor.name')
                                ->label('Supervisor')
                                ->icon('heroicon-o-user')
                                ->inlineLabel(),
                            TextEntry::make('phaseTemplate.name')
                                ->label('Phase')
                                ->icon('heroicon-o-clipboard-document-list')
                                ->inlineLabel(),
                            TextEntry::make('previousPhaseProject.title')
                                ->label('Previous Phase')
                                ->icon('heroicon-o-arrow-uturn-left')
                                ->inlineLabel()
                                ->placeholder('—'),
                        ])
                        ->columns(2)
                    ]),

                    Group::make()->columnSpan(1)->schema([
                        Section::make('Rubric Assignments')
                        ->description('Rules configured for this phase.')
                        ->schema([
                            RepeatableEntry::make('phaseTemplate.phaseRubricRules')
                                ->hiddenLabel()
                                ->schema([
                                    TextEntry::make('rubricTemplate.name')
                                        ->hiddenLabel()
                                        ->weight('bold'),
                                    TextEntry::make('evaluator_role')
                                        ->hiddenLabel()
                                        ->badge()
                                        ->formatStateUsing(fn (string $state, $record): string => $state . ' • ' . rtrim(rtrim(number_format($record->rubricTemplate->total_marks, 2), '0'), '.') . ' Marks')
                                        ->color(fn ($record): string => match ($record->evaluator_role) {
                                            'Supervisor' => 'info',
                                            'Reviewer' => 'warning',
                                            default => 'gray',
                                        }),
                                ])
                                ->columns(1)
                                ->contained(true),
                        ])
                    ]),
                ]),
            ])->columns(1);
    }

    public function getRelationManagers(): array
    {
        return [
            \App\Filament\Admin\Resources\ProjectResource\RelationManagers\StudentsRelationManager::class,
            \App\Filament\Admin\Resources\ProjectResource\RelationManagers\ReviewersRelationManager::class,
            \App\Filament\Admin\Resources\ProjectResource\RelationManagers\ConsolidatedMarksRelationManager::class,
        ];
    }
}
