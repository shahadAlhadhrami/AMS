<?php

namespace App\Filament\Staff\Pages;

use App\Models\Project;
use Filament\Pages\Page;

class ProjectDetail extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.staff.pages.project-detail';

    public Project $project;

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'project-detail/{project}';
    }

    public function mount(Project $project): void
    {
        $user = auth()->user();
        $isSupervisor = $project->supervisor_id === $user->id;
        $isReviewer = $project->reviewers()->where('users.id', $user->id)->exists();

        abort_unless($isSupervisor || $isReviewer, 403);

        $this->project = $project->load([
            'semester',
            'course',
            'phaseTemplate',
            'specialization',
            'supervisor',
            'students',
            'reviewers',
            'evaluations.evaluator',
            'evaluations.rubricTemplate',
        ]);
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->project->title;
    }
}
