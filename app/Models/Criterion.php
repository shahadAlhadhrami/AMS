<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criterion extends Model
{
    use SoftDeletes;

    protected $table = 'criteria';

    protected $fillable = [
        'title',
        'description',
        'max_score',
        'is_individual',
        'rubric_template_id',
    ];

    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'is_individual' => 'boolean',
        ];
    }

    public function rubricTemplate(): BelongsTo
    {
        return $this->belongsTo(RubricTemplate::class);
    }

    public function scoreLevels(): HasMany
    {
        return $this->hasMany(ScoreLevel::class);
    }

    public function evaluationScores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }
}
