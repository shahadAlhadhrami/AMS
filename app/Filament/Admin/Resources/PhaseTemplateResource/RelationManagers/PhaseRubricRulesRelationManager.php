<?php

namespace App\Filament\Admin\Resources\PhaseTemplateResource\RelationManagers;

use App\Models\RubricFolder;
use App\Models\RubricTemplate;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
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
                    ->options(fn () => self::groupedTemplateOptions())
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $template = RubricTemplate::find($state);
                        if ($template) {
                            $set('max_marks', $template->total_marks);
                        }
                    })
                    ->required(),
                Forms\Components\Hidden::make('max_marks'),
                Forms\Components\Select::make('evaluator_role')
                    ->options([
                        'Supervisor' => 'Supervisor',
                        'Reviewer' => 'Reviewer',
                        'External' => 'External',
                    ])
                    ->live()
                    ->required(),
                Forms\Components\TextInput::make('fill_order')
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->label('Fill Order'),
                Forms\Components\Select::make('aggregation_method')
                    ->options([
                        'AVERAGE' => 'Average',
                        'WEIGHTED_AVERAGE' => 'Weighted Average',
                        'SUM' => 'Sum',
                        'MAX' => 'Max',
                    ])
                    ->default('AVERAGE')
                    ->required()
                    ->hidden(fn (Get $get) => $get('evaluator_role') !== 'Reviewer'),
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

    private static function groupedTemplateOptions(): array
    {
        $folders = RubricFolder::all(['id', 'parent_id', 'name'])->keyBy('id');

        $buildPath = function (int $id) use (&$buildPath, $folders): string {
            $folder = $folders[$id];
            if ($folder->parent_id === null) {
                return $folder->name;
            }
            return $buildPath($folder->parent_id) . ' / ' . $folder->name;
        };

        $grouped = [];
        $templates = RubricTemplate::all(['id', 'rubric_folder_id', 'name']);

        foreach ($templates as $template) {
            $group = $template->rubric_folder_id
                ? $buildPath($template->rubric_folder_id)
                : 'No Folder';
            $grouped[$group][$template->id] = $template->name;
        }

        ksort($grouped);

        return $grouped;
    }
}
