<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
