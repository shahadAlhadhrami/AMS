<?php

namespace App\Observers;

use App\Models\Criterion;

class CriterionObserver
{
    public function created(Criterion $criterion): void
    {
        $this->recalculateTotalMarks($criterion);
    }

    public function updated(Criterion $criterion): void
    {
        $this->recalculateTotalMarks($criterion);
    }

    public function deleted(Criterion $criterion): void
    {
        $this->recalculateTotalMarks($criterion);
    }

    protected function recalculateTotalMarks(Criterion $criterion): void
    {
        $rubricTemplate = $criterion->rubricTemplate;

        if ($rubricTemplate) {
            $rubricTemplate->update([
                'total_marks' => $rubricTemplate->criteria()->sum('max_score'),
            ]);
        }
    }
}
