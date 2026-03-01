<?php

namespace App\Filament\Admin\Resources\PhaseTemplateResource\Pages;

use App\Filament\Admin\Resources\PhaseTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhaseTemplate extends EditRecord
{
    protected static string $resource = PhaseTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
