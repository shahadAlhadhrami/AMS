<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RubricTemplateResource\Pages;
use App\Filament\Admin\Resources\RubricTemplateResource\RelationManagers;
use App\Models\RubricFolder;
use App\Models\RubricTemplate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class RubricTemplateResource extends Resource
{
    protected static ?string $model = RubricTemplate::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string | \UnitEnum | null $navigationGroup = 'Template Pool';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('rubric_folder_id')
                    ->label('Folder')
                    ->options(fn () => self::getFolderOptions())
                    ->searchable()
                    ->nullable()
                    ->placeholder('— No folder —'),
                Forms\Components\Placeholder::make('total_marks')
                    ->label('Total Marks')
                    ->content(fn (?RubricTemplate $record): string => $record ? number_format((float) $record->total_marks, 2) : '0.00')
                    ->visibleOn('edit'),
                Forms\Components\Placeholder::make('version')
                    ->label('Version')
                    ->content(fn (?RubricTemplate $record): string => $record ? (string) $record->version : '1')
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('folder.name')
                    ->label('Folder')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('version')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_marks')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_locked')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->label('Locked'),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('parentTemplate.name')
                    ->label('Parent Template')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_locked')
                    ->label('Locked Status'),
                Tables\Filters\SelectFilter::make('rubric_folder_id')
                    ->label('Folder')
                    ->options(fn () => self::getFolderOptions())
                    ->placeholder('All Folders'),
                Tables\Filters\SelectFilter::make('created_by')
                    ->relationship('creator', 'name')
                    ->label('Created By')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make()
                    ->hidden(fn (RubricTemplate $record): bool => $record->is_locked || $record->created_by !== auth()->id()),
                Actions\Action::make('clone')
                    ->label('Clone')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (RubricTemplate $record) {
                        $newTemplate = static::cloneTemplate($record);

                        return redirect(static::getUrl('edit', ['record' => $newTemplate]));
                    }),
                Actions\Action::make('lock')
                    ->label('Lock')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Lock Rubric Template')
                    ->modalDescription('Locking this template will prevent further edits. Are you sure?')
                    ->action(function (RubricTemplate $record) {
                        $record->update(['is_locked' => true]);
                    })
                    ->hidden(fn (RubricTemplate $record): bool => $record->is_locked || $record->created_by !== auth()->id()),
                Actions\DeleteAction::make()
                    ->hidden(fn (RubricTemplate $record): bool => $record->is_locked || $record->created_by !== auth()->id()),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function cloneTemplate(RubricTemplate $original): RubricTemplate
    {
        $newTemplate = RubricTemplate::create([
            'name' => $original->name,
            'version' => $original->version + 1,
            'parent_template_id' => $original->id,
            'rubric_folder_id' => null,
            'total_marks' => $original->total_marks,
            'is_locked' => false,
            'created_by' => auth()->id(),
        ]);

        foreach ($original->deliverables as $deliverable) {
            $newDeliverable = $newTemplate->deliverables()->create([
                'title' => $deliverable->title,
                'max_marks' => $deliverable->max_marks,
                'sort_order' => $deliverable->sort_order,
            ]);

            foreach ($deliverable->criteria as $criterion) {
                $newCriterion = $newDeliverable->criteria()->create([
                    'rubric_template_id' => $newTemplate->id,
                    'title' => $criterion->title,
                    'description' => $criterion->description,
                    'max_score' => $criterion->max_score,
                    'is_individual' => $criterion->is_individual,
                    'sort_order' => $criterion->sort_order,
                ]);

                foreach ($criterion->scoreLevels as $scoreLevel) {
                    $newCriterion->scoreLevels()->create([
                        'label' => $scoreLevel->label,
                        'score_value' => $scoreLevel->score_value,
                        'description' => $scoreLevel->description,
                        'sort_order' => $scoreLevel->sort_order,
                    ]);
                }
            }
        }

        // Clone any orphan criteria (no deliverable) for backward compatibility
        foreach ($original->criteria()->whereNull('deliverable_id')->get() as $criterion) {
            $newCriterion = $newTemplate->criteria()->create([
                'title' => $criterion->title,
                'description' => $criterion->description,
                'max_score' => $criterion->max_score,
                'is_individual' => $criterion->is_individual,
                'sort_order' => $criterion->sort_order,
            ]);

            foreach ($criterion->scoreLevels as $scoreLevel) {
                $newCriterion->scoreLevels()->create([
                    'label' => $scoreLevel->label,
                    'score_value' => $scoreLevel->score_value,
                    'description' => $scoreLevel->description,
                    'sort_order' => $scoreLevel->sort_order,
                ]);
            }
        }

        return $newTemplate;
    }

    /**
     * Returns a flat options array for folder selects, with indentation for subfolders.
     */
    public static function getFolderOptions(?int $excludeId = null): array
    {
        $folders = RubricFolder::orderBy('name')->get()->keyBy('id');
        $options = [];

        $buildOptions = function (int|null $parentId, string $prefix) use (&$buildOptions, $folders, &$options, $excludeId): void {
            foreach ($folders->where('parent_id', $parentId) as $folder) {
                if ($folder->id === $excludeId) {
                    continue;
                }
                $options[$folder->id] = $prefix . $folder->name;
                $buildOptions($folder->id, $prefix . '— ');
            }
        };

        $buildOptions(null, '');

        return $options;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DeliverablesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRubricTemplates::route('/'),
            'create' => Pages\CreateRubricTemplate::route('/create'),
            'view' => Pages\ViewRubricTemplate::route('/{record}'),
            'edit' => Pages\EditRubricTemplate::route('/{record}/edit'),
        ];
    }
}
