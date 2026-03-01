<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationScore extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'evaluation_id',
        'criterion_id',
        'score_level_id',
        'student_id',
        'score_awarded',
        'feedback',
    ];

    protected function casts(): array
    {
        return [
            'score_awarded' => 'decimal:2',
        ];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }

    public function scoreLevel(): BelongsTo
    {
        return $this->belongsTo(ScoreLevel::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
