<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\Pages;

use App\Filament\Admin\Resources\RubricTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRubricTemplate extends CreateRecord
{
    protected static string $resource = RubricTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['version'] = 1;

        return $data;
    }
}
