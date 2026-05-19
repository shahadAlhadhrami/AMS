<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\EvaluationResource;
use App\Filament\Concerns\BuildsEvaluationForm;
use App\Models\Evaluation;
use App\Services\EvaluationService;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class ProxyEvaluationForm extends Page implements HasForms
{
    use BuildsEvaluationForm;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.admin.pages.proxy-evaluation-form';

    public Evaluation $evaluation;

    public ?array $data = [];

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'proxy-evaluation/{evaluation}';
    }

    public function mount(Evaluation $evaluation): void
    {
        $user = auth()->user();

        // Authorization: must be Coordinator or Super Admin
        abort_unless($user->hasAnyRole(['Super Admin', 'Coordinator']), 403);

        $this->evaluation = $evaluation->load([
            'rubricTemplate.criteria.scoreLevels',
            'project.students',
            'evaluationScores',
            'evaluator',
        ]);

        // Auto-upgrade from pending to draft on proxy open
        if ($this->evaluation->status === 'pending') {
            $this->evaluation->update([
                'status' => 'draft',
                'on_behalf_of_user_id' => $user->id,
            ]);
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

        $this->evaluation->update([
            'status' => 'draft',
            'on_behalf_of_user_id' => auth()->id(),
        ]);

        Notification::make()
            ->title('Proxy draft saved successfully.')
            ->success()
            ->send();
    }

    public function submitEvaluation(): void
    {
        $formData = $this->form->getState();

        $service = app(EvaluationService::class);
        $service->saveScores($this->evaluation, $formData);

        $this->evaluation->update(['on_behalf_of_user_id' => auth()->id()]);

        $submitted = $service->submit($this->evaluation);

        if ($submitted) {
            Notification::make()
                ->title('Proxy assessment submitted successfully.')
                ->success()
                ->send();

            $this->redirect(EvaluationResource::getUrl('index'));
        } else {
            Notification::make()
                ->title('Cannot submit: all criteria must have scores.')
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Proxy Assessment: '.$this->evaluation->rubricTemplate->name;
    }

    public function submitEvaluationAction(): Action
    {
        return Action::make('submitEvaluation')
            ->label('Submit Assessment')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->requiresConfirmation()
            ->modalHeading('Submit Assessment')
            ->modalDescription('You are submitting this assessment on behalf of the evaluator. Once submitted, it will be locked. Are you sure?')
            ->modalSubmitActionLabel('Submit')
            ->action(function () {
                try {
                    $this->submitEvaluation();
                } catch (\Illuminate\Validation\ValidationException $e) {
                    Notification::make()
                        ->title('Some scores are invalid')
                        ->body('Please review the highlighted fields and correct any errors before submitting.')
                        ->danger()
                        ->send();
                    throw $e;
                }
            });
    }

    public function getSubheading(): ?string
    {
        return 'On behalf of: '.$this->evaluation->evaluator->name
            .' | Project: '.$this->evaluation->project->title;
    }
}
