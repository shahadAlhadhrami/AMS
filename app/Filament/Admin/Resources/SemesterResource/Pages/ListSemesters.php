<?php

namespace App\Filament\Admin\Resources\SemesterResource\Pages;

use App\Filament\Admin\Pages\SemesterSetupWizard;
use App\Filament\Admin\Resources\SemesterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSemesters extends ListRecords
{
    protected static string $resource = SemesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('setupWizard')
                ->label('Setup Wizard')
                ->icon('heroicon-o-sparkles')
                ->color('gray')
                ->url(SemesterSetupWizard::getUrl()),
        ];
    }
}
