<?php

namespace App\Filament\Staff\Pages;

use App\Models\Project;
use Filament\Pages\Page;

class ProjectDetail extends Page
{
    private const CONTEXT_REVIEWER = 'reviewer';

    private const CONTEXT_SUPERVISOR = 'supervisor';

    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.staff.pages.project-detail';

    public Project $project;

    public string $evaluationContext = self::CONTEXT_SUPERVISOR;

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

        $this->evaluationContext = $this->resolveEvaluationContext($isSupervisor, $isReviewer);
        $evaluatorRole = $this->evaluationContext === self::CONTEXT_SUPERVISOR ? 'Supervisor' : 'Reviewer';

        $this->project = $project->load([
            'semester',
            'course',
            'phaseTemplate',
            'specialization',
            'supervisor',
            'students',
            'reviewers',
            'evaluations' => fn ($query) => $query
                ->where('evaluator_id', $user->id)
                ->where('evaluator_role', $evaluatorRole)
                ->with(['evaluator', 'rubricTemplate'])
                ->orderBy('fill_order')
                ->orderBy('id'),
        ]);
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->project->title;
    }

    private function resolveEvaluationContext(bool $isSupervisor, bool $isReviewer): string
    {
        $context = request()->query('context');

        if ($context === self::CONTEXT_REVIEWER && $isReviewer) {
            return self::CONTEXT_REVIEWER;
        }

        if ($context === self::CONTEXT_SUPERVISOR && $isSupervisor) {
            return self::CONTEXT_SUPERVISOR;
        }

        return $isSupervisor ? self::CONTEXT_SUPERVISOR : self::CONTEXT_REVIEWER;
    }
}
