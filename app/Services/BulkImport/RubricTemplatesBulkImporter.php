<?php

namespace App\Services\BulkImport;

use App\Filament\Admin\Resources\RubricTemplateResource;
use App\Models\RubricTemplate;
use Filament\Forms;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RubricTemplatesBulkImporter implements BulkImporter
{
    use ResolvesCsvFilePath;

    protected array $requiredHeaders = [
        'criterion_title',
        'max_score',
        'is_individual',
        'level_label',
        'level_score',
    ];

    public function key(): string
    {
        return 'rubric-templates';
    }

    public function label(): string
    {
        return 'Rubric Templates';
    }

    public function description(): string
    {
        return 'Upload one or more CSV files. Each file becomes a rubric template; rows describe deliverables, criteria, and score levels.';
    }

    public function requiresColumnMapping(): bool
    {
        return false;
    }

    public function supportsMultiFile(): bool
    {
        return true;
    }

    public function extraFormFields(): array
    {
        return [
            Forms\Components\Select::make('rubric_folder_id')
                ->label('Save to Folder')
                ->options(fn () => RubricTemplateResource::getFolderOptions())
                ->searchable()
                ->nullable()
                ->placeholder('— No folder (root) —'),
        ];
    }

    public function systemFields(): array
    {
        return [
            'deliverable_title',
            'deliverable_max_marks',
            'criterion_title',
            'criterion_description',
            'max_score',
            'is_individual',
            'level_label',
            'level_score',
            'level_description',
        ];
    }

    public function systemFieldLabels(): array
    {
        return [
            'deliverable_title' => 'Deliverable Title',
            'deliverable_max_marks' => 'Deliverable Max Marks',
            'criterion_title' => 'Criterion Title',
            'criterion_description' => 'Criterion Description',
            'max_score' => 'Max Score',
            'is_individual' => 'Is Individual',
            'level_label' => 'Level Label',
            'level_score' => 'Level Score',
            'level_description' => 'Level Description',
        ];
    }

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'deliverable_title', 'deliverable_max_marks',
                'criterion_title', 'criterion_description', 'max_score', 'is_individual',
                'level_label', 'level_score', 'level_description',
            ]);
            fputcsv($file, ['Project Analysis', '10', 'Literature Review', 'Quality of literature review', '5', 'false', 'Excellent', '5', 'Outstanding']);
            fputcsv($file, ['Project Analysis', '10', 'Literature Review', 'Quality of literature review', '5', 'false', 'Good', '3', 'Meets expectations']);
            fputcsv($file, ['Project Analysis', '10', 'Problem Statement', 'Clarity of problem statement', '5', 'false', 'Excellent', '5', 'Very clear']);
            fputcsv($file, ['Project Analysis', '10', 'Problem Statement', 'Clarity of problem statement', '5', 'false', 'Good', '3', 'Acceptable']);
            fputcsv($file, ['Presentation', '5', 'Oral Delivery', '', '5', 'true', 'Excellent', '5', '']);
            fputcsv($file, ['Presentation', '5', 'Oral Delivery', '', '5', 'true', 'Good', '3', '']);
            fclose($file);
        }, 'rubric_import_template.csv');
    }

    public function validateRows(array $files, array $columnMapping, array $context): array
    {
        $previewData = [];
        $errors = [];
        $hasErrors = false;

        if (empty($files)) {
            return [
                'previewData' => [],
                'previewColumns' => $this->previewColumns(),
                'errors' => ['No files uploaded.'],
                'hasErrors' => true,
            ];
        }

        foreach ($files as $index => $csvPath) {
            $rubricName = $this->extractRubricName($csvPath);
            $filePath = $this->resolveCsvFilePath($csvPath);

            $row = [
                'file' => $rubricName,
                'csv_path' => $csvPath,
                'deliverables_count' => 0,
                'criteria_count' => 0,
                'levels_count' => 0,
                'total_marks' => 0,
                'status' => 'error',
                'errors' => [],
                '_parsed' => null,
            ];

            if (! $filePath) {
                $row['errors'][] = 'File not found. Please re-upload.';
                $previewData[] = $row;
                $errors[] = "{$rubricName}: file not found.";
                $hasErrors = true;
                continue;
            }

            $parseResult = $this->parseSingleFile($filePath);

            if (isset($parseResult['error'])) {
                $row['errors'][] = $parseResult['error'];
                $previewData[] = $row;
                $errors[] = "{$rubricName}: {$parseResult['error']}";
                $hasErrors = true;
                continue;
            }

            $row['deliverables_count'] = $parseResult['deliverables_count'];
            $row['criteria_count'] = $parseResult['criteria_count'];
            $row['levels_count'] = $parseResult['levels_count'];
            $row['total_marks'] = $parseResult['total_marks'];
            $row['status'] = 'valid';
            $row['_parsed'] = [
                'rubric_name' => $rubricName,
                'deliverable_groups' => $parseResult['deliverable_groups'],
                'has_deliverable_col' => $parseResult['has_deliverable_col'],
            ];

            $previewData[] = $row;
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
        $folderId = $context['rubric_folder_id'] ?? null;
        $count = 0;

        foreach ($previewData as $row) {
            if ($row['status'] !== 'valid' || ! is_array($row['_parsed'])) {
                continue;
            }

            $parsed = $row['_parsed'];
            $rubricTemplate = RubricTemplate::create([
                'name' => $parsed['rubric_name'],
                'version' => 1,
                'rubric_folder_id' => $folderId,
                'total_marks' => 0,
                'is_locked' => false,
                'created_by' => auth()->id(),
            ]);

            $totalMarks = 0;
            $deliverableSortOrder = 0;

            foreach ($parsed['deliverable_groups'] as $deliverableTitle => $criteriaGroups) {
                $firstCriteriaGroup = reset($criteriaGroups);
                $firstRow = $firstCriteriaGroup[0] ?? [];
                $deliverableMaxMarks = $parsed['has_deliverable_col']
                    ? (float) ($firstRow['deliverable_max_marks'] ?? 0)
                    : 0;

                $deliverable = $rubricTemplate->deliverables()->create([
                    'title' => $deliverableTitle,
                    'max_marks' => $deliverableMaxMarks,
                    'sort_order' => $deliverableSortOrder++,
                ]);

                $criterionSortOrder = 0;
                $deliverableTotal = 0;

                foreach ($criteriaGroups as $criterionTitle => $levelRows) {
                    $firstCriterionRow = $levelRows[0];
                    $maxScore = (float) ($firstCriterionRow['max_score'] ?? 0);
                    $isIndividual = filter_var($firstCriterionRow['is_individual'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $description = trim($firstCriterionRow['criterion_description'] ?? '');

                    $criterion = $deliverable->criteria()->create([
                        'rubric_template_id' => $rubricTemplate->id,
                        'title' => $criterionTitle,
                        'description' => $description ?: null,
                        'max_score' => $maxScore,
                        'is_individual' => $isIndividual,
                        'sort_order' => $criterionSortOrder++,
                    ]);

                    $deliverableTotal += $maxScore;
                    $levelSortOrder = 0;

                    foreach ($levelRows as $levelRow) {
                        $levelLabel = trim($levelRow['level_label'] ?? '');
                        if (empty($levelLabel)) {
                            continue;
                        }

                        $criterion->scoreLevels()->create([
                            'label' => $levelLabel,
                            'score_value' => (float) ($levelRow['level_score'] ?? 0),
                            'description' => trim($levelRow['level_description'] ?? '') ?: null,
                            'sort_order' => $levelSortOrder++,
                        ]);
                    }
                }

                if ($deliverableMaxMarks == 0) {
                    $deliverable->update(['max_marks' => $deliverableTotal]);
                }

                $totalMarks += $deliverableTotal;
            }

            $rubricTemplate->update(['total_marks' => $totalMarks]);

            $filePath = $this->resolveCsvFilePath($row['csv_path']);
            if ($filePath) {
                @unlink($filePath);
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
        return response()->streamDownload(fn () => null, 'rubric_templates_import_results.csv');
    }

    protected function previewColumns(): array
    {
        return [
            'file' => 'Template (file)',
            'deliverables_count' => 'Deliverables',
            'criteria_count' => 'Criteria',
            'levels_count' => 'Levels',
            'total_marks' => 'Total Marks',
        ];
    }

    protected function extractRubricName(string $csvPath): string
    {
        $name = pathinfo($csvPath, PATHINFO_FILENAME);
        // Strip Livewire temp prefix if present (e.g. "tmp-1234-filename" -> "filename")
        if (preg_match('/^[a-z0-9]+-\d+-(.+)$/', $name, $m)) {
            $name = $m[1];
        }
        return $name;
    }

    /**
     * Parse a single rubric CSV and return its structure (or an 'error' key on failure).
     */
    protected function parseSingleFile(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return ['error' => 'Unable to read file.'];
        }

        $headers = fgetcsv($handle, length: 0, escape: '');
        if (! $headers) {
            fclose($handle);
            return ['error' => 'CSV is empty or has no headers.'];
        }

        $headers = array_map('trim', array_map('strtolower', $headers));
        $missingHeaders = array_diff($this->requiredHeaders, $headers);
        if (! empty($missingHeaders)) {
            fclose($handle);
            return ['error' => 'Missing columns: ' . implode(', ', $missingHeaders)];
        }

        $rows = [];
        while (($row = fgetcsv($handle, length: 0, escape: '')) !== false) {
            $rows[] = array_combine($headers, array_pad($row, count($headers), ''));
        }
        fclose($handle);

        if (empty($rows)) {
            return ['error' => 'CSV contains no data rows.'];
        }

        $hasDeliverableCol = in_array('deliverable_title', $headers, true);
        $deliverableGroups = [];

        foreach ($rows as $row) {
            $criterionTitle = trim($row['criterion_title'] ?? '');
            if (empty($criterionTitle)) {
                continue;
            }
            $deliverableTitle = $hasDeliverableCol ? trim($row['deliverable_title'] ?? '') : '';
            $deliverableTitle = $deliverableTitle ?: 'General';
            $deliverableGroups[$deliverableTitle][$criterionTitle][] = $row;
        }

        if (empty($deliverableGroups)) {
            return ['error' => 'No valid criteria found.'];
        }

        // Compute summary stats for the preview row.
        $deliverablesCount = count($deliverableGroups);
        $criteriaCount = 0;
        $levelsCount = 0;
        $totalMarks = 0.0;

        foreach ($deliverableGroups as $criteriaGroups) {
            foreach ($criteriaGroups as $levelRows) {
                $criteriaCount++;
                $totalMarks += (float) ($levelRows[0]['max_score'] ?? 0);
                foreach ($levelRows as $levelRow) {
                    if (trim($levelRow['level_label'] ?? '') !== '') {
                        $levelsCount++;
                    }
                }
            }
        }

        return [
            'deliverable_groups' => $deliverableGroups,
            'has_deliverable_col' => $hasDeliverableCol,
            'deliverables_count' => $deliverablesCount,
            'criteria_count' => $criteriaCount,
            'levels_count' => $levelsCount,
            'total_marks' => $totalMarks,
        ];
    }
}
