<?php

namespace App\Services\BulkImport;

trait ResolvesCsvFilePath
{
    /**
     * Resolve a Livewire FileUpload storage path to a real absolute file path.
     * Returns null if the file cannot be located.
     */
    protected function resolveCsvFilePath(?string $csvPath): ?string
    {
        if (! $csvPath) {
            return null;
        }

        $filePath = storage_path('app/private/' . $csvPath);
        if (! file_exists($filePath)) {
            $filePath = storage_path('app/' . $csvPath);
        }

        return file_exists($filePath) ? $filePath : null;
    }
}
