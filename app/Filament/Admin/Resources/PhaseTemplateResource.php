<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PhaseTemplateResource\Pages;
use App\Filament\Admin\Resources\PhaseTemplateResource\RelationManagers;
use App\Models\PhaseTemplate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PhaseTemplateResource extends Resource
{
    protected static ?string $model = PhaseTemplate::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Template Pool';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_phase_marks')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->label('Total Phase Marks'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_phase_marks')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->label('Total Marks'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phase_rubric_rules_count')
                    ->counts('phaseRubricRules')
                    ->label('Rubric Rules'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\PhaseRubricRulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhaseTemplates::route('/'),
            'create' => Pages\CreatePhaseTemplate::route('/create'),
            'edit' => Pages\EditPhaseTemplate::route('/{record}/edit'),
        ];
    }
}
