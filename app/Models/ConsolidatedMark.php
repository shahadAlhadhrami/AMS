<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsolidatedMark extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'phase_template_id',
        'student_id',
        'total_calculated_score',
        'override_score',
        'override_reason',
    ];

    protected function casts(): array
    {
        return [
            'total_calculated_score' => 'decimal:2',
            'override_score' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phaseTemplate(): BelongsTo
    {
        return $this->belongsTo(PhaseTemplate::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(ConsolidatedMarkComponent::class);
    }
}
