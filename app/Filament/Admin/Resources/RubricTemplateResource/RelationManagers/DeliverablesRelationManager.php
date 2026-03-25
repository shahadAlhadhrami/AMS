<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DeliverablesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliverables';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Deliverable Title')
                    ->placeholder('e.g., Project Analysis'),
                Forms\Components\TextInput::make('max_marks')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->label('Max Marks'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Sort Order'),
                Forms\Components\Repeater::make('criteria')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Component/Criterion Title')
                            ->placeholder('e.g., Related Literature Study'),
                        Forms\Components\Textarea::make('description')
                            ->nullable()
                            ->rows(2),
                        Forms\Components\TextInput::make('max_score')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->label('Max Score'),
                        Forms\Components\Toggle::make('is_individual')
                            ->label('Individual scoring per student')
                            ->default(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort Order'),
                        Forms\Components\Repeater::make('scoreLevels')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Excellent'),
                                Forms\Components\TextInput::make('score_value')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->label('Score Value'),
                                Forms\Components\TextInput::make('percentage_range')
                                    ->nullable()
                                    ->label('Percentage Range')
                                    ->placeholder('e.g., 90-100%'),
                                Forms\Components\Textarea::make('description')
                                    ->nullable()
                                    ->rows(2),
                            ])
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->defaultItems(0)
                            ->columnSpanFull()
                            ->addActionLabel('Add Score Level'),
                    ])
                    ->orderColumn('sort_order')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->defaultItems(0)
                    ->columnSpanFull()
                    ->addActionLabel('Add Criterion'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label('Deliverable'),
                Tables\Columns\TextColumn::make('max_marks')
                    ->numeric(decimalPlaces: 2)
                    ->label('Max Marks'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('criteria_count')
                    ->counts('criteria')
                    ->label('Criteria'),
            ])
            ->reorderable('sort_order')
            ->headerActions([
                Actions\CreateAction::make()
                    ->hidden(fn (): bool => $this->ownerRecord->is_locked),
            ])
            ->actions([
                Actions\EditAction::make()
                    ->hidden(fn (): bool => $this->ownerRecord->is_locked),
                Actions\DeleteAction::make()
                    ->hidden(fn (): bool => $this->ownerRecord->is_locked),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->hidden(fn (): bool => $this->ownerRecord->is_locked),
                ]),
            ]);
    }
}
