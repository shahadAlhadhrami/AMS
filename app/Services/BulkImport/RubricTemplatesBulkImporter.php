<?php

namespace App\Services\BulkImport;

use App\Filament\Admin\Resources\RubricTemplateResource;
use App\Models\RubricTemplate;
use Filament\Forms;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options as XlsxOptions;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
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

    public function contextFormFields(): array
    {
        return [];
    }

    public function validateContext(array $previewData, array $context): array
    {
        return ['errors' => [], 'hasErrors' => false];
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
        // Real rubrics always have 6 score levels per criterion.
        // Values go in the top-left cell of each merged region only; other cells are empty.
        //
        // Row layout (header = row 1):
        //   Deliverable 1 "Project Analysis"  → rows 2–13  (2 criteria × 6 levels)
        //     Criterion 1 "Problem Statement" → rows 2–7
        //     Criterion 2 "Literature Study"  → rows 8–13
        //   Deliverable 2 "Presentation"      → rows 14–25 (2 criteria × 6 levels)
        //     Criterion 1 "Oral Presentation" → rows 14–19
        //     Criterion 2 "Individual Contribution" → rows 20–25  (is_individual = true)
        $dataRows = [
            // D1 C1
            ['Project Analysis', '10', 'Problem Statement',      'Clarity of problem definition, objectives, and scope',   '5', 'false', 'Excellent',    '5',    'Problem statement is exceptionally clear, well-defined, and comprehensive.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Very Good',    '4',    'Problem statement is clear and well-defined with minor gaps.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Good',         '3',    'Problem statement is mostly clear with some gaps.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Satisfactory', '2',    'Problem statement is partially clear with notable gaps.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Poor',         '1',    'Problem statement is unclear and poorly defined.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Very Poor',    '0.25', 'Problem statement is missing or incomprehensible.'],
            // D1 C2
            ['',                 '',   'Literature Study',       'Quality and relevance of reviewed literature',           '5', 'false', 'Excellent',    '5',    'Literature is comprehensive, relevant, and critically analysed.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Very Good',    '4',    'Literature is mostly comprehensive and relevant.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Good',         '3',    'Literature review is adequate with some gaps.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Satisfactory', '2',    'Literature review is minimal and partially relevant.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Poor',         '1',    'Literature review is very limited.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Very Poor',    '0.25', 'No meaningful literature review present.'],
            // D2 C1
            ['Presentation',     '10', 'Oral Presentation',      'Clarity and effectiveness of oral delivery',             '5', 'false', 'Excellent',    '5',    'Presentation is exceptionally clear, engaging, and well-delivered.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Very Good',    '4',    'Presentation is clear and mostly engaging.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Good',         '3',    'Presentation is reasonably clear with minor issues.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Satisfactory', '2',    'Presentation is partially clear with some issues.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Poor',         '1',    'Presentation is unclear and poorly delivered.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Very Poor',    '0.25', 'Presentation is incomprehensible or absent.'],
            // D2 C2 — individual criterion: each group member is scored separately
            ['',                 '',   'Individual Contribution', 'Student\'s personal contribution to the project',       '5', 'true',  'Excellent',    '5',    'Effectively completed all assigned tasks.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Very Good',    '4',    'Most of the assigned tasks were completed.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Good',         '3',    'Partially completed the assigned tasks.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Satisfactory', '2',    'Minimally completed the assigned tasks.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Poor',         '1',    'Poorly completed the assigned tasks.'],
            ['',                 '',   '',                       '',                                                        '',  '',      'Very Poor',    '0.25', 'Did not complete the assigned tasks.'],
        ];

        $tmpPath = tempnam(sys_get_temp_dir(), 'ams_tpl_') . '.xlsx';

        $options = new XlsxOptions();
        $options->setColumnWidth(22, 1);
        $options->setColumnWidth(22, 2);
        $options->setColumnWidth(25, 3);
        $options->setColumnWidth(32, 4);
        $options->setColumnWidth(12, 5);
        $options->setColumnWidth(15, 6);
        $options->setColumnWidth(15, 7);
        $options->setColumnWidth(12, 8);
        $options->setColumnWidth(40, 9);

        // Deliverable-level merges (cols 0–1, 0-indexed)
        $options->mergeCells(0, 2, 0, 13);
        $options->mergeCells(1, 2, 1, 13);
        $options->mergeCells(0, 14, 0, 25);
        $options->mergeCells(1, 14, 1, 25);

        // Criterion-level merges (cols 2–5): each criterion spans 6 level rows
        foreach ([[2, 7], [8, 13], [14, 19], [20, 25]] as [$start, $end]) {
            for ($col = 2; $col <= 5; $col++) {
                $options->mergeCells($col, $start, $col, $end);
            }
        }

        $writer = new XlsxWriter($options);
        $writer->openToFile($tmpPath);

        $headerStyle = (new Style())->setFontBold();
        $writer->addRow(Row::fromValues([
            'deliverable_title', 'deliverable_max_marks',
            'criterion_title', 'criterion_description', 'max_score', 'is_individual',
            'level_label', 'level_score', 'level_description',
        ], $headerStyle));

        foreach ($dataRows as $rowData) {
            $writer->addRow(Row::fromValues($rowData));
        }

        $writer->close();

        return response()->streamDownload(function () use ($tmpPath) {
            readfile($tmpPath);
            @unlink($tmpPath);
        }, 'rubric_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
        try {
            $parsed = SpreadsheetReader::read($filePath);
        } catch (\Throwable $e) {
            return ['error' => 'Unable to read file: ' . $e->getMessage()];
        }

        if (empty($parsed['headers'])) {
            return ['error' => 'Spreadsheet is empty or has no headers.'];
        }

        $headers = array_map('strtolower', $parsed['headers']);
        $missingHeaders = array_diff($this->requiredHeaders, $headers);
        if (! empty($missingHeaders)) {
            return ['error' => 'Missing columns: ' . implode(', ', $missingHeaders)];
        }

        $rows = [];
        foreach ($parsed['rows'] as $rawCells) {
            $rows[] = array_combine($headers, array_pad($rawCells, count($headers), ''));
        }

        if (empty($rows)) {
            return ['error' => 'Spreadsheet contains no data rows.'];
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
