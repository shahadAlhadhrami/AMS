<?php

namespace App\Filament\Admin\Pages;

use App\Services\BulkImport\BulkImporter;
use App\Services\BulkImport\BulkImporterRegistry;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImports extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string | \UnitEnum | null $navigationGroup = 'Tools';

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
    public bool $hasErrors = false;

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
            ->label($importer->supportsMultiFile() ? 'CSV File(s)' : 'CSV File')
            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
            ->required()
            ->disk('local')
            ->directory('csv-imports')
            ->visibility('private');

        if ($importer->supportsMultiFile()) {
            $fileField = $fileField->multiple();
        }

        return $form
            ->components(array_merge([$fileField], $importer->extraFormFields()))
            ->statePath('data');
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
        $this->hasErrors = false;
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
            Notification::make()->title('CSV file not found.')->danger()->send();
            return;
        }

        $handle = fopen($firstFilePath, 'r');
        if (! $handle) {
            Notification::make()->title('Unable to read the CSV file.')->danger()->send();
            return;
        }

        $headers = fgetcsv($handle, length: 0, escape: '');
        fclose($handle);

        if (! $headers) {
            Notification::make()->title('CSV file is empty or has no headers.')->danger()->send();
            return;
        }

        $this->csvHeaders = array_map('trim', $headers);

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
                ->title('Please map all required columns: ' . implode(', ', array_keys($unmapped)))
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
        $this->hasErrors = $result['hasErrors'] ?? false;

        if (empty($this->previewData) && empty($this->validationErrors)) {
            Notification::make()->title('CSV file contains no data rows.')->warning()->send();
        }
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

        DB::beginTransaction();
        try {
            $result = $importer->import($this->previewData, $this->extraContext);
            DB::commit();

            $this->imported = true;
            $this->importedCount = $result['count'] ?? 0;
            $this->importResults = $result['results'] ?? [];

            Notification::make()
                ->title("Successfully imported {$this->importedCount} {$importer->label()}.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();

            Notification::make()
                ->title('Import failed: ' . $e->getMessage())
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
        $this->hasErrors = false;

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
        $filePath = storage_path('app/private/' . $csvPath);
        if (! file_exists($filePath)) {
            $filePath = storage_path('app/' . $csvPath);
        }
        return file_exists($filePath) ? $filePath : null;
    }
}
