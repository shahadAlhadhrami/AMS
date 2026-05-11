<?php

namespace App\Services\BulkImport;

use App\Models\Course;
use App\Models\PhaseTemplate;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialization;
use App\Models\User;
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
        return 'Create projects with supervisor, students, and reviewers in bulk. Lookups resolve by semester name, course code, and university IDs.';
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
            'title',
            'semester_name',
            'course_code',
            'phase_template_name',
            'specialization_name',
            'supervisor_university_id',
            'student_university_ids',
            'reviewer_university_ids',
        ];
    }

    public function systemFieldLabels(): array
    {
        return [
            'title' => 'Project Title',
            'semester_name' => 'Semester Name',
            'course_code' => 'Course Code',
            'phase_template_name' => 'Phase Template Name',
            'specialization_name' => 'Specialization Name',
            'supervisor_university_id' => 'Supervisor University ID',
            'student_university_ids' => 'Student University IDs (pipe-separated)',
            'reviewer_university_ids' => 'Reviewer University IDs (pipe-separated)',
        ];
    }

    public function downloadTemplate(): StreamedResponse
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

    public function validateRows(array $files, array $columnMapping, array $context): array
    {
        $previewData = [];
        $errors = [];

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

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return [
                'previewData' => [],
                'previewColumns' => $this->previewColumns(),
                'errors' => ['Unable to read the CSV file.'],
                'hasErrors' => true,
            ];
        }

        $rawHeaders = fgetcsv($handle, length: 0, escape: '');
        $rawHeaders = array_map('trim', $rawHeaders ?? []);

        $rows = [];
        $rowNumber = 1;
        while (($row = fgetcsv($handle, length: 0, escape: '')) !== false) {
            $rowNumber++;
            $rawRow = array_combine($rawHeaders, array_pad($row, count($rawHeaders), ''));

            $rows[] = [
                'title' => trim($rawRow[$columnMapping['title']] ?? ''),
                'semester_name' => trim($rawRow[$columnMapping['semester_name']] ?? ''),
                'course_code' => trim($rawRow[$columnMapping['course_code']] ?? ''),
                'phase_template_name' => trim($rawRow[$columnMapping['phase_template_name']] ?? ''),
                'specialization_name' => trim($rawRow[$columnMapping['specialization_name']] ?? ''),
                'supervisor_university_id' => trim($rawRow[$columnMapping['supervisor_university_id']] ?? ''),
                'student_university_ids' => trim($rawRow[$columnMapping['student_university_ids']] ?? ''),
                'reviewer_university_ids' => trim($rawRow[$columnMapping['reviewer_university_ids']] ?? ''),
                '_row' => $rowNumber,
            ];
        }
        fclose($handle);

        @unlink($filePath);

        if (empty($rows)) {
            return [
                'previewData' => [],
                'previewColumns' => $this->previewColumns(),
                'errors' => ['CSV file contains no data rows.'],
                'hasErrors' => true,
            ];
        }

        $studentSemesterTracker = [];
        $hasErrors = false;

        foreach ($rows as $row) {
            $rowNum = $row['_row'];
            $rowErrors = [];

            $title = $row['title'];
            if (empty($title)) {
                $rowErrors[] = 'title is required';
            }

            $semesterName = $row['semester_name'];
            $semester = $semesterName ? Semester::where('name', $semesterName)->first() : null;
            if (! $semester) {
                $rowErrors[] = "Semester '{$semesterName}' not found";
            }

            $courseCode = $row['course_code'];
            $course = $courseCode ? Course::where('code', $courseCode)->first() : null;
            if (! $course) {
                $rowErrors[] = "Course '{$courseCode}' not found";
            }

            $phaseTemplateName = $row['phase_template_name'];
            $phaseTemplate = $phaseTemplateName ? PhaseTemplate::where('name', $phaseTemplateName)->first() : null;
            if (! $phaseTemplate) {
                $rowErrors[] = "Phase template '{$phaseTemplateName}' not found";
            }

            $specName = $row['specialization_name'];
            $specialization = $specName ? Specialization::where('name', $specName)->first() : null;
            if (! $specialization) {
                $rowErrors[] = "Specialization '{$specName}' not found";
            }

            $supervisorUid = $row['supervisor_university_id'];
            $supervisor = $supervisorUid ? User::where('university_id', $supervisorUid)->first() : null;
            if (! $supervisor) {
                $rowErrors[] = "Supervisor with university_id '{$supervisorUid}' not found";
            } elseif (! $supervisor->hasRole('Reviewer/Supervisor')) {
                $rowErrors[] = "User '{$supervisorUid}' does not have the Reviewer/Supervisor role";
            }

            $studentUids = array_filter(array_map('trim', explode('|', $row['student_university_ids'])));
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

                    if ($semester) {
                        $existsInDb = Project::where('semester_id', $semester->id)
                            ->whereHas('students', fn ($q) => $q->where('users.id', $student->id))
                            ->exists();
                        if ($existsInDb) {
                            $rowErrors[] = "Student '{$uid}' is already in another project in semester '{$semesterName}'";
                        }

                        $trackKey = $semester->id . '-' . $student->id;
                        if (isset($studentSemesterTracker[$trackKey])) {
                            $rowErrors[] = "Student '{$uid}' appears in multiple projects for semester '{$semesterName}' in this CSV";
                        }
                        $studentSemesterTracker[$trackKey] = $rowNum;
                    }
                }
            }

            if (count($studentIds) > 4) {
                $rowErrors[] = 'Maximum 4 students per project (found ' . count($studentIds) . ')';
            }

            $reviewerUids = array_filter(array_map('trim', explode('|', $row['reviewer_university_ids'])));
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
                    if ($supervisor && $reviewer->id === $supervisor->id) {
                        $rowErrors[] = "Supervisor '{$supervisorUid}' cannot also be a reviewer";
                    }
                }
            }

            $status = empty($rowErrors) ? 'valid' : 'error';

            $previewData[] = [
                'row' => $rowNum,
                'title' => $title,
                'semester' => $semesterName,
                'course' => $courseCode,
                'supervisor' => $supervisorUid,
                'students_count' => count($studentIds),
                'reviewers_count' => count($reviewerIds),
                'status' => $status,
                'errors' => $rowErrors,
                // Resolved IDs carried forward to import():
                '_resolved' => [
                    'title' => $title,
                    'semester_id' => $semester?->id,
                    'course_id' => $course?->id,
                    'phase_template_id' => $phaseTemplate?->id,
                    'specialization_id' => $specialization?->id,
                    'supervisor_id' => $supervisor?->id,
                    'student_ids' => $studentIds,
                    'reviewer_ids' => $reviewerIds,
                ],
            ];

            if (! empty($rowErrors)) {
                $hasErrors = true;
                foreach ($rowErrors as $error) {
                    $errors[] = "Row {$rowNum}: {$error}";
                }
            }
        }

        return [
            'previewData' => $previewData,
            'previewColumns' => $this->previewColumns(),
            'errors' => $errors,
            'hasErrors' => $hasErrors,
        ];
    }

    public function import(array $previewData, array $context): array
    {
        $count = 0;

        foreach ($previewData as $row) {
            $resolved = $row['_resolved'];

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
            'title' => 'Title',
            'semester' => 'Semester',
            'course' => 'Course',
            'supervisor' => 'Supervisor',
            'students_count' => 'Students',
            'reviewers_count' => 'Reviewers',
        ];
    }
}
