<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Concerns\HidesDuringMasterDataSetup;
use App\Filament\Admin\Resources\ProjectResource\Pages;
use App\Filament\Admin\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use App\Support\FilamentLookupCache;
use Closure;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    use HidesDuringMasterDataSetup;

    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'Academic Setup';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['semester', 'course', 'specialization', 'supervisor']);
        $user = auth()->user();

        if ($user && $user->hasRole('Coordinator') && ! $user->hasRole('Super Admin')) {
            $query->whereHas('semester.coordinators', function (Builder $q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return $query;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema(static::projectFormComponents());
    }

    public static function projectFormComponents(
        ?Closure $semesterOptions = null,
        ?Closure $courseOptions = null,
        ?Closure $phaseTemplateOptions = null,
        ?Closure $specializationOptions = null,
        ?Closure $supervisorOptions = null,
        ?Closure $projectOptions = null,
        mixed $semesterDefault = null,
        mixed $phaseTemplateDefault = null,
        bool|Closure $lockSemester = false,
        bool|Closure $lockPhaseTemplate = false,
    ): array {
        $resolveDefault = fn (mixed $default): mixed => $default instanceof Closure ? $default() : $default;

        return [
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('semester_id')
                ->options($semesterOptions ?? fn (): array => FilamentLookupCache::semesterOptions())
                ->default(fn (): mixed => $resolveDefault($semesterDefault))
                ->disabled($lockSemester)
                ->dehydrated()
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('course_id')
                ->options($courseOptions ?? fn (): array => FilamentLookupCache::courseOptions())
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('phase_template_id')
                ->options($phaseTemplateOptions ?? fn (): array => FilamentLookupCache::phaseTemplateOptions())
                ->default(fn (): mixed => $resolveDefault($phaseTemplateDefault))
                ->disabled($lockPhaseTemplate)
                ->dehydrated()
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('specialization_id')
                ->options($specializationOptions ?? fn (): array => FilamentLookupCache::specializationOptions())
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('supervisor_id')
                ->label('Supervisor')
                ->options($supervisorOptions ?? fn (): array => FilamentLookupCache::supervisorOptions())
                ->searchable()
                ->required(),
            Forms\Components\Select::make('previous_phase_project_id')
                ->label('Previous Phase Project')
                ->options($projectOptions ?? fn (): array => FilamentLookupCache::projectOptions())
                ->searchable()
                ->nullable(),
            Forms\Components\Select::make('status')
                ->options([
                    'setup' => 'Setup',
                    'evaluating' => 'Evaluating',
                    'completed' => 'Completed',
                ])
                ->default('setup')
                ->required(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('semester.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.code')
                    ->label('Course')
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialization.name')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('supervisor.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'setup' => 'gray',
                        'evaluating' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Students'),
                Tables\Columns\TextColumn::make('reviewers_count')
                    ->counts('reviewers')
                    ->label('Reviewers'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('semester_id')
                    ->options(fn (): array => FilamentLookupCache::semesterOptions())
                    ->searchable()
                    ->preload()
                    ->label('Semester'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'setup' => 'Setup',
                        'evaluating' => 'Evaluating',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('course_id')
                    ->options(fn (): array => FilamentLookupCache::courseOptions())
                    ->label('Course'),
                Tables\Filters\SelectFilter::make('supervisor_id')
                    ->options(fn (): array => FilamentLookupCache::supervisorOptions())
                    ->searchable()
                    ->preload()
                    ->label('Supervisor'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->hidden(fn (Project $record): bool => $record->status !== 'setup'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('changeStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'setup' => 'Setup',
                                    'evaluating' => 'Evaluating',
                                    'completed' => 'Completed',
                                ])
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data): void {
                            $records->each(fn (Project $record) => $record->update(['status' => $data['status']]));
                        })
                        ->deselectRecordsAfterCompletion(),
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
            RelationManagers\ReviewersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
