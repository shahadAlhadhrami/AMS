<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EvaluationResource\Pages;
use App\Models\Evaluation;
use Filament\Actions;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Assessment Monitoring';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->hasRole('Coordinator') && ! $user->hasRole('Super Admin')) {
            $query->whereHas('project.semester.coordinators', function (Builder $q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('rubricTemplate.name')
                    ->label('Rubric')
                    ->sortable()
                    ->limit(25),
                Tables\Columns\TextColumn::make('evaluator.name')
                    ->label('Evaluator')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('evaluator_role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Supervisor' => 'info',
                        'Reviewer' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'draft' => 'warning',
                        'submitted' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('onBehalfOfUser.name')
                    ->label('Proxy By')
                    ->placeholder('--')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('unlockedByUser.name')
                    ->label('Unlocked By')
                    ->placeholder('--')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('semester')
                    ->relationship('project.semester', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Semester'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                    ]),
                Tables\Filters\SelectFilter::make('evaluator_role')
                    ->options([
                        'Supervisor' => 'Supervisor',
                        'Reviewer' => 'Reviewer',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\Action::make('unlock')
                    ->label('Unlock')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Unlock Evaluation')
                    ->modalDescription('This will allow the evaluator to edit their marks again. Are you sure?')
                    ->action(function (Evaluation $record) {
                        $record->update([
                            'status' => 'draft',
                            'unlocked_by' => auth()->id(),
                        ]);
                    })
                    ->visible(fn (Evaluation $record): bool => $record->status === 'submitted'),
                Actions\Action::make('proxyEntry')
                    ->label('Proxy Mark Entry')
                    ->icon('heroicon-o-user-circle')
                    ->color('info')
                    ->url(fn (Evaluation $record) => \App\Filament\Admin\Pages\ProxyEvaluationForm::getUrl(['evaluation' => $record->id]))
                    ->visible(fn (Evaluation $record): bool => in_array($record->status, ['pending', 'draft'])),
            ]);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Evaluation Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('project.title')
                            ->label('Project'),
                        Infolists\Components\TextEntry::make('rubricTemplate.name')
                            ->label('Rubric Template'),
                        Infolists\Components\TextEntry::make('evaluator.name')
                            ->label('Evaluator'),
                        Infolists\Components\TextEntry::make('evaluator_role')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Supervisor' => 'info',
                                'Reviewer' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'draft' => 'warning',
                                'submitted' => 'success',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('onBehalfOfUser.name')
                            ->label('Proxy By')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('unlockedByUser.name')
                            ->label('Unlocked By')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('general_feedback')
                            ->label('General Feedback')
                            ->columnSpanFull()
                            ->placeholder('No feedback provided.'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluations::route('/'),
            'view' => Pages\ViewEvaluation::route('/{record}'),
        ];
    }
}
