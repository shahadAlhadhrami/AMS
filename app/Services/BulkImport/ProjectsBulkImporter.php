<?php

namespace App\Services\BulkImport;

use App\Models\Course;
use App\Models\PhaseTemplate;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialization;
use App\Models\User;
use App\Support\StudentProjectReassignment;
use Filament\Forms;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options as XlsxOptions;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectsBulkImporter implements BulkImporter
{
    use ResolvesCsvFilePath;

    public function key(): string
    {
        return 'projects';
    }

    public function label(): string
    {
        return 'Projects';
    }

    public function description(): string
    {
        return 'Create projects in bulk. One CSV row per student — repeat the project title and supervisor across that project\'s students. Semester, course, phase template, specialization, and reviewers are chosen in the UI after the CSV is previewed.';
    }

    public function requiresColumnMapping(): bool
    {
        return true;
    }

    public function supportsMultiFile(): bool
    {
        return false;
    }

    public function extraFormFields(): array
    {
        return [];
    }

    public function systemFields(): array
    {
        return [
            'project_title',
            'supervisor_id',
            'student_id',
        ];
    }

    public function systemFieldLabels(): array
    {
        return [
            'project_title' => 'Project Title',
            'supervisor_id' => 'Supervisor ID',
            'student_id' => 'Student ID',
        ];
    }

    public function downloadTemplate(): StreamedResponse
    {
        $dataRows = [
            ['Smart Campus Companion App',        'e9173', '26s3614'],
            ['',                                  '',      '26j3729'],
            ['',                                  '',      '26s3846'],
            ['AI-Powered Assessment Dashboard',   'e6248', '26j3952'],
            ['',                                  '',      '26s4078'],
            ['',                                  '',      '26j4185'],
            ['IoT-Based Smart Greenhouse',        'e3706', '26s4297'],
            ['',                                  '',      '26j4363'],
            ['',                                  '',      '26s4489'],
        ];

        $tmpPath = tempnam(sys_get_temp_dir(), 'ams_tpl_') . '.xlsx';

        $options = new XlsxOptions();
        $options->setColumnWidth(38, 1);
        $options->setColumnWidth(16, 2);
        $options->setColumnWidth(16, 3);
        // Merge project_title (col 0) and supervisor_id (col 1) for each 3-row group.
        // Columns are 0-indexed, rows are 1-indexed; header occupies row 1.
        foreach ([[2, 4], [5, 7], [8, 10]] as [$start, $end]) {
            $options->mergeCells(0, $start, 0, $end);
            $options->mergeCells(1, $start, 1, $end);
        }

        $writer = new XlsxWriter($options);
        $writer->openToFile($tmpPath);

        $headerStyle = (new Style())->setFontBold();
        $writer->addRow(Row::fromValues(['project_title', 'supervisor_id', 'student_id'], $headerStyle));

        foreach ($dataRows as $rowData) {
            $writer->addRow(Row::fromValues($rowData));
        }

        $writer->close();

        return response()->streamDownload(function () use ($tmpPath) {
            readfile($tmpPath);
            @unlink($tmpPath);
        }, 'projects_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function contextFormFields(): array
    {
        return [
            Forms\Components\Select::make('semester_id')
                ->label('Semester')
                ->options(fn () => Semester::orderByDesc('id')->pluck('name', 'id')->toArray())
                ->required()
                ->searchable(),
            Forms\Components\Select::make('course_id')
                ->label('Course')
                ->options(fn () => Course::orderBy('code')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->code} — {$c->title}"])->toArray())
                ->required()
                ->searchable(),
            Forms\Components\Select::make('phase_template_id')
                ->label('Phase Template')
                ->helperText('Reviewers assigned on this Phase Template will be copied onto every imported project.')
                ->options(fn () => PhaseTemplate::orderBy('name')->pluck('name', 'id')->toArray())
                ->required()
                ->searchable(),
            Forms\Components\Select::make('specialization_id')
                ->label('Specialization')
                ->options(fn () => Specialization::orderBy('name')->pluck('name', 'id')->toArray())
                ->required()
                ->searchable(),
        ];
    }

    public function validateRows(array $files, array $columnMapping, array $context): array
    {
        $csvPath = $files[0] ?? null;
        $filePath = $this->resolveCsvFilePath($csvPath);

        if (! $filePath) {
            return [
                'previewData' => [],
                'previewColumns' => $this->previewColumns(),
                'errors' => ['CSV file not found. Please re-upload.'],
                'hasErrors' => true,
            ];
        }

        try {
            $parsed = SpreadsheetReader::read($filePath);
        } catch (\Throwable $e) {
            @unlink($filePath);
            return [
                'previewData' => [],
                'previewColumns' => $this->previewColumns(),
                'errors' => ['Unable to read the spreadsheet: ' . $e->getMessage()],
                'hasErrors' => true,
            ];
        }

        @unlink($filePath);

        $rawHeaders = $parsed['headers'];
        $rows = [];
        // Header row is row 1; data rows start at row 2 in spreadsheet terms.
        $rowNumber = 1;

        // Fill-down state: when a row leaves project_title or supervisor_id empty
        // (because the source xlsx used merged cells, or because the human just left them
        // blank under the previous value), inherit from the most recent non-empty value.
        $lastTitle = '';
        $lastSupervisor = '';

        foreach ($parsed['rows'] as $rawCells) {
            $rowNumber++;
            $rawRow = array_combine($rawHeaders, $rawCells);

            $title = trim($rawRow[$columnMapping['project_title']] ?? '');
            $supervisor = trim($rawRow[$columnMapping['supervisor_id']] ?? '');
            $student = trim($rawRow[$columnMapping['student_id']] ?? '');

            if ($title === '') {
                $title = $lastTitle;
            } else {
                $lastTitle = $title;
                // A new project starts → reset supervisor fill-down so a missing
                // supervisor on the first row of a new project is flagged, not
                // silently inherited from the previous project.
                $lastSupervisor = '';
            }

            if ($supervisor === '') {
                $supervisor = $lastSupervisor;
            } else {
                $lastSupervisor = $supervisor;
            }

            $rows[] = [
                'project_title' => $title,
                'supervisor_id' => $supervisor,
                'student_id' => $student,
                '_row' => $rowNumber,
            ];
        }

        // Drop fully blank rows (a trailing blank line in a spreadsheet shouldn't error).
        $rows = array_values(array_filter(
            $rows,
            fn ($r) => $r['project_title'] !== '' || $r['supervisor_id'] !== '' || $r['student_id'] !== '',
        ));

        if (empty($rows)) {
            return [
                'previewData' => [],
                'previewColumns' => $this->previewColumns(),
                'errors' => ['CSV file contains no data rows.'],
                'hasErrors' => true,
            ];
        }

        // ─── Pass 1: per-row resolution + per-row errors ────────────────────────────
        $errors = [];
        $hasErrors = false;
        $rowOutputs = [];

        // Per-CSV caches to avoid repeated lookups for repeated IDs.
        $userCache = [];

        $resolveUser = function (string $uid) use (&$userCache): ?User {
            if (! array_key_exists($uid, $userCache)) {
                $userCache[$uid] = $uid === '' ? null : User::where('university_id', $uid)->first();
            }
            return $userCache[$uid];
        };

        foreach ($rows as $row) {
            $rowNum = $row['_row'];
            $rowErrors = [];

            $title = $row['project_title'];
            if ($title === '') {
                $rowErrors[] = 'project_title is required';
            }

            $supervisorUid = $row['supervisor_id'];
            $supervisor = null;
            if ($supervisorUid === '') {
                $rowErrors[] = 'supervisor_id is required';
            } else {
                $supervisor = $resolveUser($supervisorUid);
                if (! $supervisor) {
                    $rowErrors[] = "Supervisor with university_id '{$supervisorUid}' not found";
                } elseif (! $supervisor->hasRole('Reviewer/Supervisor')) {
                    $rowErrors[] = "User '{$supervisorUid}' does not have the Reviewer/Supervisor role";
                }
            }

            $studentUid = $row['student_id'];
            $student = null;
            if ($studentUid === '') {
                $rowErrors[] = 'student_id is required';
            } else {
                $student = $resolveUser($studentUid);
                if (! $student) {
                    $rowErrors[] = "Student with university_id '{$studentUid}' not found";
                } elseif (! $student->hasRole('Student')) {
                    $rowErrors[] = "User '{$studentUid}' does not have the Student role";
                }
            }

            $rowOutputs[] = [
                '_row' => $rowNum,
                'project_title' => $title,
                'supervisor_id' => $supervisorUid,
                'student_id' => $studentUid,
                '_supervisor_user_id' => $supervisor?->id,
                '_student_user_id' => $student?->id,
                '_errors' => $rowErrors,
            ];

            foreach ($rowErrors as $error) {
                $errors[] = "Row {$rowNum}: {$error}";
            }
            if (! empty($rowErrors)) {
                $hasErrors = true;
            }
        }

        // ─── Pass 2: group rows by project_title ─────────────────────────────────────
        // Order is preserved by first-appearance of each title.
        $groups = [];
        foreach ($rowOutputs as $output) {
            $title = $output['project_title'];
            if ($title === '') {
                continue; // already reported as a per-row error
            }
            $groups[$title][] = $output;
        }

        // Track students globally across the CSV to flag duplicates across projects.
        $studentFirstSeen = []; // student_id => "Row N (project X)"

        $previewData = [];
        foreach ($groups as $title => $groupRows) {
            $groupErrors = [];

            // Supervisors: every row in the group must agree.
            $distinctSupervisors = collect($groupRows)
                ->pluck('supervisor_id')
                ->filter(fn ($v) => $v !== '')
                ->unique()
                ->values()
                ->all();

            if (count($distinctSupervisors) > 1) {
                $groupErrors[] = "Project '{$title}' has inconsistent supervisor IDs across rows: " . implode(', ', $distinctSupervisors);
            }

            // Students: collect distinct and detect duplicates within the CSV.
            $seenInGroup = [];
            $studentUserIds = [];
            foreach ($groupRows as $output) {
                $sid = $output['student_id'];
                if ($sid === '') {
                    continue; // missing-required already reported
                }

                if (isset($seenInGroup[$sid])) {
                    $groupErrors[] = "Student '{$sid}' is listed twice in project '{$title}'";
                    continue;
                }
                $seenInGroup[$sid] = true;

                if (isset($studentFirstSeen[$sid])) {
                    $groupErrors[] = "Student '{$sid}' appears in project '{$title}' and also in {$studentFirstSeen[$sid]} — a student can only be in one project per import";
                } else {
                    $studentFirstSeen[$sid] = "project '{$title}'";
                }

                if ($output['_student_user_id'] !== null) {
                    $studentUserIds[] = $output['_student_user_id'];
                }
            }

            $studentUserIds = array_values(array_unique($studentUserIds));
            $studentCount = count($seenInGroup);

            // Pick the first non-empty supervisor as canonical (validation already flagged conflicts).
            $supervisorUid = $distinctSupervisors[0] ?? '';
            $supervisorUserId = collect($groupRows)
                ->firstWhere('supervisor_id', $supervisorUid)['_supervisor_user_id'] ?? null;

            $firstRow = $groupRows[0]['_row'];
            $rowAnyHasErrors = collect($groupRows)->contains(fn ($r) => ! empty($r['_errors']));
            $status = (empty($groupErrors) && ! $rowAnyHasErrors) ? 'valid' : 'error';

            $previewData[] = [
                'row' => $firstRow,
                'title' => $title,
                'supervisor' => $supervisorUid,
                'students_count' => $studentCount,
                'student_ids_display' => implode(', ', collect($groupRows)->pluck('student_id')->filter()->unique()->all()),
                'status' => $status,
                'errors' => $groupErrors,
                '_resolved' => [
                    'title' => $title,
                    'supervisor_id' => $supervisorUserId,
                    'student_ids' => $studentUserIds,
                ],
            ];

            foreach ($groupErrors as $error) {
                $errors[] = "Project '{$title}': {$error}";
            }
            if (! empty($groupErrors)) {
                $hasErrors = true;
            }
        }

        return [
            'previewData' => $previewData,
            'previewColumns' => $this->previewColumns(),
            'errors' => $errors,
            'hasErrors' => $hasErrors,
        ];
    }

    public function validateContext(array $previewData, array $context): array
    {
        $errors = [];
        $warnings = [];

        $semesterId = $context['semester_id'] ?? null;
        if (! $semesterId) {
            return ['errors' => $errors, 'hasErrors' => false, 'warnings' => $warnings, 'hasWarnings' => false];
        }

        // Collect all student IDs from the preview, then a single query to check existing
        // memberships in this semester. Avoids N+1.
        $allStudentIds = collect($previewData)
            ->pluck('_resolved.student_ids')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($allStudentIds)) {
            return ['errors' => $errors, 'hasErrors' => false, 'warnings' => $warnings, 'hasWarnings' => false];
        }

        $assignments = StudentProjectReassignment::existingAssignmentsForSemester($allStudentIds, (int) $semesterId);

        if (empty($assignments)) {
            return ['errors' => $errors, 'hasErrors' => false, 'warnings' => $warnings, 'hasWarnings' => false];
        }

        $studentsById = User::query()
            ->whereIn('id', array_keys($assignments))
            ->get(['id', 'university_id', 'name'])
            ->keyBy('id');

        // Map each clashing student to the project it appears in for this CSV.
        foreach ($previewData as $row) {
            $studentIds = $row['_resolved']['student_ids'] ?? [];
            foreach ($studentIds as $sid) {
                foreach ($assignments[$sid] ?? [] as $existingProject) {
                    $student = $studentsById[$sid] ?? null;
                    if (! $student) {
                        continue;
                    }

                    $warnings[] = "Project '{$row['title']}': "
                        . StudentProjectReassignment::warningMessage($student, $existingProject, $row['title']);
                }
            }
        }

        return [
            'errors' => $errors,
            'hasErrors' => ! empty($errors),
            'warnings' => $warnings,
            'hasWarnings' => ! empty($warnings),
        ];
    }

    public function import(array $previewData, array $context): array
    {
        $semesterId = $context['semester_id'] ?? null;
        $courseId = $context['course_id'] ?? null;
        $phaseTemplateId = $context['phase_template_id'] ?? null;
        $specializationId = $context['specialization_id'] ?? null;

        $reviewerIds = [];
        if ($phaseTemplateId) {
            $reviewerIds = PhaseTemplate::with('reviewers:id')
                ->find($phaseTemplateId)
                ?->reviewers
                ?->pluck('id')
                ->all() ?? [];
        }

        $count = 0;
        foreach ($previewData as $row) {
            $resolved = $row['_resolved'];
            $studentIds = $resolved['student_ids'] ?? [];

            if ($semesterId && ! empty($studentIds)) {
                StudentProjectReassignment::detachFromSemester($studentIds, (int) $semesterId);
            }

            $project = Project::create([
                'title' => $resolved['title'],
                'semester_id' => $semesterId,
                'course_id' => $courseId,
                'phase_template_id' => $phaseTemplateId,
                'specialization_id' => $specializationId,
                'supervisor_id' => $resolved['supervisor_id'],
                'status' => 'setup',
            ]);

            if (! empty($studentIds)) {
                $project->students()->attach($studentIds);
            }

            if (! empty($reviewerIds)) {
                $project->reviewers()->attach($reviewerIds);
            }

            $count++;
        }

        return ['count' => $count, 'results' => []];
    }

    public function hasResultsDownload(): bool
    {
        return false;
    }

    public function downloadResults(array $results): StreamedResponse
    {
        return response()->streamDownload(fn () => null, 'projects_import_results.csv');
    }

    protected function previewColumns(): array
    {
        return [
            'title' => 'Project',
            'supervisor' => 'Supervisor',
            'students_count' => '# Students',
            'student_ids_display' => 'Students',
        ];
    }
}
