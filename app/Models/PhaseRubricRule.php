<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhaseRubricRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'phase_template_id',
        'rubric_template_id',
        'evaluator_role',
        'fill_order',
        'max_marks',
        'aggregation_method',
    ];

    protected function casts(): array
    {
        return [
            'max_marks' => 'decimal:2',
            'fill_order' => 'integer',
        ];
    }

    public function phaseTemplate(): BelongsTo
    {
        return $this->belongsTo(PhaseTemplate::class);
    }

    public function rubricTemplate(): BelongsTo
    {
        return $this->belongsTo(RubricTemplate::class);
    }
}
