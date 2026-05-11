<?php

namespace App\Services\BulkImport;

use InvalidArgumentException;

class BulkImporterRegistry
{
    /**
     * @var array<string, BulkImporter>
     */
    protected array $importers = [];

    public function __construct(
        UsersBulkImporter $users,
        ProjectsBulkImporter $projects,
        RubricTemplatesBulkImporter $rubricTemplates,
    ) {
        foreach ([$users, $projects, $rubricTemplates] as $importer) {
            $this->importers[$importer->key()] = $importer;
        }
    }

    /**
     * @return array<string, BulkImporter>
     */
    public function all(): array
    {
        return $this->importers;
    }

    public function has(string $key): bool
    {
        return isset($this->importers[$key]);
    }

    public function get(string $key): BulkImporter
    {
        if (! isset($this->importers[$key])) {
            throw new InvalidArgumentException("Unknown bulk importer: {$key}");
        }

        return $this->importers[$key];
    }
}
