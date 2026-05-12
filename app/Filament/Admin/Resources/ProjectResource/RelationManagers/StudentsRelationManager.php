<?php

namespace App\Filament\Admin\Resources\ProjectResource\RelationManagers;

use App\Models\Project;
use App\Models\User;
use App\Support\StudentProjectReassignment;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

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
                            ->live(),
                        Forms\Components\Placeholder::make('assignment_warning')
                            ->label('Assignment warning')
                            ->content(fn (Get $get): HtmlString => $this->assignmentWarning((int) ($get('recordId') ?? 0)))
                            ->visible(fn (Get $get): bool => $this->hasAssignmentWarning((int) ($get('recordId') ?? 0))),
                    ])
                    ->modalSubmitActionLabel('Attach / Move Student')
                    ->before(function (array $data): void {
                        $studentId = (int) ($data['recordId'] ?? 0);

                        if (! $studentId) {
                            return;
                        }

                        /** @var Project $project */
                        $project = $this->ownerRecord;

                        StudentProjectReassignment::detachFromSemester(
                            [$studentId],
                            (int) $project->semester_id,
                            (int) $project->id,
                        );
                    }),
            ])
            ->actions([
                Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Actions\DetachBulkAction::make(),
            ]);
    }

    protected function assignmentWarning(int $studentId): HtmlString
    {
        if (! $studentId) {
            return new HtmlString('');
        }

        /** @var Project $project */
        $project = $this->ownerRecord;
        $existingProject = StudentProjectReassignment::firstExistingAssignmentForSemester(
            $studentId,
            (int) $project->semester_id,
            (int) $project->id,
        );
        $student = User::find($studentId);

        if (! $existingProject || ! $student) {
            return new HtmlString('');
        }

        return new HtmlString(
            '<div class="rounded-lg border border-warning-300 bg-warning-50 p-3 text-sm text-warning-700 dark:border-warning-600 dark:bg-warning-950/20 dark:text-warning-300">'
            . e(StudentProjectReassignment::warningMessage($student, $existingProject, $project->title))
            . '</div>'
        );
    }

    protected function hasAssignmentWarning(int $studentId): bool
    {
        if (! $studentId) {
            return false;
        }

        /** @var Project $project */
        $project = $this->ownerRecord;

        return StudentProjectReassignment::firstExistingAssignmentForSemester(
            $studentId,
            (int) $project->semester_id,
            (int) $project->id,
        ) !== null;
    }
}
