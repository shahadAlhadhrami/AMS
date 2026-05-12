<?php

namespace App\Services\BulkImport;

use OpenSpout\Reader\Common\Creator\ReaderFactory;
use RuntimeException;

/**
 * Reads tabular data from .csv, .xlsx, and .ods spreadsheets into a uniform shape.
 *
 * OpenSpout does not expand merged cells — the merge's value comes back in the top-left
 * cell only, the rest are empty. Callers that need fill-down semantics (Projects bulk
 * import: project_title / supervisor_id repeated across a project's student rows) apply
 * it themselves on the rows returned here.
 */
class SpreadsheetReader
{
    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>}
     */
    public static function read(string $absolutePath): array
    {
        if (! file_exists($absolutePath)) {
            throw new RuntimeException("Spreadsheet file not found: {$absolutePath}");
        }

        $reader = ReaderFactory::createFromFile($absolutePath);
        $reader->open($absolutePath);

        $headers = [];
        $rows = [];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $isFirst = true;
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = array_map(
                        fn ($v) => trim((string) $v),
                        $row->toArray(),
                    );

                    if ($isFirst) {
                        $headers = $cells;
                        $isFirst = false;
                        continue;
                    }

                    // Skip fully-empty rows. Pad short rows to the header width so
                    // downstream array_combine() lines up correctly.
                    if (count(array_filter($cells, fn ($c) => $c !== '')) === 0) {
                        continue;
                    }

                    $rows[] = array_pad($cells, count($headers), '');
                }

                break; // first sheet only
            }
        } finally {
            $reader->close();
        }

        return ['headers' => $headers, 'rows' => $rows];
    }
}
