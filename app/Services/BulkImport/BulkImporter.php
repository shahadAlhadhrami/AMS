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
     *  - $context: extra form values from the upload step (e.g. ['rubric_folder_id' => 42]).
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
     * Filament form components for the post-preview "context" step (Stage 3).
     * Return an empty array if the importer does not need a context step — the wizard
     * will then jump straight from preview to import.
     *
     * Example use: Projects asks for Semester / Course / Phase Template / Specialization
     * here so those values aren't duplicated on every CSV row.
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    public function contextFormFields(): array;

    /**
     * Stage 3.5: validate the chosen context against the already-validated preview data.
     * Runs only when contextFormFields() is non-empty, and only after the admin submits
     * the context form. Used for checks that need the context values to be meaningful
     * (e.g. "is this student already in another project for the chosen semester?").
     *
     * Returns the same shape as validateRows() (sans previewColumns) so the page can merge
     * errors into the existing list.
     *
     * @return array{errors: array<int, string>, hasErrors: bool}
     */
    public function validateContext(array $previewData, array $context): array;

    /**
     * Stage 4: persist previously-validated rows. Wrapped by the caller in a DB transaction.
     * $context is the merge of the upload-step extras and the context-step values.
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
