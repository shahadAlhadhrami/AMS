<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhaseTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'total_phase_marks', 'created_by'];

    protected function casts(): array
    {
        return [
            'total_phase_marks' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function phaseRubricRules(): HasMany
    {
        return $this->hasMany(PhaseRubricRule::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function consolidatedMarks(): HasMany
    {
        return $this->hasMany(ConsolidatedMark::class);
    }
}
