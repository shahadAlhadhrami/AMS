<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class CriteriaRelationManager extends RelationManager
{
    protected static string $relationship = 'criteria';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('max_score')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->label('Max Score'),
                Forms\Components\Toggle::make('is_individual')
                    ->label('Individual scoring per student')
                    ->default(false),
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_score')
                    ->numeric(decimalPlaces: 2)
                    ->label('Max Score'),
                Tables\Columns\IconColumn::make('is_individual')
                    ->boolean()
                    ->label('Individual'),
                Tables\Columns\TextColumn::make('score_levels_count')
                    ->counts('scoreLevels')
                    ->label('Score Levels'),
            ])
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
