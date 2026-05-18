<?php

namespace App\Filament\Admin\Resources\RubricTemplateResource\Pages;

use App\Filament\Admin\Resources\RubricTemplateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewRubricTemplate extends ViewRecord
{
    protected static string $resource = RubricTemplateResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->is_locked) {
            $this->notifyWhyLocked();
        }
    }

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

    public function getSubheading(): ?string
    {
        if (! $this->record->is_locked) {
            return null;
        }

        return $this->lockReasonMessage();
    }

    public function getRelationManagers(): array
    {
        return [
            RubricTemplateResource\RelationManagers\DeliverablesRelationManager::class,
        ];
    }

    protected function notifyWhyLocked(): void
    {
        Notification::make()
            ->title('This rubric template is locked')
            ->body($this->lockReasonMessage().' You can view all deliverables and criteria, but values cannot be edited. Use "Clone" to create an editable copy.')
            ->warning()
            ->persistent()
            ->send();
    }

    protected function lockReasonMessage(): string
    {
        if ($this->record->evaluations()->exists()) {
            return 'This template is locked because it is currently in use by one or more active projects.';
        }

        return 'This template has been locked and is read-only.';
    }
}
