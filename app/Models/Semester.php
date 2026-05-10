<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Semester extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'academic_year',
        'start_date',
        'end_date',
        'is_active',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'is_closed' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $this->whereBooleanLiteral($query, 'is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $this->whereBooleanLiteral($query, 'is_active', false);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $this->whereBooleanLiteral($query, 'is_closed', true);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $this->whereBooleanLiteral($query, 'is_closed', false);
    }

    private function whereBooleanLiteral(Builder $query, string $column, bool $value): Builder
    {
        return $query->where(
            $query->getModel()->qualifyColumn($column),
            DB::raw($value ? 'true' : 'false')
        );
    }

    public function coordinators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coordinator_semester')
            ->withTimestamps()
            ->withPivot('id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
