<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\Pages;

use App\Filament\Admin\Resources\RubricTemplateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
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

        if ($this->record->is_locked) {
            $reason = $this->record->evaluations()->exists()
                ? 'This template is locked because it is currently in use by one or more active projects.'
                : 'This template has been locked and is read-only.';

            Notification::make()
                ->title('Editing is disabled')
                ->body($reason.' Use "Clone" to create an editable copy.')
                ->warning()
                ->persistent()
                ->send();

            $this->redirect(RubricTemplateResource::getUrl('view', ['record' => $this->record]));

            return;
        }

        if ($this->record->created_by !== auth()->id()) {
            Notification::make()
                ->title('Editing is disabled')
                ->body('Only the creator of this rubric template can edit it. Use "Clone" to create your own editable copy.')
                ->warning()
                ->persistent()
                ->send();

            $this->redirect(RubricTemplateResource::getUrl('view', ['record' => $this->record]));
        }
    }
}
