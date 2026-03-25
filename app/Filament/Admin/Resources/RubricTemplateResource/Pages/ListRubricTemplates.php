<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\Pages;

use App\Filament\Admin\Resources\RubricTemplateResource;
use App\Models\RubricFolder;
use App\Models\RubricTemplate;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRubricTemplates extends ListRecords
{
    protected static string $resource = RubricTemplateResource::class;

    protected string $view = 'filament.admin.resources.rubric-template-resource.pages.list-rubric-templates';

    #[Url]
    public ?int $currentFolderId = null;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where('rubric_folder_id', $this->currentFolderId);
    }

    public function navigateToFolder(?int $folderId): void
    {
        $this->currentFolderId = $folderId;
        $this->resetTable();
    }

    public function getCurrentFolder(): ?RubricFolder
    {
        if ($this->currentFolderId === null) {
            return null;
        }

        return RubricFolder::find($this->currentFolderId);
    }

    public function getBreadcrumbs(): array
    {
        if ($this->currentFolderId === null) {
            return [];
        }

        $folder = $this->getCurrentFolder();
        if (! $folder) {
            return [];
        }

        $breadcrumbs = $folder->getAncestors()
            ->map(fn (RubricFolder $f) => ['id' => $f->id, 'name' => $f->name])
            ->toArray();

        $breadcrumbs[] = ['id' => $folder->id, 'name' => $folder->name];

        return $breadcrumbs;
    }

    public function getSubfolders(): \Illuminate\Database\Eloquent\Collection
    {
        return RubricFolder::where('parent_id', $this->currentFolderId)
            ->orderBy('name')
            ->with('creator')
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('createFolder')
                ->label('New Folder')
                ->icon('heroicon-o-folder-plus')
                ->color('gray')
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->label('Folder Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., Phase I Templates'),
                ])
                ->action(function (array $data): void {
                    RubricFolder::create([
                        'name' => $data['name'],
                        'parent_id' => $this->currentFolderId,
                        'created_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title("Folder '{$data['name']}' created.")
                        ->success()
                        ->send();
                }),
            Actions\Action::make('renameFolder')
                ->label('Rename Folder')
                ->icon('heroicon-o-pencil')
                ->color('gray')
                ->visible(fn (): bool => $this->currentFolderId !== null && $this->getCurrentFolder()?->created_by === auth()->id())
                ->form(fn (): array => [
                    Forms\Components\TextInput::make('name')
                        ->label('New Name')
                        ->required()
                        ->maxLength(255)
                        ->default($this->getCurrentFolder()?->name),
                ])
                ->action(function (array $data): void {
                    $folder = $this->getCurrentFolder();
                    if (! $folder) {
                        return;
                    }

                    $folder->update(['name' => $data['name']]);

                    Notification::make()
                        ->title("Folder renamed to '{$data['name']}'.")
                        ->success()
                        ->send();
                }),
            Actions\Action::make('deleteFolder')
                ->label('Delete Folder')
                ->icon('heroicon-o-folder-minus')
                ->color('danger')
                ->visible(fn (): bool => $this->currentFolderId !== null && $this->getCurrentFolder()?->created_by === auth()->id())
                ->requiresConfirmation()
                ->modalHeading('Delete Folder')
                ->modalDescription('This will only delete the folder itself. Templates and subfolders inside will be moved to "No folder". Are you sure?')
                ->action(function (): void {
                    $folder = $this->getCurrentFolder();
                    if (! $folder) {
                        return;
                    }

                    // Move contents to parent before deleting
                    RubricTemplate::where('rubric_folder_id', $folder->id)
                        ->update(['rubric_folder_id' => $folder->parent_id]);
                    RubricFolder::where('parent_id', $folder->id)
                        ->update(['parent_id' => $folder->parent_id]);

                    $parentId = $folder->parent_id;
                    $folder->delete();

                    $this->navigateToFolder($parentId);

                    Notification::make()
                        ->title('Folder deleted.')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('importCsv')
                ->label('Import from CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    Forms\Components\FileUpload::make('csv_files')
                        ->label('CSV File(s)')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->required()
                        ->multiple()
                        ->disk('local')
                        ->directory('csv-imports')
                        ->visibility('private'),
                    Forms\Components\Select::make('rubric_folder_id')
                        ->label('Save to Folder')
                        ->options(fn () => RubricTemplateResource::getFolderOptions())
                        ->searchable()
                        ->nullable()
                        ->placeholder('— No folder (root) —'),
                ])
                ->action(function (array $data): void {
                    $this->importRubricsFromCsv($data);
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

    protected function importRubricsFromCsv(array $data): void
    {
        $files = $data['csv_files'] ?? [];
        $folderId = $data['rubric_folder_id'] ?? null;

        if (empty($files)) {
            Notification::make()->title('No files selected.')->danger()->send();

            return;
        }

        $successCount = 0;
        $errors = [];

        foreach ($files as $csvPath) {
            $filePath = storage_path('app/private/' . $csvPath);
            if (! file_exists($filePath)) {
                $filePath = storage_path('app/' . $csvPath);
            }

            if (! file_exists($filePath)) {
                $errors[] = "{$csvPath}: File not found.";

                continue;
            }

            $rubricName = pathinfo($csvPath, PATHINFO_FILENAME);
            // Strip Livewire temp prefix if present (e.g. "tmp-1234-filename" -> "filename")
            if (preg_match('/^[a-z0-9]+-\d+-(.+)$/', $rubricName, $m)) {
                $rubricName = $m[1];
            }

            $result = $this->importSingleCsv($filePath, $rubricName, $folderId);
            if ($result === true) {
                $successCount++;
                @unlink($filePath);
            } else {
                $errors[] = "{$rubricName}: {$result}";
            }
        }

        if ($successCount > 0) {
            Notification::make()
                ->title("{$successCount} rubric template(s) imported successfully.")
                ->success()
                ->send();
        }

        foreach ($errors as $error) {
            Notification::make()
                ->title('Import failed: ' . $error)
                ->danger()
                ->send();
        }
    }

    /**
     * @return true|string Returns true on success, or an error message string on failure.
     */
    protected function importSingleCsv(string $filePath, string $rubricName, ?int $folderId): true|string
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return 'Unable to read file.';
        }

        $headers = fgetcsv($handle, length: 0, escape: '');
        if (! $headers) {
            fclose($handle);

            return 'CSV is empty or has no headers.';
        }

        $headers = array_map('trim', array_map('strtolower', $headers));
        $requiredHeaders = ['criterion_title', 'max_score', 'is_individual', 'level_label', 'level_score'];
        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (! empty($missingHeaders)) {
            fclose($handle);

            return 'Missing columns: ' . implode(', ', $missingHeaders);
        }

        $rows = [];
        while (($row = fgetcsv($handle, length: 0, escape: '')) !== false) {
            $rows[] = array_combine($headers, array_pad($row, count($headers), ''));
        }
        fclose($handle);

        if (empty($rows)) {
            return 'CSV contains no data rows.';
        }

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
            return 'No valid criteria found.';
        }

        DB::beginTransaction();
        try {
            $rubricTemplate = RubricTemplate::create([
                'name' => $rubricName,
                'version' => 1,
                'rubric_folder_id' => $folderId,
                'total_marks' => 0,
                'is_locked' => false,
                'created_by' => auth()->id(),
            ]);

            $totalMarks = 0;
            $deliverableSortOrder = 0;

            foreach ($deliverableGroups as $deliverableTitle => $criteriaGroups) {
                $firstCriteriaGroup = reset($criteriaGroups);
                $firstRow = $firstCriteriaGroup[0] ?? [];
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

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return $e->getMessage();
        }

        return true;
    }

    protected function downloadRubricTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'deliverable_title', 'deliverable_max_marks',
                'criterion_title', 'criterion_description', 'max_score', 'is_individual',
                'level_label', 'level_score', 'level_description',
            ]);
            // Deliverable 1: Project Analysis
            fputcsv($file, ['Project Analysis', '10', 'Literature Review', 'Quality of literature review', '5', 'false', 'Excellent', '5', 'Outstanding']);
            fputcsv($file, ['Project Analysis', '10', 'Literature Review', 'Quality of literature review', '5', 'false', 'Good', '3', 'Meets expectations']);
            fputcsv($file, ['Project Analysis', '10', 'Problem Statement', 'Clarity of problem statement', '5', 'false', 'Excellent', '5', 'Very clear']);
            fputcsv($file, ['Project Analysis', '10', 'Problem Statement', 'Clarity of problem statement', '5', 'false', 'Good', '3', 'Acceptable']);
            // Deliverable 2: Presentation (individual)
            fputcsv($file, ['Presentation', '5', 'Oral Delivery', '', '5', 'true', 'Excellent', '5', '']);
            fputcsv($file, ['Presentation', '5', 'Oral Delivery', '', '5', 'true', 'Good', '3', '']);
            fclose($file);
        }, 'rubric_import_template.csv');
    }
}
