<?php

namespace App\Filament\Admin\Resources\ProjectResource\RelationManagers;

use App\Models\ConsolidatedMark;
use App\Models\GradingScale;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConsolidatedMarksRelationManager extends RelationManager
{
    protected static string $relationship = 'consolidatedMarks';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('student.name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student.name')
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.university_id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_calculated_score')
                    ->label('Calculated Score')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('override_score')
                    ->label('Override Score')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('final_mark')
                    ->label('Final Mark')
                    ->getStateUsing(fn (ConsolidatedMark $record): string => number_format((float) ($record->override_score ?? $record->total_calculated_score), 2)),
                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->getStateUsing(function (ConsolidatedMark $record): string {
                        $score = (float) ($record->override_score ?? $record->total_calculated_score);
                        return GradingScale::getLetterGrade($score) ?? '--';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A+', 'A', 'A-' => 'success',
                        'B+', 'B', 'B-' => 'info',
                        'C+', 'C', 'C-' => 'warning',
                        'D+', 'D' => 'warning',
                        'F' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
