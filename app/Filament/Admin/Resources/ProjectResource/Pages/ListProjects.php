<?php

namespace App\Filament\Admin\Resources\ProjectResource\Pages;

use App\Filament\Admin\Resources\ProjectResource;
use App\Models\Course;
use App\Models\PhaseTemplate;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialization;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('importCsv')
                ->label('Import from CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    Forms\Components\FileUpload::make('csv_file')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->required()
                        ->disk('local')
                        ->directory('csv-imports')
                        ->visibility('private'),
                ])
                ->action(function (array $data): void {
                    $this->importProjectsFromCsv($data);
                }),
            Actions\Action::make('downloadTemplate')
                ->label('Download CSV Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return $this->downloadProjectTemplate();
                }),
        ];
    }

    protected function importProjectsFromCsv(array $data): void
    {
        $csvPath = $data['csv_file'] ?? null;

        if (! $csvPath) {
            Notification::make()->title('No CSV file uploaded.')->danger()->send();

            return;
        }

        $filePath = storage_path('app/private/' . $csvPath);
        if (! file_exists($filePath)) {
            $filePath = storage_path('app/' . $csvPath);
        }

        if (! file_exists($filePath)) {
            Notification::make()->title('CSV file not found.')->danger()->send();

            return;
        }

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            Notification::make()->title('Unable to read the CSV file.')->danger()->send();

            return;
        }

        $headers = fgetcsv($handle, length: 0, escape: '');
        if (! $headers) {
            fclose($handle);
            Notification::make()->title('CSV file is empty or has no headers.')->danger()->send();

            return;
        }

        $headers = array_map('trim', array_map('strtolower', $headers));
        $requiredHeaders = [
            'title', 'semester_name', 'course_code', 'phase_template_name',
            'specialization_name', 'supervisor_university_id',
            'student_university_ids', 'reviewer_university_ids',
        ];
        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (! empty($missingHeaders)) {
            fclose($handle);
            Notification::make()
                ->title('Missing required columns: ' . implode(', ', $missingHeaders))
                ->danger()
                ->send();

            return;
        }

        $rows = [];
        $rowNumber = 1;
        while (($row = fgetcsv($handle, length: 0, escape: '')) !== false) {
            $rowNumber++;
            $rowData = array_combine($headers, array_pad($row, count($headers), ''));
            $rowData['_row'] = $rowNumber;
            $rows[] = $rowData;
        }
        fclose($handle);

        if (empty($rows)) {
            Notification::make()->title('CSV file contains no data rows.')->warning()->send();

            return;
        }

        // Validate all rows first
        $errors = [];
        $resolvedRows = [];
        $studentSemesterTracker = []; // Track student-semester assignments across rows

        foreach ($rows as $row) {
            $rowNum = $row['_row'];
            $rowErrors = [];

            // Resolve title
            $title = trim($row['title'] ?? '');
            if (empty($title)) {
                $rowErrors[] = 'title is required';
            }

            // Resolve semester
            $semesterName = trim($row['semester_name'] ?? '');
            $semester = $semesterName ? Semester::where('name', $semesterName)->first() : null;
            if (! $semester) {
                $rowErrors[] = "Semester '{$semesterName}' not found";
            }

            // Resolve course
            $courseCode = trim($row['course_code'] ?? '');
            $course = $courseCode ? Course::where('code', $courseCode)->first() : null;
            if (! $course) {
                $rowErrors[] = "Course '{$courseCode}' not found";
            }

            // Resolve phase template
            $phaseTemplateName = trim($row['phase_template_name'] ?? '');
            $phaseTemplate = $phaseTemplateName ? PhaseTemplate::where('name', $phaseTemplateName)->first() : null;
            if (! $phaseTemplate) {
                $rowErrors[] = "Phase template '{$phaseTemplateName}' not found";
            }

            // Resolve specialization
            $specName = trim($row['specialization_name'] ?? '');
            $specialization = $specName ? Specialization::where('name', $specName)->first() : null;
            if (! $specialization) {
                $rowErrors[] = "Specialization '{$specName}' not found";
            }

            // Resolve supervisor
            $supervisorUid = trim($row['supervisor_university_id'] ?? '');
            $supervisor = $supervisorUid ? User::where('university_id', $supervisorUid)->first() : null;
            if (! $supervisor) {
                $rowErrors[] = "Supervisor with university_id '{$supervisorUid}' not found";
            } elseif (! $supervisor->hasRole('Reviewer/Supervisor')) {
                $rowErrors[] = "User '{$supervisorUid}' does not have the Reviewer/Supervisor role";
            }

            // Resolve students
            $studentUids = array_filter(array_map('trim', explode('|', $row['student_university_ids'] ?? '')));
            $studentIds = [];
            foreach ($studentUids as $uid) {
                if (empty($uid)) {
                    continue;
                }
                $student = User::where('university_id', $uid)->first();
                if (! $student) {
                    $rowErrors[] = "Student with university_id '{$uid}' not found";
                } elseif (! $student->hasRole('Student')) {
                    $rowErrors[] = "User '{$uid}' does not have the Student role";
                } else {
                    $studentIds[] = $student->id;

                    // D3: Check semester uniqueness (against DB)
                    if ($semester) {
                        $existsInDb = Project::where('semester_id', $semester->id)
                            ->whereHas('students', fn ($q) => $q->where('users.id', $student->id))
                            ->exists();
                        if ($existsInDb) {
                            $rowErrors[] = "Student '{$uid}' is already in another project in semester '{$semesterName}'";
                        }

                        // D3: Check semester uniqueness (across CSV rows)
                        $trackKey = $semester->id . '-' . $student->id;
                        if (isset($studentSemesterTracker[$trackKey])) {
                            $rowErrors[] = "Student '{$uid}' appears in multiple projects for semester '{$semesterName}' in this CSV";
                        }
                        $studentSemesterTracker[$trackKey] = $rowNum;
                    }
                }
            }

            // D3: Max 4 students
            if (count($studentIds) > 4) {
                $rowErrors[] = 'Maximum 4 students per project (found ' . count($studentIds) . ')';
            }

            // Resolve reviewers
            $reviewerUids = array_filter(array_map('trim', explode('|', $row['reviewer_university_ids'] ?? '')));
            $reviewerIds = [];
            foreach ($reviewerUids as $uid) {
                if (empty($uid)) {
                    continue;
                }
                $reviewer = User::where('university_id', $uid)->first();
                if (! $reviewer) {
                    $rowErrors[] = "Reviewer with university_id '{$uid}' not found";
                } elseif (! $reviewer->hasRole('Reviewer/Supervisor')) {
                    $rowErrors[] = "User '{$uid}' does not have the Reviewer/Supervisor role";
                } else {
                    $reviewerIds[] = $reviewer->id;

                    // D3: Supervisor cannot be reviewer
                    if ($supervisor && $reviewer->id === $supervisor->id) {
                        $rowErrors[] = "Supervisor '{$supervisorUid}' cannot also be a reviewer";
                    }
                }
            }

            if (! empty($rowErrors)) {
                foreach ($rowErrors as $error) {
                    $errors[] = "Row {$rowNum}: {$error}";
                }
            }

            $resolvedRows[] = [
                'title' => $title,
                'semester_id' => $semester?->id,
                'course_id' => $course?->id,
                'phase_template_id' => $phaseTemplate?->id,
                'specialization_id' => $specialization?->id,
                'supervisor_id' => $supervisor?->id,
                'student_ids' => $studentIds,
                'reviewer_ids' => $reviewerIds,
            ];
        }

        if (! empty($errors)) {
            Notification::make()
                ->title('Validation errors found (' . count($errors) . ')')
                ->body(implode("\n", array_slice($errors, 0, 10)) . (count($errors) > 10 ? "\n...and " . (count($errors) - 10) . ' more' : ''))
                ->danger()
                ->persistent()
                ->send();

            @unlink($filePath);

            return;
        }

        // All valid — import
        DB::beginTransaction();
        try {
            $importedCount = 0;

            foreach ($resolvedRows as $resolved) {
                $project = Project::create([
                    'title' => $resolved['title'],
                    'semester_id' => $resolved['semester_id'],
                    'course_id' => $resolved['course_id'],
                    'phase_template_id' => $resolved['phase_template_id'],
                    'specialization_id' => $resolved['specialization_id'],
                    'supervisor_id' => $resolved['supervisor_id'],
                    'status' => 'setup',
                ]);

                if (! empty($resolved['student_ids'])) {
                    $project->students()->attach($resolved['student_ids']);
                }

                if (! empty($resolved['reviewer_ids'])) {
                    $project->reviewers()->attach($resolved['reviewer_ids']);
                }

                $importedCount++;
            }

            DB::commit();

            @unlink($filePath);

            Notification::make()
                ->title("Successfully imported {$importedCount} projects.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Import failed: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function downloadProjectTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'title', 'semester_name', 'course_code', 'phase_template_name',
                'specialization_name', 'supervisor_university_id',
                'student_university_ids', 'reviewer_university_ids',
            ]);
            fputcsv($file, [
                'Smart Campus App', 'Fall 2026', 'CS101', 'Phase 1 Template',
                'Software Engineering', 'SUP001',
                'STU001|STU002|STU003', 'REV001|REV002',
            ]);
            fputcsv($file, [
                'AI Assessment System', 'Fall 2026', 'CS101', 'Phase 1 Template',
                'Software Engineering', 'SUP002',
                'STU004|STU005', 'REV001',
            ]);
            fclose($file);
        }, 'projects_import_template.csv');
    }
}
