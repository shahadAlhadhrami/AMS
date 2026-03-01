<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RubricTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'version',
        'parent_template_id',
        'total_marks',
        'is_locked',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_marks' => 'decimal:2',
            'is_locked' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(RubricTemplate::class, 'parent_template_id');
    }

    public function childTemplates(): HasMany
    {
        return $this->hasMany(RubricTemplate::class, 'parent_template_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(Criterion::class);
    }

    public function phaseRubricRules(): HasMany
    {
        return $this->hasMany(PhaseRubricRule::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }
}
