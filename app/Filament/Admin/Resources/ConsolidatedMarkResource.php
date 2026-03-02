<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ConsolidatedMarkResource\Pages;
use App\Models\ConsolidatedMark;
use App\Models\GradingScale;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConsolidatedMarkResource extends Resource
{
    protected static ?string $model = ConsolidatedMark::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|\UnitEnum|null $navigationGroup = 'Grade Consolidation';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->hasRole('Coordinator') && ! $user->hasRole('Super Admin')) {
            $query->whereHas('project.semester.coordinators', function (Builder $q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.university_id')
                    ->label('University ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_calculated_score')
                    ->label('Calculated')
                    ->sortable(),
                Tables\Columns\TextColumn::make('override_score')
                    ->label('Override')
                    ->placeholder('--')
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_mark')
                    ->label('Final Mark')
                    ->getStateUsing(fn (ConsolidatedMark $record): string => number_format((float) ($record->override_score ?? $record->total_calculated_score), 2))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw('COALESCE(override_score, total_calculated_score) ' . $direction);
                    }),
                Tables\Columns\TextColumn::make('letter_grade')
                    ->label('Grade')
                    ->getStateUsing(function (ConsolidatedMark $record): string {
                        $finalScore = (float) ($record->override_score ?? $record->total_calculated_score);

                        return GradingScale::getLetterGrade($finalScore) ?? '--';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('semester')
                    ->relationship('project.semester', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Semester'),
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->preload()
                    ->label('Project'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\Action::make('overrideMark')
                    ->label('Override Mark')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('override_score')
                            ->numeric()
                            ->required()
                            ->label('Override Score'),
                        Forms\Components\Textarea::make('override_reason')
                            ->required()
                            ->label('Reason for Override'),
                    ])
                    ->action(function (ConsolidatedMark $record, array $data) {
                        $record->update([
                            'override_score' => $data['override_score'],
                            'override_reason' => $data['override_reason'],
                        ]);
                    })
                    ->visible(function (ConsolidatedMark $record): bool {
                        $semester = $record->project->semester;

                        return ! $semester->is_closed;
                    }),
            ]);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Student Information')
                    ->schema([
                        TextEntry::make('student.name')
                            ->label('Student'),
                        TextEntry::make('student.university_id')
                            ->label('University ID'),
                        TextEntry::make('project.title')
                            ->label('Project'),
                        TextEntry::make('project.semester.name')
                            ->label('Semester'),
                    ])
                    ->columns(2),
                Section::make('Score Breakdown')
                    ->schema([
                        RepeatableEntry::make('components')
                            ->schema([
                                TextEntry::make('source_label')
                                    ->label('Source'),
                                TextEntry::make('score')
                                    ->label('Score'),
                            ])
                            ->columns(2),
                        TextEntry::make('total_calculated_score')
                            ->label('Total Calculated Score'),
                    ]),
                Section::make('Override')
                    ->schema([
                        TextEntry::make('override_score')
                            ->label('Override Score'),
                        TextEntry::make('override_reason')
                            ->label('Reason')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn (ConsolidatedMark $record): bool => $record->override_score !== null),
                Section::make('Final Result')
                    ->schema([
                        TextEntry::make('final_score_display')
                            ->label('Final Score')
                            ->getStateUsing(fn (ConsolidatedMark $record): string => number_format((float) ($record->override_score ?? $record->total_calculated_score), 2)),
                        TextEntry::make('letter_grade_display')
                            ->label('Letter Grade')
                            ->getStateUsing(function (ConsolidatedMark $record): string {
                                $finalScore = (float) ($record->override_score ?? $record->total_calculated_score);

                                return GradingScale::getLetterGrade($finalScore) ?? '--';
                            }),
                        TextEntry::make('gpa_display')
                            ->label('GPA')
                            ->getStateUsing(function (ConsolidatedMark $record): string {
                                $finalScore = (float) ($record->override_score ?? $record->total_calculated_score);
                                $gpa = GradingScale::getGpa($finalScore);

                                return $gpa !== null ? number_format($gpa, 2) : '--';
                            }),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsolidatedMarks::route('/'),
            'view' => Pages\ViewConsolidatedMark::route('/{record}'),
        ];
    }
}
