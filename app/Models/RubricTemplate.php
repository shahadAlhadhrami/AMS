<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RubricTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'version',
        'parent_template_id',
        'rubric_folder_id',
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

    public function scopeLocked(Builder $query): Builder
    {
        return $this->whereBooleanLiteral($query, 'is_locked', true);
    }

    public function scopeUnlocked(Builder $query): Builder
    {
        return $this->whereBooleanLiteral($query, 'is_locked', false);
    }

    private function whereBooleanLiteral(Builder $query, string $column, bool $value): Builder
    {
        return $query->where(
            $query->getModel()->qualifyColumn($column),
            DB::raw($value ? 'true' : 'false')
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(RubricFolder::class, 'rubric_folder_id');
    }

    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(RubricTemplate::class, 'parent_template_id');
    }

    public function childTemplates(): HasMany
    {
        return $this->hasMany(RubricTemplate::class, 'parent_template_id');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class)->orderBy('sort_order');
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
