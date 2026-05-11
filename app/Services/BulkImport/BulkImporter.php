<?php

namespace App\Services\BulkImport;

use Symfony\Component\HttpFoundation\StreamedResponse;

interface BulkImporter
{
    /**
     * Stable identifier used in URLs and form state. e.g. 'users', 'projects', 'rubric-templates'.
     */
    public function key(): string;

    /**
     * Human label shown on the tab.
     */
    public function label(): string;

    /**
     * Short helper text shown below the tab strip.
     */
    public function description(): string;

    /**
     * Whether the wizard should show the column mapping step.
     * Rubric Templates use a fixed schema, so they skip mapping.
     */
    public function requiresColumnMapping(): bool;

    /**
     * Whether the file upload accepts multiple files.
     */
    public function supportsMultiFile(): bool;

    /**
     * Filament form components to render in the upload form alongside the file input
     * (e.g. a RubricFolder picker for Rubric Templates).
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    public function extraFormFields(): array;

    /**
     * Field keys the user must map columns to (mapping importers) OR
     * the canonical column names the CSV must contain (fixed-schema importers).
     * Order matters — it drives the mapping UI order.
     *
     * @return array<int, string>
     */
    public function systemFields(): array;

    /**
     * Friendly labels for system fields, keyed by field key.
     *
     * @return array<string, string>
     */
    public function systemFieldLabels(): array;

    /**
     * Download the per-type CSV template.
     */
    public function downloadTemplate(): StreamedResponse;

    /**
     * Stage 2: validate the mapped/parsed rows. Receives:
     *  - $files: array of relative storage paths under the local disk (always an array, even for single-file importers).
     *  - $columnMapping: ['system_field' => 'csv_header', ...] (empty for fixed-schema importers).
     *  - $context: extra form values (e.g. ['rubric_folder_id' => 42]).
     *
     * Returns:
     *  [
     *    'previewData' => array<int, array>,    // per-row records for the preview table
     *    'previewColumns' => array<string,string>, // ['key' => 'Header'] for the preview table
     *    'errors' => array<int, string>,        // human-readable error messages, prefixed with row/file context
     *    'hasErrors' => bool,
     *  ]
     */
    public function validateRows(array $files, array $columnMapping, array $context): array;

    /**
     * Stage 3: persist previously-validated rows. Wrapped by the caller in a DB transaction.
     *
     * Returns:
     *  [
     *    'count' => int,
     *    'results' => array<int, array>, // rows for an optional results CSV (e.g. with generated passwords)
     *  ]
     */
    public function import(array $previewData, array $context): array;

    /**
     * Whether `downloadResults` produces a meaningful artifact for this importer.
     */
    public function hasResultsDownload(): bool;

    /**
     * Optional results CSV (e.g. generated passwords for Users).
     */
    public function downloadResults(array $results): StreamedResponse;
}
