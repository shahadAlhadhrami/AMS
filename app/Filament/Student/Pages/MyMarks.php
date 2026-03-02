<?php

namespace App\Filament\Student\Pages;

use App\Models\ConsolidatedMark;
use App\Models\Evaluation;
use App\Models\GradingScale;
use App\Models\Project;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MyMarks extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'My Marks';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.student.pages.my-marks';

    public ?int $selectedSemester = null;

    public function mount(): void
    {
        $student = auth()->user();
        $latestProject = $student->studentProjects()
            ->with('semester')
            ->latest('projects.created_at')
            ->first();

        $this->selectedSemester = $latestProject?->semester_id;
    }

    public function getSemesters(): Collection
    {
        return auth()->user()->studentProjects()
            ->with('semester')
            ->get()
            ->pluck('semester')
            ->unique('id')
            ->sortByDesc('created_at')
            ->values();
    }

    public function getProjectData(): ?array
    {
        if (! $this->selectedSemester) {
            return null;
        }

        $student = auth()->user();
        $project = $student->studentProjects()
            ->where('semester_id', $this->selectedSemester)
            ->with(['supervisor', 'course', 'semester', 'students'])
            ->first();

        if (! $project) {
            return null;
        }

        return [
            'project' => $project,
            'internalMarks' => $this->getInternalMarks($project, $student),
            'consolidatedMark' => $this->getConsolidatedMark($project, $student),
        ];
    }

    private function getInternalMarks(Project $project, $student): Collection
    {
        return Evaluation::where('project_id', $project->id)
            ->where('evaluator_role', 'Supervisor')
            ->where('status', 'submitted')
            ->with(['rubricTemplate', 'evaluationScores' => function ($q) use ($student) {
                $q->with('criterion')
                    ->where(function ($sub) use ($student) {
                        $sub->whereNull('student_id')
                            ->orWhere('student_id', $student->id);
                    });
            }])
            ->get();
    }

    private function getConsolidatedMark(Project $project, $student): ?array
    {
        $mark = ConsolidatedMark::where('project_id', $project->id)
            ->where('student_id', $student->id)
            ->with('components')
            ->first();

        if (! $mark) {
            return null;
        }

        $finalScore = (float) ($mark->override_score ?? $mark->total_calculated_score);

        return [
            'mark' => $mark,
            'finalScore' => number_format($finalScore, 2),
            'letterGrade' => GradingScale::getLetterGrade($finalScore) ?? '--',
            'gpa' => GradingScale::getGpa($finalScore),
        ];
    }
}
