<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoordinatorSemester extends Pivot
{
    use SoftDeletes;

    protected $table = 'coordinator_semester';

    public $incrementing = true;

    protected $fillable = ['user_id', 'semester_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
