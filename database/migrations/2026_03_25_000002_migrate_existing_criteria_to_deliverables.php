<?php

use App\Models\Criterion;
use App\Models\Deliverable;
use App\Models\RubricTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // For each rubric template that has criteria without a deliverable, create a "General" deliverable
        RubricTemplate::withTrashed()->each(function (RubricTemplate $template) {
            $orphanCriteria = Criterion::withTrashed()
                ->where('rubric_template_id', $template->id)
                ->whereNull('deliverable_id')
                ->get();

            if ($orphanCriteria->isEmpty()) {
                return;
            }

            $deliverable = Deliverable::create([
                'rubric_template_id' => $template->id,
                'title' => 'General',
                'max_marks' => $orphanCriteria->sum('max_score'),
                'sort_order' => 0,
            ]);

            Criterion::withTrashed()
                ->where('rubric_template_id', $template->id)
                ->whereNull('deliverable_id')
                ->update(['deliverable_id' => $deliverable->id]);
        });
    }

    public function down(): void
    {
        // Remove deliverables named "General" that were created by this migration
        Deliverable::where('title', 'General')->delete();
    }
};
