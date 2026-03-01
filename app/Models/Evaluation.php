<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'rubric_template_id',
        'evaluator_id',
        'evaluator_role',
        'on_behalf_of_user_id',
        'evidence_attachment_path',
        'status',
        'general_feedback',
        'unlocked_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function rubricTemplate(): BelongsTo
    {
        return $this->belongsTo(RubricTemplate::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function onBehalfOfUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'on_behalf_of_user_id');
    }

    public function unlockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    public function evaluationScores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }
}
