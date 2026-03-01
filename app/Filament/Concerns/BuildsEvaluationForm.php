<?php

namespace App\Filament\Concerns;

use App\Models\Criterion;
use Filament\Forms;

trait BuildsEvaluationForm
{
    /**
     * Dynamically build the form schema from rubric criteria.
     */
    protected function buildFormSchema(): array
    {
        $schema = [];
        $criteria = $this->evaluation->rubricTemplate->criteria->sortBy('id');
        $students = $this->evaluation->project->students;
        $isReadOnly = $this->evaluation->status === 'submitted';

        // Group criteria section
        $groupCriteria = $criteria->where('is_individual', false);

        if ($groupCriteria->isNotEmpty()) {
            $schema[] = Forms\Components\Section::make('Group Criteria')
                ->description('These criteria are scored once for the entire group.')
                ->schema(
                    $groupCriteria->flatMap(fn (Criterion $criterion) => $this->buildCriterionFields($criterion, null, $isReadOnly)
                    )->toArray()
                );
        }

        // Individual criteria section
        $individualCriteria = $criteria->where('is_individual', true);

        if ($individualCriteria->isNotEmpty()) {
            $schema[] = Forms\Components\Section::make('Individual Criteria')
                ->description('These criteria are scored individually for each student.')
                ->schema(
                    $individualCriteria->map(fn (Criterion $criterion) => Forms\Components\Section::make($criterion->title.' ('.$criterion->max_score.' marks)')
                        ->description($criterion->description)
                        ->schema(
                            $students->map(fn ($student) => Forms\Components\Fieldset::make($student->name.' ('.$student->university_id.')')
                                ->schema(
                                    $this->buildCriterionFields($criterion, $student->id, $isReadOnly)
                                )
                            )->toArray()
                        )
                        ->collapsible()
                    )->toArray()
                );
        }

        // General feedback
        $schema[] = Forms\Components\Textarea::make('general_feedback')
            ->label('General Feedback')
            ->rows(4)
            ->disabled($isReadOnly)
            ->columnSpanFull();

        return $schema;
    }

    /**
     * Build form fields for a single criterion (or criterion+student combo).
     */
    protected function buildCriterionFields(Criterion $criterion, ?int $studentId, bool $isReadOnly): array
    {
        $scoreLevels = $criterion->scoreLevels->sortBy('sort_order');

        $prefix = $studentId
            ? "criteria.{$criterion->id}.students.{$studentId}"
            : "criteria.{$criterion->id}";

        $fields = [];

        // Score level dropdown (only if score levels are defined)
        if ($scoreLevels->isNotEmpty()) {
            $fields[] = Forms\Components\Select::make("{$prefix}.score_level_id")
                ->label('Score Level')
                ->options(
                    $scoreLevels->mapWithKeys(fn ($level) => [
                        $level->id => "{$level->label} ({$level->score_value})",
                    ])
                )
                ->placeholder('Select or enter manual score')
                ->disabled($isReadOnly)
                ->live()
                ->afterStateUpdated(function ($state, callable $set) use ($prefix, $scoreLevels) {
                    if ($state) {
                        $level = $scoreLevels->firstWhere('id', $state);

                        if ($level) {
                            $set("{$prefix}.score_awarded", (string) $level->score_value);
                        }
                    }
                });
        }

        // Criterion label for group criteria (section already shows label for individual)
        $scoreLabel = $studentId
            ? 'Score'
            : $criterion->title.' ('.$criterion->max_score.' marks)';

        // Manual score input
        $fields[] = Forms\Components\TextInput::make("{$prefix}.score_awarded")
            ->label($scoreLabel)
            ->numeric()
            ->minValue(0)
            ->maxValue((float) $criterion->max_score)
            ->step(0.01)
            ->disabled($isReadOnly)
            ->suffix('/ '.$criterion->max_score)
            ->required();

        // Feedback textarea
        $fields[] = Forms\Components\Textarea::make("{$prefix}.feedback")
            ->label('Feedback')
            ->rows(2)
            ->disabled($isReadOnly);

        return $fields;
    }

    /**
     * Load existing scores into the form data array structure.
     */
    protected function loadFormData(): array
    {
        $data = [
            'general_feedback' => $this->evaluation->general_feedback,
            'criteria' => [],
        ];

        foreach ($this->evaluation->evaluationScores as $score) {
            $scoreData = [
                'score_level_id' => $score->score_level_id,
                'score_awarded' => $score->score_awarded,
                'feedback' => $score->feedback,
            ];

            if ($score->student_id) {
                $data['criteria'][$score->criterion_id]['students'][$score->student_id] = $scoreData;
            } else {
                $data['criteria'][$score->criterion_id] = $scoreData;
            }
        }

        return $data;
    }
}
