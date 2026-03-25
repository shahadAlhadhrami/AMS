<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deliverable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rubric_template_id',
        'title',
        'max_marks',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'max_marks' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function rubricTemplate(): BelongsTo
    {
        return $this->belongsTo(RubricTemplate::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(Criterion::class)->orderBy('sort_order');
    }
}
