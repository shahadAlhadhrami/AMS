<?php

namespace App\Filament\Admin\Pages;

use App\Models\ConsolidatedMark;
use App\Models\Course;
use App\Models\GradingScale;
use App\Models\Semester;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradeExport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Export';

    protected static ?string $navigationLabel = 'Grade Export';

    protected static ?string $title = 'Grade Export';

    protected string $view = 'filament.admin.pages.grade-export';

    public ?int $selectedSemester = null;

    public ?int $selectedCourse = null;

    public function mount(): void
    {
        // Default to most recent active semester
        $this->selectedSemester = $this->getSemesters()->first()?->id;
    }

    public function getSemesters(): Collection
    {
        $query = Semester::query()->orderByDesc('created_at');

        $user = auth()->user();
        if ($user->hasRole('Coordinator') && ! $user->hasRole('Super Admin')) {
            $query->whereHas('coordinators', function (Builder $q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return $query->get();
    }

    public function getCourses(): Collection
    {
        if (! $this->selectedSemester) {
            return collect();
        }

        return Course::whereHas('projects', function (Builder $q) {
            $q->where('semester_id', $this->selectedSemester);
        })->orderBy('code')->get();
    }

    public function getPreviewData(): Collection
    {
        if (! $this->selectedSemester) {
            return collect();
        }

        $query = ConsolidatedMark::query()
            ->with(['student', 'project.course', 'project.semester', 'components'])
            ->whereHas('project', function (Builder $q) {
                $q->where('semester_id', $this->selectedSemester);

                if ($this->selectedCourse) {
                    $q->where('course_id', $this->selectedCourse);
                }
            });

        // Coordinator scoping
        $user = auth()->user();
        if ($user->hasRole('Coordinator') && ! $user->hasRole('Super Admin')) {
            $query->whereHas('project.semester.coordinators', function (Builder $q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return $query->get()->map(function (ConsolidatedMark $mark) {
            $finalScore = (float) ($mark->override_score ?? $mark->total_calculated_score);

            return [
                'university_id' => $mark->student->university_id,
                'student_name' => $mark->student->name,
                'project_title' => $mark->project->title,
                'calculated_score' => $mark->total_calculated_score,
                'override_score' => $mark->override_score,
                'final_score' => number_format($finalScore, 2),
                'letter_grade' => GradingScale::getLetterGrade($finalScore) ?? '--',
            ];
        });
    }

    public function updatedSelectedSemester(): void
    {
        $this->selectedCourse = null;
    }

    public function exportCsv(): StreamedResponse
    {
        $data = $this->getPreviewData();

        return response()->streamDownload(function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['University ID', 'Student Name', 'Project Title', 'Calculated Score', 'Override Score', 'Final Score', 'Letter Grade']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['university_id'],
                    $row['student_name'],
                    $row['project_title'],
                    $row['calculated_score'],
                    $row['override_score'] ?? '',
                    $row['final_score'],
                    $row['letter_grade'],
                ]);
            }

            fclose($file);
        }, 'grades_export_' . now()->format('Y-m-d') . '.csv');
    }

    public function exportPdf()
    {
        $data = $this->getPreviewData();
        $semester = Semester::find($this->selectedSemester);

        if (! $semester || $data->isEmpty()) {
            \Filament\Notifications\Notification::make()
                ->title('No data to export.')
                ->warning()
                ->send();

            return;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.grade-report', [
            'semester' => $semester,
            'rows' => $data,
            'generatedAt' => now(),
            'generatedBy' => auth()->user()->name,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'grades_' . \Illuminate\Support\Str::slug($semester->name) . '.pdf'
        );
    }
}
