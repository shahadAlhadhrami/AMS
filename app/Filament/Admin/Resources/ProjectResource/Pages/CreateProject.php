<?php

namespace App\Filament\Admin\Resources\ProjectResource\Pages;

use App\Filament\Admin\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (
            empty($data['coordinator_id'])
            && $user
            && $user->hasRole('Coordinator')
            && ! $user->hasRole('Super Admin')
        ) {
            $data['coordinator_id'] = $user->id;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->autoTransitionToEvaluating();
    }
}
