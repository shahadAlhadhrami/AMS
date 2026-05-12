<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Concerns\HidesDuringMasterDataSetup;
use App\Services\BulkImport\BulkImporter;
use App\Services\BulkImport\BulkImporterRegistry;
use App\Services\BulkImport\SpreadsheetReader;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImports extends Page
{
    use HidesDuringMasterDataSetup;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?string $navigationLabel = 'Bulk Imports';

    protected static ?string $title = 'Bulk Imports';

    protected string $view = 'filament.admin.pages.bulk-imports';

    #[Url]
    public string $type = 'users';

    public array $data = [];

    // Stage 1 → 2: column mapping state
    public bool $showMapping = false;

    public array $uploadedFiles = [];

    public array $extraContext = [];

    public array $csvHeaders = [];

    public array $columnMapping = [];

    // Stage 2 → 3: preview & import state
    public array $previewData = [];

    public array $previewColumns = [];

    public array $validationErrors = [];

    public array $validationWarnings = [];

    public bool $hasErrors = false;

    public bool $hasWarnings = false;

    // Stage 3 → 4: optional context step (semester/course/phase/specialization for projects, etc.)
    public bool $showContextStep = false;

    public array $contextData = [];

    public ?string $confirmedWarningSignature = null;

    // Post-import state
    public bool $imported = false;

    public int $importedCount = 0;

    public array $importResults = [];

    public function mount(): void
    {
        if (! $this->registry()->has($this->type)) {
            $this->type = 'users';
        }

        $this->resetWizard();
        $this->form->fill();
    }

    public function updatedType(): void
    {
        if (! $this->registry()->has($this->type)) {
            $this->type = 'users';
        }

        $this->resetWizard();
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        $importer = $this->importer();

        $fileField = Forms\Components\FileUpload::make('csvPath')
            ->key("csvPath-{$importer->key()}")
            ->label($importer->supportsMultiFile() ? 'CSV / Excel File(s)' : 'CSV / Excel File')
            ->helperText('Accepts .csv, .xlsx, or .ods. Merged cells in Excel are read correctly — values flow down through following rows.')
            ->acceptedFileTypes([
                'text/csv',
                'text/plain',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.oasis.opendocument.spreadsheet',
            ])
            ->required()
            ->disk('local')
            ->directory('csv-imports')
            ->storeFileNamesIn('csvOriginalNames')
            ->visibility('private');

        if ($importer->supportsMultiFile()) {
            $fileField = $fileField
                ->multiple()
                ->appendFiles()
                ->maxParallelUploads(6);
        }

        $extraFields = $importer->extraFormFields();
        $components = $importer->key() === 'rubric-templates'
            ? array_merge($extraFields, [$fileField])
            : array_merge([$fileField], $extraFields);

        return $form
            ->components($components)
            ->statePath('data');
    }

    public function contextForm(Schema $form): Schema
    {
        return $form
            ->components($this->importer()->contextFormFields())
            ->statePath('contextData');
    }

    protected function getForms(): array
    {
        return [
            'form',
            'contextForm',
        ];
    }

    public function importerNeedsContextStep(): bool
    {
        return ! empty($this->importer()->contextFormFields());
    }

    public function getImporters(): array
    {
        return $this->registry()->all();
    }

    public function getImporter(): BulkImporter
    {
        return $this->importer();
    }

    protected function importer(): BulkImporter
    {
        return $this->registry()->get($this->type);
    }

    protected function registry(): BulkImporterRegistry
    {
        return app(BulkImporterRegistry::class);
    }

    public function downloadTemplate(): StreamedResponse
    {
        return $this->importer()->downloadTemplate();
    }

    /**
     * Stage 1 → mapping (or directly to preview for fixed-schema importers).
     */
    public function uploadAndPreview(): void
    {
        $importer = $this->importer();
        $formState = $this->form->getState();

        $this->imported = false;
        $this->importResults = [];
        $this->previewData = [];
        $this->validationErrors = [];
        $this->validationWarnings = [];
        $this->hasErrors = false;
        $this->hasWarnings = false;
        $this->confirmedWarningSignature = null;
        $this->showMapping = false;

        $files = $this->normalizeUploadedFiles($formState['csvPath'] ?? null);

        if (empty($files)) {
            Notification::make()->title('No CSV file uploaded.')->danger()->send();

            return;
        }

        $this->uploadedFiles = $files;
        $this->extraContext = $this->extractExtraContext($formState);

        if (! $importer->requiresColumnMapping()) {
            // Fixed-schema importers go straight to validation.
            $this->runValidation();

            return;
        }

        // Mapping importers: read headers from the first file.
        $firstFilePath = $this->resolveFilePath($files[0]);
        if (! $firstFilePath) {
            Notification::make()->title('Spreadsheet file not found.')->danger()->send();

            return;
        }

        try {
            $parsed = SpreadsheetReader::read($firstFilePath);
        } catch (\Throwable $e) {
            Notification::make()->title('Unable to read the spreadsheet: '.$e->getMessage())->danger()->send();

            return;
        }

        if (empty($parsed['headers'])) {
            Notification::make()->title('Spreadsheet is empty or has no headers.')->danger()->send();

            return;
        }

        $this->csvHeaders = $parsed['headers'];

        // Auto-map system fields whose names already appear in the CSV (case-insensitive).
        $normalised = array_map('strtolower', $this->csvHeaders);
        $this->columnMapping = [];
        foreach ($importer->systemFields() as $field) {
            $idx = array_search($field, $normalised, true);
            $this->columnMapping[$field] = $idx !== false ? $this->csvHeaders[$idx] : '';
        }

        $this->showMapping = true;
    }

    /**
     * Stage 2 → preview & validate.
     */
    public function confirmMappingAndPreview(): void
    {
        $importer = $this->importer();

        $unmapped = array_filter($this->columnMapping, fn ($v) => empty($v));
        if (! empty($unmapped)) {
            Notification::make()
                ->title('Please map all required columns: '.implode(', ', array_keys($unmapped)))
                ->danger()
                ->send();

            return;
        }

        $this->showMapping = false;
        $this->runValidation();
    }

    protected function runValidation(): void
    {
        $importer = $this->importer();
        $result = $importer->validateRows($this->uploadedFiles, $this->columnMapping, $this->extraContext);

        $this->previewData = $result['previewData'] ?? [];
        $this->previewColumns = $result['previewColumns'] ?? [];
        $this->validationErrors = $result['errors'] ?? [];
        $this->validationWarnings = $result['warnings'] ?? [];
        $this->hasErrors = $result['hasErrors'] ?? false;
        $this->hasWarnings = $result['hasWarnings'] ?? ! empty($this->validationWarnings);
        $this->confirmedWarningSignature = null;

        if (empty($this->previewData) && empty($this->validationErrors)) {
            Notification::make()->title('CSV file contains no data rows.')->warning()->send();
        }
    }

    /**
     * Preview → context step (only used by importers whose contextFormFields() is non-empty).
     */
    public function continueToContextStep(): void
    {
        if ($this->hasErrors || empty($this->previewData)) {
            Notification::make()
                ->title('Cannot continue: fix validation errors first.')
                ->danger()
                ->send();

            return;
        }

        if (! $this->importerNeedsContextStep()) {
            $this->runImport();

            return;
        }

        $this->showContextStep = true;
        $this->contextForm->fill();
    }

    /**
     * Context step → validate context → import.
     */
    public function confirmContextAndImport(): void
    {
        if (! $this->importerNeedsContextStep()) {
            $this->runImport();

            return;
        }

        $state = $this->contextForm->getState();
        $this->contextData = $state;

        $importer = $this->importer();
        $contextErrors = $importer->validateContext($this->previewData, $this->contextData);

        // Replace any prior context-validation errors so retries don't accumulate, while
        // keeping the per-row errors from the preview stage.
        $this->validationErrors = array_values(array_filter(
            $this->validationErrors,
            fn ($e) => ! str_starts_with($e, '[Context] '),
        ));
        $this->validationWarnings = array_values(array_filter(
            $this->validationWarnings,
            fn ($e) => ! str_starts_with($e, '[Context] '),
        ));
        $this->hasErrors = ! empty($this->validationErrors);

        $newErrors = array_map(fn ($e) => "[Context] {$e}", $contextErrors['errors'] ?? []);
        $newWarnings = array_map(fn ($e) => "[Context] {$e}", $contextErrors['warnings'] ?? []);

        if (! empty($newWarnings)) {
            $this->validationWarnings = array_merge($this->validationWarnings, $newWarnings);
        }

        $this->hasWarnings = ! empty($this->validationWarnings);

        if (! empty($newErrors)) {
            $this->validationErrors = array_merge($this->validationErrors, $newErrors);
            $this->hasErrors = true;
            Notification::make()
                ->title('Context validation failed — review errors and adjust your selections.')
                ->danger()
                ->send();

            return;
        }

        if (! empty($newWarnings)) {
            $warningSignature = md5(json_encode([
                'context' => $this->contextData,
                'warnings' => $newWarnings,
            ]));

            if ($this->confirmedWarningSignature !== $warningSignature) {
                $this->confirmedWarningSignature = $warningSignature;

                Notification::make()
                    ->title('Assignment warnings found. Review them, then click import again to overwrite existing assignments.')
                    ->warning()
                    ->send();

                return;
            }
        } else {
            $this->confirmedWarningSignature = null;
        }

        $this->runImport();
    }

    public function runImport(): void
    {
        if ($this->hasErrors || empty($this->previewData)) {
            Notification::make()
                ->title('Cannot import: fix validation errors first.')
                ->danger()
                ->send();

            return;
        }

        $importer = $this->importer();
        $context = array_merge($this->extraContext, $this->contextData);

        DB::beginTransaction();
        try {
            $result = $importer->import($this->previewData, $context);
            DB::commit();

            $this->imported = true;
            $this->importedCount = $result['count'] ?? 0;
            $this->importResults = $result['results'] ?? [];
            $this->validationWarnings = [];
            $this->hasWarnings = false;
            $this->confirmedWarningSignature = null;

            Notification::make()
                ->title("Successfully imported {$this->importedCount} {$importer->label()}.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();

            Notification::make()
                ->title('Import failed: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function downloadResults(): ?StreamedResponse
    {
        $importer = $this->importer();
        if (! $importer->hasResultsDownload()) {
            return null;
        }

        return $importer->downloadResults($this->importResults);
    }

    public function resetImport(): void
    {
        $this->resetWizard();
        $this->form->fill();
    }

    protected function resetWizard(): void
    {
        $this->data = [];
        $this->showMapping = false;
        $this->uploadedFiles = [];
        $this->extraContext = [];
        $this->csvHeaders = [];

        $importer = $this->importer();
        $this->columnMapping = $importer->requiresColumnMapping()
            ? array_fill_keys($importer->systemFields(), '')
            : [];

        $this->previewData = [];
        $this->previewColumns = [];
        $this->validationErrors = [];
        $this->validationWarnings = [];
        $this->hasErrors = false;
        $this->hasWarnings = false;

        $this->showContextStep = false;
        $this->contextData = [];
        $this->confirmedWarningSignature = null;

        $this->imported = false;
        $this->importedCount = 0;
        $this->importResults = [];
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeUploadedFiles(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($v) => ! empty($v)));
        }

        return $value ? [$value] : [];
    }

    protected function extractExtraContext(array $formState): array
    {
        unset($formState['csvPath']);

        return $formState;
    }

    protected function resolveFilePath(string $csvPath): ?string
    {
        $filePath = storage_path('app/private/'.$csvPath);
        if (! file_exists($filePath)) {
            $filePath = storage_path('app/'.$csvPath);
        }

        return file_exists($filePath) ? $filePath : null;
    }
}
