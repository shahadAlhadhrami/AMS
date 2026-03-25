<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RubricFolder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'parent_id',
        'created_by',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RubricFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(RubricFolder::class, 'parent_id')->orderBy('name');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rubricTemplates(): HasMany
    {
        return $this->hasMany(RubricTemplate::class, 'rubric_folder_id');
    }

    /**
     * Returns the ancestor chain from root down to (but not including) this folder.
     * @return \Illuminate\Support\Collection<RubricFolder>
     */
    public function getAncestors(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current !== null) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }
}
