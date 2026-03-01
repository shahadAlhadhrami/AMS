<?php

namespace App\Filament\Admin\Resources\EvaluationResource\Pages;

use App\Filament\Admin\Resources\EvaluationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEvaluation extends ViewRecord
{
    protected static string $resource = EvaluationResource::class;

    protected string $view = 'filament.admin.pages.view-evaluation';

    public function getScores(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->record->evaluationScores()
            ->with(['criterion', 'scoreLevel', 'student'])
            ->get();
    }
}
