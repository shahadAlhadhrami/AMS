<?php

namespace App\Filament\Admin\Resources\PhaseTemplateResource\RelationManagers;

use App\Models\RubricTemplate;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PhaseRubricRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'phaseRubricRules';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('rubric_template_id')
                    ->label('Rubric Template')
                    ->options(
                        RubricTemplate::where('is_locked', true)
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('evaluator_role')
                    ->options([
                        'Supervisor' => 'Supervisor',
                        'Reviewer' => 'Reviewer',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('fill_order')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->label('Fill Order'),
                Forms\Components\TextInput::make('max_marks')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->label('Max Marks'),
                Forms\Components\Select::make('aggregation_method')
                    ->options([
                        'AVERAGE' => 'Average',
                        'WEIGHTED_AVERAGE' => 'Weighted Average',
                        'SUM' => 'Sum',
                        'MAX' => 'Max',
                    ])
                    ->default('AVERAGE')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rubricTemplate.name')
                    ->label('Rubric Template')
                    ->sortable(),
                Tables\Columns\TextColumn::make('evaluator_role')
                    ->label('Evaluator Role'),
                Tables\Columns\TextColumn::make('fill_order')
                    ->sortable()
                    ->label('Fill Order'),
                Tables\Columns\TextColumn::make('max_marks')
                    ->numeric(decimalPlaces: 2)
                    ->label('Max Marks'),
                Tables\Columns\TextColumn::make('aggregation_method')
                    ->badge()
                    ->label('Aggregation'),
            ])
            ->defaultSort('fill_order')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
