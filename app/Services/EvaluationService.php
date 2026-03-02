<?php

namespace App\Services;

use App\Models\Evaluation;
use App\Models\EvaluationScore;
use App\Notifications\AllEvaluationsSubmittedNotification;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    /**
     * Check if this evaluation's fill_order prerequisites are met.
     * All evaluations for the same project with lower fill_order must be submitted.
     */
    public function isFillOrderMet(Evaluation $evaluation): bool
    {
        $project = $evaluation->project;
        $phaseTemplate = $project->phaseTemplate;

        if (! $phaseTemplate) {
            return true;
        }

        // Find this evaluation's fill_order from the PhaseRubricRule
        $currentRule = $phaseTemplate->phaseRubricRules()
            ->where('rubric_template_id', $evaluation->rubric_template_id)
            ->where('evaluator_role', $evaluation->evaluator_role)
            ->first();

        if (! $currentRule || $currentRule->fill_order <= 1) {
            return true;
        }

        // Find all rules with lower fill_order
        $prerequisiteRules = $phaseTemplate->phaseRubricRules()
            ->where('fill_order', '<', $currentRule->fill_order)
            ->get();

        foreach ($prerequisiteRules as $prereqRule) {
            $hasEvaluations = $project->evaluations()
                ->where('rubric_template_id', $prereqRule->rubric_template_id)
                ->where('evaluator_role', $prereqRule->evaluator_role)
                ->exists();

            if (! $hasEvaluations) {
                return false;
            }

            $hasUnsubmitted = $project->evaluations()
                ->where('rubric_template_id', $prereqRule->rubric_template_id)
                ->where('evaluator_role', $prereqRule->evaluator_role)
                ->where('status', '!=', 'submitted')
                ->exists();

            if ($hasUnsubmitted) {
                return false;
            }
        }

        return true;
    }

    /**
     * Save evaluation scores from form data.
     */
    public function saveScores(Evaluation $evaluation, array $formData): int
    {
        $count = 0;

        DB::transaction(function () use ($evaluation, $formData, &$count) {
            $evaluation->update([
                'general_feedback' => $formData['general_feedback'] ?? null,
            ]);

            foreach ($formData['criteria'] ?? [] as $criterionId => $criterionData) {
                if (isset($criterionData['students'])) {
                    // Individual criterion — per-student scores
                    foreach ($criterionData['students'] as $studentId => $studentData) {
                        $this->upsertScore($evaluation, $criterionId, $studentData, $studentId);
                        $count++;
                    }
                } else {
                    // Group criterion — single score
                    $this->upsertScore($evaluation, $criterionId, $criterionData, null);
                    $count++;
                }
            }
        });

        return $count;
    }

    private function upsertScore(
        Evaluation $evaluation,
        int $criterionId,
        array $data,
        ?int $studentId
    ): void {
        EvaluationScore::updateOrCreate(
            [
                'evaluation_id' => $evaluation->id,
                'criterion_id' => $criterionId,
                'student_id' => $studentId,
            ],
            [
                'score_level_id' => $data['score_level_id'] ?? null,
                'score_awarded' => $data['score_awarded'] ?? 0,
                'feedback' => $data['feedback'] ?? null,
            ]
        );
    }

    /**
     * Submit an evaluation — validates completeness then changes status.
     */
    public function submit(Evaluation $evaluation): bool
    {
        $rubric = $evaluation->rubricTemplate->load('criteria');
        $students = $evaluation->project->students;

        foreach ($rubric->criteria as $criterion) {
            if ($criterion->is_individual) {
                foreach ($students as $student) {
                    $hasScore = $evaluation->evaluationScores()
                        ->where('criterion_id', $criterion->id)
                        ->where('student_id', $student->id)
                        ->whereNotNull('score_awarded')
                        ->exists();

                    if (! $hasScore) {
                        return false;
                    }
                }
            } else {
                $hasScore = $evaluation->evaluationScores()
                    ->where('criterion_id', $criterion->id)
                    ->whereNull('student_id')
                    ->whereNotNull('score_awarded')
                    ->exists();

                if (! $hasScore) {
                    return false;
                }
            }
        }

        $evaluation->update(['status' => 'submitted']);

        // Check if all evaluations for this project are now submitted
        $project = $evaluation->project;
        $allSubmitted = ! $project->evaluations()
            ->where('status', '!=', 'submitted')
            ->exists();

        if ($allSubmitted) {
            app(GradeConsolidationService::class)->consolidate($project);

            // Notify coordinators that all evaluations are submitted
            $project->load('semester.coordinators');
            foreach ($project->semester->coordinators as $coordinator) {
                $coordinator->notify(new AllEvaluationsSubmittedNotification($project));
            }
        }

        return true;
    }
}
