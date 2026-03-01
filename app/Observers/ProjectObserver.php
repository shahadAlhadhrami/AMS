<?php

namespace App\Observers;

use App\Actions\GenerateProjectEvaluations;
use App\Models\Project;

class ProjectObserver
{
    public function updated(Project $project): void
    {
        if (
            $project->isDirty('status')
            && $project->status === 'evaluating'
            && $project->getOriginal('status') === 'setup'
        ) {
            app(GenerateProjectEvaluations::class)->execute($project);
        }
    }
}
