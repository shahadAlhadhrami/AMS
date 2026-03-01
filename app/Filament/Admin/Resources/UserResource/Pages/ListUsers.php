<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Pages\BulkImportUsers;
use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(BulkImportUsers::getUrl()),
        ];
    }
}
