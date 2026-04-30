<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProjectResource\Pages;
use App\Filament\Admin\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-briefcase';

    protected static string | \UnitEnum | null $navigationGroup = 'Academic Setup';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
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
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('semester_id')
                    ->relationship('semester', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'title')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->title}")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('phase_template_id')
                    ->relationship('phaseTemplate', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('specialization_id')
                    ->relationship('specialization', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('supervisor_id')
                    ->label('Supervisor')
                    ->options(function () {
                        return User::role('Reviewer/Supervisor')->get()
                            ->mapWithKeys(fn (User $user) => [
                                $user->id => "{$user->name} ({$user->university_id})",
                            ]);
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('previous_phase_project_id')
                    ->label('Previous Phase Project')
                    ->relationship('previousPhaseProject', 'title')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->relationship('semester', 'name')
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
                    ->relationship('course', 'title')
                    ->label('Course'),
                Tables\Filters\SelectFilter::make('supervisor_id')
                    ->relationship('supervisor', 'name')
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
