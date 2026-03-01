<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Concerns\BuildsEvaluationForm;
use App\Models\Evaluation;
use App\Services\EvaluationService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class EvaluationForm extends Page implements HasForms
{
    use BuildsEvaluationForm;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.staff.pages.evaluation-form';

    public Evaluation $evaluation;

    public ?array $data = [];

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'evaluation/{evaluation}';
    }

    public function mount(Evaluation $evaluation): void
    {
        $user = auth()->user();

        // Authorization: evaluator must match current user
        abort_unless($evaluation->evaluator_id === $user->id, 403);

        // Fill order check
        $service = app(EvaluationService::class);

        if (! $service->isFillOrderMet($evaluation)) {
            abort(403, 'Previous assessments must be completed first.');
        }

        $this->evaluation = $evaluation->load([
            'rubricTemplate.criteria.scoreLevels',
            'project.students',
            'evaluationScores',
        ]);

        // Auto-upgrade from pending to draft on first open
        if ($this->evaluation->status === 'pending') {
            $this->evaluation->update(['status' => 'draft']);
        }

        $this->form->fill($this->loadFormData());
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema($this->buildFormSchema())
            ->statePath('data');
    }

    public function saveDraft(): void
    {
        $formData = $this->form->getState();

        $service = app(EvaluationService::class);
        $service->saveScores($this->evaluation, $formData);

        if ($this->evaluation->status !== 'draft') {
            $this->evaluation->update(['status' => 'draft']);
        }

        Notification::make()
            ->title('Draft saved successfully.')
            ->success()
            ->send();
    }

    public function submitEvaluation(): void
    {
        $formData = $this->form->getState();

        $service = app(EvaluationService::class);
        $service->saveScores($this->evaluation, $formData);

        $submitted = $service->submit($this->evaluation);

        if ($submitted) {
            Notification::make()
                ->title('Assessment submitted successfully.')
                ->success()
                ->send();

            $this->redirect(static::getUrl(['evaluation' => $this->evaluation->id]));
        } else {
            Notification::make()
                ->title('Cannot submit: all criteria must have scores.')
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Assessment: '.$this->evaluation->rubricTemplate->name;
    }

    public function getSubheading(): ?string
    {
        return 'Project: '.$this->evaluation->project->title
            .' | Role: '.$this->evaluation->evaluator_role;
    }
}
