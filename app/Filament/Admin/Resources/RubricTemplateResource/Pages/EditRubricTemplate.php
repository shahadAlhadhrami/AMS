<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\Pages;

use App\Filament\Admin\Resources\RubricTemplateResource;
use App\Models\RubricTemplate;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRubricTemplate extends EditRecord
{
    protected static string $resource = RubricTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clone')
                ->label('Clone')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->action(function () {
                    $newTemplate = RubricTemplateResource::cloneTemplate($this->record);

                    return redirect(RubricTemplateResource::getUrl('edit', ['record' => $newTemplate]));
                }),
            Actions\Action::make('lock')
                ->label('Lock')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Lock Rubric Template')
                ->modalDescription('Locking this template will prevent further edits. Are you sure?')
                ->action(function () {
                    $this->record->update(['is_locked' => true]);

                    return redirect(RubricTemplateResource::getUrl('view', ['record' => $this->record]));
                })
                ->hidden(fn (): bool => $this->record->is_locked || $this->record->created_by !== auth()->id()),
            Actions\DeleteAction::make()
                ->hidden(fn (): bool => $this->record->is_locked || $this->record->created_by !== auth()->id()),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->is_locked || $this->record->created_by !== auth()->id()) {
            redirect(RubricTemplateResource::getUrl('view', ['record' => $this->record]));
        }
    }
}
