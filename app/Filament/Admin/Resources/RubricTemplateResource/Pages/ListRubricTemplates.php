<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\Pages;

use App\Filament\Admin\Resources\RubricTemplateResource;
use App\Models\Criterion;
use App\Models\RubricTemplate;
use App\Models\ScoreLevel;
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

        $headers = fgetcsv($handle);
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
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
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

        // Group rows by criterion_title
        $criteriaGroups = [];
        foreach ($rows as $row) {
            $title = trim($row['criterion_title'] ?? '');
            if (empty($title)) {
                continue;
            }
            $criteriaGroups[$title][] = $row;
        }

        if (empty($criteriaGroups)) {
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

            foreach ($criteriaGroups as $title => $levelRows) {
                $firstRow = $levelRows[0];
                $maxScore = (float) ($firstRow['max_score'] ?? 0);
                $isIndividual = filter_var($firstRow['is_individual'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $description = trim($firstRow['criterion_description'] ?? '');

                $criterion = $rubricTemplate->criteria()->create([
                    'title' => $title,
                    'description' => $description ?: null,
                    'max_score' => $maxScore,
                    'is_individual' => $isIndividual,
                ]);

                $totalMarks += $maxScore;

                $sortOrder = 0;
                foreach ($levelRows as $levelRow) {
                    $levelLabel = trim($levelRow['level_label'] ?? '');
                    if (empty($levelLabel)) {
                        continue;
                    }

                    $criterion->scoreLevels()->create([
                        'label' => $levelLabel,
                        'score_value' => (float) ($levelRow['level_score'] ?? 0),
                        'description' => trim($levelRow['level_description'] ?? '') ?: null,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            $rubricTemplate->update(['total_marks' => $totalMarks]);

            DB::commit();

            // Clean up file
            @unlink($filePath);

            Notification::make()
                ->title("Rubric template '{$rubricName}' imported with " . count($criteriaGroups) . ' criteria.')
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
            fputcsv($file, ['criterion_title', 'criterion_description', 'max_score', 'is_individual', 'level_label', 'level_score', 'level_description']);
            fputcsv($file, ['Literature Review', 'Evaluate quality of literature review', '5', 'false', 'Excellent', '5', 'Outstanding work']);
            fputcsv($file, ['Literature Review', 'Evaluate quality of literature review', '5', 'false', 'Very Good', '4', 'Above average']);
            fputcsv($file, ['Literature Review', 'Evaluate quality of literature review', '5', 'false', 'Good', '3', 'Meets expectations']);
            fputcsv($file, ['Report', 'Evaluate report quality', '5', 'false', 'Excellent', '5', 'Outstanding report']);
            fputcsv($file, ['Report', 'Evaluate report quality', '5', 'false', 'Good', '3', 'Acceptable report']);
            fputcsv($file, ['Presentation', '', '5', 'true', 'Excellent', '5', '']);
            fputcsv($file, ['Presentation', '', '5', 'true', 'Good', '3', '']);
            fclose($file);
        }, 'rubric_import_template.csv');
    }
}
