<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'semester_id',
        'course_id',
        'phase_template_id',
        'specialization_id',
        'title',
        'supervisor_id',
        'coordinator_id',
        'previous_phase_project_id',
        'status',
    ];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function phaseTemplate(): BelongsTo
    {
        return $this->belongsTo(PhaseTemplate::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function previousPhaseProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'previous_phase_project_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_student')
                    ->withTimestamps()
                    ->withPivot('id');
    }

    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_reviewer')
                    ->withTimestamps()
                    ->withPivot('id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function consolidatedMarks(): HasMany
    {
        return $this->hasMany(ConsolidatedMark::class);
    }

    public function autoTransitionToEvaluating(): void
    {
        if ($this->status !== 'setup' || is_null($this->supervisor_id)) {
            return;
        }

        $phaseTemplate = $this->phaseTemplate;

        if (! $phaseTemplate || ! $phaseTemplate->phaseRubricRules()->exists()) {
            return;
        }

        $this->update(['status' => 'evaluating']);
    }
}
