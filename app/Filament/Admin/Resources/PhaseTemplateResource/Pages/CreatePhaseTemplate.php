<?php

namespace App\Filament\Admin\Resources\PhaseTemplateResource\Pages;

use App\Filament\Admin\Resources\PhaseTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePhaseTemplate extends CreateRecord
{
    protected static string $resource = PhaseTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
