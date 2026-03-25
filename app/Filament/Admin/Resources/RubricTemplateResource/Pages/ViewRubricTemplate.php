<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\Pages;

use App\Filament\Admin\Resources\RubricTemplateResource;
use App\Models\RubricTemplate;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRubricTemplate extends ViewRecord
{
    protected static string $resource = RubricTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->hidden(fn (): bool => $this->record->is_locked || $this->record->created_by !== auth()->id()),
            Actions\Action::make('clone')
                ->label('Clone')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->action(function () {
                    $newTemplate = RubricTemplateResource::cloneTemplate($this->record);

                    return redirect(RubricTemplateResource::getUrl('edit', ['record' => $newTemplate]));
                }),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            RubricTemplateResource\RelationManagers\DeliverablesRelationManager::class,
        ];
    }
}
