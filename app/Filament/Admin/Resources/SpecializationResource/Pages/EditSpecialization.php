<?php

namespace App\Filament\Admin\Resources\SpecializationResource\Pages;

use App\Filament\Admin\Resources\SpecializationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialization extends EditRecord
{
    protected static string $resource = SpecializationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
