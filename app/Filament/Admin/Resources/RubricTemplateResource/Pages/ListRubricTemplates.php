<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\Pages;

use App\Filament\Admin\Resources\RubricTemplateResource;
use App\Models\Deliverable;
use App\Models\RubricTemplate;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRubricTemplates extends ListRecords
{
    protected static string $resource = RubricTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('importCsv')
                ->label('Import from CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    Forms\Components\TextInput::make('rubric_name')
                        ->label('Rubric Template Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., Phase 1 - Review I'),
                    Forms\Components\FileUpload::make('csv_file')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->required()
                        ->disk('local')
                        ->directory('csv-imports')
                        ->visibility('private'),
                ])
                ->action(function (array $data): void {
                    $this->importRubricFromCsv($data);
                }),
            Actions\Action::make('downloadTemplate')
                ->label('Download CSV Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return $this->downloadRubricTemplate();
                }),
        ];
    }

    protected function importRubricFromCsv(array $data): void
    {
        $csvPath = $data['csv_file'] ?? null;
        $rubricName = $data['rubric_name'] ?? null;

        if (! $csvPath || ! $rubricName) {
            Notification::make()
                ->title('Missing required fields.')
                ->danger()
                ->send();
            return;
        }

        $filePath = storage_path('app/private/' . $csvPath);
        if (! file_exists($filePath)) {
            $filePath = storage_path('app/' . $csvPath);
        }

        if (! file_exists($filePath)) {
            Notification::make()
                ->title('CSV file not found.')
                ->danger()
                ->send();
            return;
        }

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            Notification::make()
                ->title('Unable to read the CSV file.')
                ->danger()
                ->send();
            return;
        }

        $headers = fgetcsv($handle, length: 0, escape: '');
        if (! $headers) {
            fclose($handle);
            Notification::make()
                ->title('CSV file is empty or has no headers.')
                ->danger()
                ->send();
            return;
        }

        $headers = array_map('trim', array_map('strtolower', $headers));
        $requiredHeaders = ['criterion_title', 'max_score', 'is_individual', 'level_label', 'level_score'];
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
        while (($row = fgetcsv($handle, length: 0, escape: '')) !== false) {
            $rowData = array_combine($headers, array_pad($row, count($headers), ''));
            $rows[] = $rowData;
        }
        fclose($handle);

        if (empty($rows)) {
            Notification::make()
                ->title('CSV file contains no data rows.')
                ->warning()
                ->send();
            return;
        }

        // Group rows: deliverable_title → criterion_title → [level rows]
        // If deliverable_title column is absent, put all criteria under a single "General" deliverable.
        $hasDeliverableCol = in_array('deliverable_title', $headers);
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
            Notification::make()
                ->title('No valid criteria found in CSV.')
                ->warning()
                ->send();
            return;
        }

        DB::beginTransaction();
        try {
            $rubricTemplate = RubricTemplate::create([
                'name' => $rubricName,
                'version' => 1,
                'total_marks' => 0,
                'is_locked' => false,
                'created_by' => auth()->id(),
            ]);

            $totalMarks = 0;
            $deliverableSortOrder = 0;

            foreach ($deliverableGroups as $deliverableTitle => $criteriaGroups) {
                // Infer max_marks from the first row that has deliverable_max_marks, or sum criteria
                $firstRow = reset(reset($criteriaGroups));
                $deliverableMaxMarks = $hasDeliverableCol
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
                            'percentage_range' => trim($levelRow['level_percentage'] ?? '') ?: null,
                            'sort_order' => $levelSortOrder++,
                        ]);
                    }
                }

                // If deliverable_max_marks was not provided, derive it from criteria
                if ($deliverableMaxMarks == 0) {
                    $deliverable->update(['max_marks' => $deliverableTotal]);
                }

                $totalMarks += $deliverableTotal;
            }

            $rubricTemplate->update(['total_marks' => $totalMarks]);

            DB::commit();

            @unlink($filePath);

            $criteriaCount = array_sum(array_map('count', $deliverableGroups));
            Notification::make()
                ->title("Rubric '{$rubricName}' imported: " . count($deliverableGroups) . ' deliverable(s), ' . $criteriaCount . ' criteria.')
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

    protected function downloadRubricTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'deliverable_title', 'deliverable_max_marks',
                'criterion_title', 'criterion_description', 'max_score', 'is_individual',
                'level_label', 'level_score', 'level_percentage', 'level_description',
            ]);
            // Deliverable 1: Project Analysis
            fputcsv($file, ['Project Analysis', '10', 'Literature Review', 'Quality of literature review', '5', 'false', 'Excellent', '5', '90-100%', 'Outstanding']);
            fputcsv($file, ['Project Analysis', '10', 'Literature Review', 'Quality of literature review', '5', 'false', 'Good', '3', '70-80%', 'Meets expectations']);
            fputcsv($file, ['Project Analysis', '10', 'Problem Statement', 'Clarity of problem statement', '5', 'false', 'Excellent', '5', '90-100%', 'Very clear']);
            fputcsv($file, ['Project Analysis', '10', 'Problem Statement', 'Clarity of problem statement', '5', 'false', 'Good', '3', '70-80%', 'Acceptable']);
            // Deliverable 2: Presentation (individual)
            fputcsv($file, ['Presentation', '5', 'Oral Delivery', '', '5', 'true', 'Excellent', '5', '90-100%', '']);
            fputcsv($file, ['Presentation', '5', 'Oral Delivery', '', '5', 'true', 'Good', '3', '70-80%', '']);
            fclose($file);
        }, 'rubric_import_template.csv');
    }
}
