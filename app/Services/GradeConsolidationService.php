<?php

namespace App\Services;

use App\Models\ConsolidatedMark;
use App\Models\PhaseRubricRule;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MarksFinalisedNotification;
use Illuminate\Support\Facades\DB;

class GradeConsolidationService
{
    public function consolidate(Project $project): void
    {
        DB::transaction(function () use ($project) {
            // Lock the project row to prevent race conditions
            $project = Project::lockForUpdate()->find($project->id);

            $project->load([
                'phaseTemplate.phaseRubricRules.rubricTemplate.criteria',
                'students',
                'evaluations' => function ($q) {
                    $q->where('status', 'submitted')->with('evaluationScores');
                },
            ]);

            $phaseTemplate = $project->phaseTemplate;

            if (! $phaseTemplate) {
                return;
            }

            $rules = $phaseTemplate->phaseRubricRules;
            $students = $project->students;

            // Clear any existing consolidated marks for re-calculation
            $project->consolidatedMarks()->forceDelete();

            foreach ($students as $student) {
                $totalScore = 0;
                $components = [];

                foreach ($rules as $rule) {
                    $ruleScore = $this->calculateRuleScore($project, $rule, $student);

                    // Cap at max_marks
                    $ruleScore = min($ruleScore, (float) $rule->max_marks);

                    $components[] = [
                        'source_label' => $this->buildSourceLabel($rule),
                        'score' => round($ruleScore, 2),
                    ];

                    $totalScore += $ruleScore;
                }

                $consolidatedMark = ConsolidatedMark::create([
                    'project_id' => $project->id,
                    'phase_template_id' => $phaseTemplate->id,
                    'student_id' => $student->id,
                    'total_calculated_score' => round($totalScore, 2),
                ]);

                foreach ($components as $component) {
                    $consolidatedMark->components()->create($component);
                }
            }

            // Update project status to completed
            $project->update(['status' => 'completed']);

            // Notify students that their marks are finalized
            foreach ($students as $student) {
                $student->notify(new MarksFinalisedNotification($project));
            }
        });
    }

    private function calculateRuleScore(Project $project, PhaseRubricRule $rule, User $student): float
    {
        $evaluations = $project->evaluations
            ->where('rubric_template_id', $rule->rubric_template_id)
            ->where('evaluator_role', $rule->evaluator_role)
            ->where('status', 'submitted');

        if ($evaluations->isEmpty()) {
            return 0;
        }

        $evaluationTotals = [];

        foreach ($evaluations as $evaluation) {
            $evalTotal = 0;
            $criteria = $rule->rubricTemplate->criteria;

            foreach ($criteria as $criterion) {
                if ($criterion->is_individual) {
                    $score = $evaluation->evaluationScores
                        ->where('criterion_id', $criterion->id)
                        ->where('student_id', $student->id)
                        ->first();
                } else {
                    $score = $evaluation->evaluationScores
                        ->where('criterion_id', $criterion->id)
                        ->whereNull('student_id')
                        ->first();
                }

                $evalTotal += $score ? (float) $score->score_awarded : 0;
            }

            $evaluationTotals[] = $evalTotal;
        }

        return $this->aggregate($evaluationTotals, $rule->aggregation_method);
    }

    private function aggregate(array $scores, string $method): float
    {
        if (empty($scores)) {
            return 0;
        }

        return match ($method) {
            'SUM' => array_sum($scores),
            'MAX' => max($scores),
            'AVERAGE', 'WEIGHTED_AVERAGE' => array_sum($scores) / count($scores),
            default => array_sum($scores) / count($scores),
        };
    }

    private function buildSourceLabel(PhaseRubricRule $rule): string
    {
        return $rule->evaluator_role . ' - ' . $rule->rubricTemplate->name;
    }
}
