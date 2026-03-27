<?php

namespace App\Models;

use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;
    use InteractsWithAppAuthentication, InteractsWithAppAuthenticationRecovery;

    protected $fillable = [
        'university_id',
        'name',
        'email',
        'password',
        'is_approved',
        'specialization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_approved) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole(['Super Admin', 'Coordinator']),
            'staff' => $this->hasAnyRole(['Supervisor', 'Reviewer']),
            'student' => $this->hasRole('Student'),
            default => false,
        };
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function coordinatedSemesters(): BelongsToMany
    {
        return $this->belongsToMany(Semester::class, 'coordinator_semester')
                    ->withTimestamps()
                    ->withPivot('id');
    }

    public function supervisedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'supervisor_id');
    }

    public function studentProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_student')
                    ->withTimestamps()
                    ->withPivot('id');
    }

    public function reviewedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_reviewer')
                    ->withTimestamps()
                    ->withPivot('id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    public function createdRubricTemplates(): HasMany
    {
        return $this->hasMany(RubricTemplate::class, 'created_by');
    }

    public function createdPhaseTemplates(): HasMany
    {
        return $this->hasMany(PhaseTemplate::class, 'created_by');
    }

    public function consolidatedMarks(): HasMany
    {
        return $this->hasMany(ConsolidatedMark::class, 'student_id');
    }
}
