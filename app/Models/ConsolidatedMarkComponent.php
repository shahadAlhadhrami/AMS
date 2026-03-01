<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsolidatedMarkComponent extends Model
{
    use SoftDeletes;

    protected $fillable = ['consolidated_mark_id', 'source_label', 'score'];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function consolidatedMark(): BelongsTo
    {
        return $this->belongsTo(ConsolidatedMark::class);
    }
}
