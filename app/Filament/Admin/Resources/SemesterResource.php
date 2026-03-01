<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SemesterResource\Pages;
use App\Filament\Admin\Resources\SemesterResource\RelationManagers;
use App\Models\Semester;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SemesterResource extends Resource
{
    protected static ?string $model = Semester::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string | \UnitEnum | null $navigationGroup = 'Academic Setup';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->hasRole('Coordinator') && ! $user->hasRole('Super Admin')) {
            $query->whereHas('coordinators', function (Builder $q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return $query;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Fall 2026'),
                Forms\Components\TextInput::make('academic_year')
                    ->required()
                    ->maxLength(9)
                    ->placeholder('e.g., 2025-2026'),
                Forms\Components\DatePicker::make('start_date')
                    ->nullable(),
                Forms\Components\DatePicker::make('end_date')
                    ->nullable()
                    ->afterOrEqual('start_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academic_year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\IconColumn::make('is_closed')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->label('Closed'),
                Tables\Columns\TextColumn::make('projects_count')
                    ->counts('projects')
                    ->label('Projects'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\TernaryFilter::make('is_closed')
                    ->label('Closed Status'),
                Tables\Filters\SelectFilter::make('academic_year')
                    ->options(fn () => Semester::query()
                        ->distinct()
                        ->pluck('academic_year', 'academic_year')
                        ->toArray()
                    ),
            ])
            ->actions([
                Actions\Action::make('closeSemester')
                    ->label('Close')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Close Semester')
                    ->modalDescription('Closing this semester will prevent further changes to its projects and evaluations. This action cannot be undone. Are you sure?')
                    ->action(function (Semester $record) {
                        $record->update(['is_closed' => true]);
                    })
                    ->hidden(fn (Semester $record): bool => $record->is_closed),
                Actions\EditAction::make()
                    ->hidden(fn (Semester $record): bool => $record->is_closed),
                Actions\DeleteAction::make()
                    ->hidden(fn (Semester $record): bool => $record->is_closed),
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
            RelationManagers\CoordinatorsRelationManager::class,
            RelationManagers\ProjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSemesters::route('/'),
            'create' => Pages\CreateSemester::route('/create'),
            'edit' => Pages\EditSemester::route('/{record}/edit'),
        ];
    }
}
