<?php

namespace App\Filament\Admin\Resources\ProjectResource\RelationManagers;

use App\Models\Project;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function table(Table $table): Table
    {
        return $table
            ->inverseRelationship('studentProjects')
            ->columns([
                Tables\Columns\TextColumn::make('university_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email'),
            ])
            ->headerActions([
                Actions\AttachAction::make()
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        return $query->role('Student');
                    })
                    ->recordTitle(fn (User $record): string => "{$record->name} ({$record->university_id})")
                    ->preloadRecordSelect()
                    ->form(fn (Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $project = $this->ownerRecord;

                                        // D3: Max 4 students per project
                                        if ($project->students()->count() >= 4) {
                                            $fail('This project already has 4 students (maximum).');

                                            return;
                                        }

                                        // D3: Student not in another project this semester
                                        $existsInSemester = Project::where('semester_id', $project->semester_id)
                                            ->where('id', '!=', $project->id)
                                            ->whereHas('students', function ($q) use ($value) {
                                                $q->where('users.id', $value);
                                            })
                                            ->exists();

                                        if ($existsInSemester) {
                                            $studentName = User::find($value)?->name ?? 'Student';
                                            $fail("{$studentName} is already assigned to another project in this semester.");
                                        }
                                    };
                                },
                            ]),
                    ]),
            ])
            ->actions([
                Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Actions\DetachBulkAction::make(),
            ]);
    }
}
