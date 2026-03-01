<?php

namespace App\Filament\Admin\Resources\SpecializationResource\Pages;

use App\Filament\Admin\Resources\SpecializationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecializations extends ListRecords
{
    protected static string $resource = SpecializationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
