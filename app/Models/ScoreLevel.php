<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScoreLevel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'criterion_id',
        'label',
        'score_value',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'score_value' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }
}
