<?php

namespace App\Filament\Admin\Resources\AllowedEmailDomainResource\Pages;

use App\Filament\Admin\Resources\AllowedEmailDomainResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAllowedEmailDomains extends ListRecords
{
    protected static string $resource = AllowedEmailDomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
