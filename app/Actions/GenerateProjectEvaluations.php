<?php

namespace App\Actions;

use App\Models\Evaluation;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class GenerateProjectEvaluations
{
    public function execute(Project $project): int
    {
        if ($project->status !== 'evaluating') {
            return 0;
        }

        $project->loadMissing([
            'phaseTemplate.phaseRubricRules.rubricTemplate',
            'reviewers',
        ]);

        if (! $project->phaseTemplate) {
            return 0;
        }

        $rules = $project->phaseTemplate->phaseRubricRules;

        if ($rules->isEmpty()) {
            return 0;
        }

        $createdCount = 0;

        DB::transaction(function () use ($project, $rules, &$createdCount) {
            foreach ($rules as $rule) {
                // Lock the rubric template on first use (UC-SY-03)
                if (! $rule->rubricTemplate->is_locked) {
                    $rule->rubricTemplate->update(['is_locked' => true]);
                }

                $evaluators = $this->resolveEvaluators($project, $rule->evaluator_role);

                foreach ($evaluators as $evaluatorId) {
                    $evaluation = Evaluation::firstOrCreate(
                        [
                            'project_id' => $project->id,
                            'rubric_template_id' => $rule->rubric_template_id,
                            'evaluator_id' => $evaluatorId,
                        ],
                        [
                            'evaluator_role' => $rule->evaluator_role,
                            'fill_order' => $rule->fill_order,
                            'status' => 'pending',
                        ]
                    );

                    if ($evaluation->wasRecentlyCreated) {
                        $createdCount++;
                    }
                }
            }
        });

        return $createdCount;
    }

    private function resolveEvaluators(Project $project, string $role): array
    {
        return match ($role) {
            'Supervisor' => $project->supervisor_id ? [$project->supervisor_id] : [],
            'Reviewer' => $project->reviewers->pluck('id')->toArray(),
            default => [],
        };
    }
}
