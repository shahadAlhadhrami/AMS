<?php

namespace App\Filament\Admin\Resources\ProjectResource\RelationManagers;

use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewersRelationManager extends RelationManager
{
    protected static string $relationship = 'reviewers';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('university_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        $supervisorId = $this->ownerRecord->supervisor_id;

                        return $query->role('Reviewer')
                            ->when($supervisorId, fn (Builder $q) => $q->where('users.id', '!=', $supervisorId));
                    })
                    ->recordTitle(fn (User $record): string => "{$record->name} ({$record->university_id})")
                    ->preloadRecordSelect()
                    ->searchable()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $project = $this->ownerRecord;

                                        // D3: Reviewer cannot be same as supervisor
                                        if ((int) $value === (int) $project->supervisor_id) {
                                            $fail('The supervisor cannot also be a reviewer on this project.');
                                        }
                                    };
                                },
                            ]),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
