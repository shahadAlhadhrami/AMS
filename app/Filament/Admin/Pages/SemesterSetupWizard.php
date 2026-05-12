<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Concerns\HidesDuringMasterDataSetup;
use App\Filament\Admin\Resources\ProjectResource;
use App\Filament\Admin\Resources\SemesterResource;
use App\Models\PhaseTemplate;
use App\Models\Project;
use App\Models\Semester;
use App\Services\BulkImport\ProjectsBulkImporter;
use App\Services\BulkImport\SpreadsheetReader;
use App\Support\FilamentLookupCache;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SemesterSetupWizard extends Page
{
    use HidesDuringMasterDataSetup;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|\UnitEnum|null $navigationGroup = 'Academic Setup';

    protected static ?string $navigationLabel = 'Semester Setup Wizard';

    protected static ?string $title = 'Semester Setup Wizard';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.semester-setup-wizard';

    public ?array $data = [];

    public ?int $semesterId = null;

    public bool $semesterWasCreated = false;

    public ?int $selectedPhaseTemplateId = null;

    public array $createdProjectIds = [];

    public array $projectImportUploadedFiles = [];

    public array $projectImportHeaders = [];

    public array $projectImportColumnMapping = [];

    public array $projectImportPreviewData = [];

    public array $projectImportPreviewColumns = [];

    public array $projectImportValidationErrors = [];

    public array $projectImportValidationWarnings = [];

    public bool $projectImportHasErrors = false;

    public bool $projectImportHasWarnings = false;

    public bool $projectImportShowMapping = false;

    public bool $projectImportCompleted = false;

    public int $projectImportImportedCount = 0;

    public ?string $projectImportConfirmedWarningSignature = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $existingSemesterId = Semester::query()->orderByDesc('id')->value('id');

        $this->form->fill([
            'semester_mode' => $existingSemesterId ? 'existing' : 'create',
            'semester_id' => $existingSemesterId,
            'project_entry_method' => 'manual',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Wizard::make([
                    $this->getStep1CreateSemester(),
                    $this->getStep2SelectPhaseTemplates(),
                    $this->getStep3ImportProjects(),
                    $this->getStep4ReviewSummary(),
                ])
                    ->submitAction(new HtmlString(
                        '<button type="button" wire:click="finishSetup" class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-success fi-color-success gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50" style="--c-400:var(--success-400);--c-500:var(--success-500);--c-600:var(--success-600);">
                            <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <span>Finish Setup</span>
                        </button>'
                    ))
                    ->persistStepInQueryString('step')
                    ->skippable(false),
            ])
            ->statePath('data');
    }

    protected function getStep1CreateSemester(): Wizard\Step
    {
        return Wizard\Step::make('Semester')
            ->icon('heroicon-o-academic-cap')
            ->completedIcon('heroicon-s-academic-cap')
            ->description('Select an existing semester or create one')
            ->schema([
                Forms\Components\Radio::make('semester_mode')
                    ->label('Semester')
                    ->options([
                        'existing' => 'Use an existing semester',
                        'create' => 'Create a new semester',
                    ])
                    ->descriptions([
                        'existing' => 'Choose from semesters already available in the system.',
                        'create' => 'Add a semester only when the required semester does not already exist.',
                    ])
                    ->default(fn (): string => Semester::query()->exists() ? 'existing' : 'create')
                    ->live()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('semester_id')
                    ->label('Available Semester')
                    ->options(fn (): array => $this->semesterOptions())
                    ->helperText('Select the semester this setup should work on.')
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => $get('semester_mode') === 'existing')
                    ->visible(fn (Get $get): bool => $get('semester_mode') === 'existing')
                    ->columnSpanFull(),

                Forms\Components\Placeholder::make('selected_semester_details')
                    ->label('Selected semester details')
                    ->content(fn (Get $get): HtmlString => $this->semesterDetailsHtml((int) ($get('semester_id') ?? 0)))
                    ->visible(fn (Get $get): bool => $get('semester_mode') === 'existing' && filled($get('semester_id')))
                    ->columnSpanFull(),

                Section::make('New Semester Details')
                    ->visible(fn (Get $get): bool => $get('semester_mode') === 'create')
                    ->schema([
                        Forms\Components\TextInput::make('semester_name')
                            ->label('Semester Name')
                            ->required(fn (Get $get): bool => $get('semester_mode') === 'create')
                            ->maxLength(255)
                            ->placeholder('e.g., Fall 2026'),

                        Forms\Components\TextInput::make('academic_year')
                            ->label('Academic Year')
                            ->required(fn (Get $get): bool => $get('semester_mode') === 'create')
                            ->maxLength(9)
                            ->placeholder('e.g., 2025-2026'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->nullable(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->nullable()
                            ->afterOrEqual('start_date'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->afterValidation(function () {
                $state = $this->data;

                if (($state['semester_mode'] ?? null) === 'existing') {
                    $semester = Semester::findOrFail($state['semester_id']);
                    $this->semesterId = $semester->id;
                    $this->semesterWasCreated = false;
                } else {
                    $payload = [
                        'name' => $state['semester_name'],
                        'academic_year' => $state['academic_year'],
                        'start_date' => $state['start_date'] ?? null,
                        'end_date' => $state['end_date'] ?? null,
                        'is_active' => true,
                        'is_closed' => false,
                    ];

                    if ($this->semesterWasCreated && $this->semesterId) {
                        $semester = Semester::find($this->semesterId);
                        $semester?->update($payload);
                    } else {
                        $semester = Semester::create($payload);
                        $this->semesterId = $semester->id;
                        $this->semesterWasCreated = true;
                    }
                }

                $this->data['semester_id'] = $this->semesterId;
                $this->syncCurrentCoordinatorToSemester();
                $this->syncManualProjectContextDefaults();

                Notification::make()
                    ->title($this->semesterWasCreated ? 'Semester created.' : 'Semester selected.')
                    ->success()
                    ->send();
            });
    }

    protected function getStep2SelectPhaseTemplates(): Wizard\Step
    {
        return Wizard\Step::make('Select Phase Template')
            ->icon('heroicon-o-rectangle-stack')
            ->completedIcon('heroicon-s-rectangle-stack')
            ->description('Choose the one plan for this project set')
            ->schema([
                Forms\Components\Placeholder::make('phase_template_info')
                    ->content('Choose one phase template only. This template is the plan used by the projects created in this setup.')
                    ->columnSpanFull(),

                Forms\Components\Radio::make('phase_template_id')
                    ->label('Phase Template')
                    ->options(fn (): array => $this->phaseTemplateOptions())
                    ->descriptions(fn (): array => $this->phaseTemplateDescriptions())
                    ->required()
                    ->columns(1)
                    ->columnSpanFull(),
            ])
            ->afterValidation(function () {
                $state = $this->data;
                $this->selectedPhaseTemplateId = (int) ($state['phase_template_id'] ?? 0) ?: null;
                $this->syncManualProjectContextDefaults();
            });
    }

    protected function getStep3ImportProjects(): Wizard\Step
    {
        return Wizard\Step::make('Projects')
            ->icon('heroicon-o-briefcase')
            ->completedIcon('heroicon-s-briefcase')
            ->description('Add projects to the semester')
            ->schema([
                Forms\Components\Radio::make('project_entry_method')
                    ->label('How would you like to add projects?')
                    ->options([
                        'manual' => 'Add projects manually',
                        'csv' => 'Import from CSV file',
                        'skip' => 'Skip — add projects later',
                    ])
                    ->default('manual')
                    ->descriptions([
                        'manual' => 'Create projects with the same fields used in the Projects page.',
                        'csv' => 'Use the project bulk-import workflow with column mapping, preview, and overwrite warnings.',
                        'skip' => 'Finish semester setup now and add projects from the Projects page later.',
                    ])
                    ->live()
                    ->required()
                    ->columnSpanFull(),

                Section::make('Manual Project Creation')
                    ->visible(fn (Get $get) => $get('project_entry_method') === 'manual')
                    ->description('Semester and phase template are locked to the selections from the previous steps.')
                    ->schema([
                        Forms\Components\Repeater::make('manual_projects')
                            ->label('Projects')
                            ->schema(ProjectResource::projectFormComponents(
                                semesterOptions: fn (): array => $this->selectedSemesterOption(),
                                phaseTemplateOptions: fn (): array => $this->selectedPhaseTemplateOption(),
                                semesterDefault: fn (): ?int => $this->semesterId,
                                phaseTemplateDefault: fn (): ?int => $this->selectedPhaseTemplateId,
                                lockSemester: true,
                                lockPhaseTemplate: true,
                            ))
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'New Project')
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Add Another Project')
                            ->columnSpanFull(),
                    ]),

                Section::make('Bulk Project Import')
                    ->visible(fn (Get $get) => $get('project_entry_method') === 'csv')
                    ->description(fn (): string => $this->projectImporter()->description())
                    ->schema([
                        Forms\Components\Placeholder::make('project_import_context')
                            ->label('Applied setup context')
                            ->content(fn (): HtmlString => $this->projectImportContextHtml())
                            ->columnSpanFull(),

                        Forms\Components\Select::make('project_import_course_id')
                            ->label('Course')
                            ->options(fn (): array => FilamentLookupCache::courseOptions())
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('project_entry_method') === 'csv'),

                        Forms\Components\Select::make('project_import_specialization_id')
                            ->label('Specialization')
                            ->options(fn (): array => FilamentLookupCache::specializationOptions())
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('project_entry_method') === 'csv'),

                        Forms\Components\FileUpload::make('project_import_file')
                            ->key('wizard-project-import-file')
                            ->label('CSV / Excel File')
                            ->helperText('Accepts .csv, .xlsx, or .ods. Use the template if you want merged project cells and column mapping support.')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.oasis.opendocument.spreadsheet',
                            ])
                            ->disk('local')
                            ->directory('csv-imports')
                            ->visibility('private')
                            ->columnSpanFull(),

                        SchemaActions::make([
                            Action::make('downloadProjectImportTemplate')
                                ->label('Download Project Import Template')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->color('gray')
                                ->action(fn (): StreamedResponse => $this->downloadProjectImportTemplate()),
                            Action::make('uploadAndMapProjectImport')
                                ->label('Upload & Map Columns')
                                ->icon('heroicon-o-eye')
                                ->action(function (): void {
                                    $this->uploadAndMapProjectImport();
                                }),
                        ])
                            ->columnSpanFull(),

                        Forms\Components\ViewField::make('project_import_workflow')
                            ->view('filament.admin.pages.partials.project-import-workflow')
                            ->columnSpanFull(),
                    ]),
            ])
            ->afterValidation(function () {
                $state = $this->data;
                $method = $state['project_entry_method'] ?? 'manual';

                if (! $this->semesterId || ! $this->selectedPhaseTemplateId) {
                    Notification::make()
                        ->title('Complete the semester and phase template steps before adding projects.')
                        ->danger()
                        ->send();

                    throw new Halt;
                }

                if ($method === 'manual') {
                    $this->createProjectsManually($state);
                } elseif ($method === 'csv') {
                    if (! $this->projectImportCompleted) {
                        Notification::make()
                            ->title('Import the projects before continuing to the summary.')
                            ->body('Upload the file, map the columns, preview it, then click Import Projects.')
                            ->danger()
                            ->send();

                        throw new Halt;
                    }
                }
            });
    }

    protected function getStep4ReviewSummary(): Wizard\Step
    {
        return Wizard\Step::make('Review Summary')
            ->icon('heroicon-o-clipboard-document-check')
            ->completedIcon('heroicon-s-clipboard-document-check')
            ->description('Review everything created')
            ->schema([
                Forms\Components\ViewField::make('summary')
                    ->view('filament.admin.pages.partials.wizard-summary')
                    ->columnSpanFull(),
            ]);
    }

    public function uploadAndMapProjectImport(): void
    {
        $this->projectImportUploadedFiles = [];
        $this->projectImportHeaders = [];
        $this->projectImportColumnMapping = [];
        $this->resetProjectImportPreviewState();

        $files = $this->normalizeUploadedFiles($this->data['project_import_file'] ?? null);

        if (empty($files)) {
            Notification::make()->title('Upload a CSV or Excel file first.')->danger()->send();

            return;
        }

        $this->projectImportUploadedFiles = $files;

        $filePath = $this->resolveProjectImportFilePath($files[0]);
        if (! $filePath) {
            Notification::make()->title('Spreadsheet file not found. Please re-upload.')->danger()->send();

            return;
        }

        try {
            $parsed = SpreadsheetReader::read($filePath);
        } catch (\Throwable $e) {
            Notification::make()->title('Unable to read the spreadsheet: '.$e->getMessage())->danger()->send();

            return;
        }

        if (empty($parsed['headers'])) {
            Notification::make()->title('Spreadsheet is empty or has no headers.')->danger()->send();

            return;
        }

        $this->projectImportHeaders = $parsed['headers'];
        $normalisedHeaders = array_map('strtolower', $this->projectImportHeaders);
        $this->projectImportColumnMapping = [];

        foreach ($this->projectImporter()->systemFields() as $field) {
            $index = array_search($field, $normalisedHeaders, true);
            $this->projectImportColumnMapping[$field] = $index !== false ? $this->projectImportHeaders[$index] : '';
        }

        $this->projectImportShowMapping = true;
    }

    public function confirmProjectImportMapping(): void
    {
        $unmapped = array_filter($this->projectImportColumnMapping, fn ($value) => blank($value));

        if (! empty($unmapped)) {
            Notification::make()
                ->title('Map all required columns: '.implode(', ', array_keys($unmapped)))
                ->danger()
                ->send();

            return;
        }

        $result = $this->projectImporter()->validateRows(
            $this->projectImportUploadedFiles,
            $this->projectImportColumnMapping,
            [],
        );

        $this->projectImportPreviewData = $result['previewData'] ?? [];
        $this->projectImportPreviewColumns = $result['previewColumns'] ?? [];
        $this->projectImportValidationErrors = $result['errors'] ?? [];
        $this->projectImportValidationWarnings = $result['warnings'] ?? [];
        $this->projectImportHasErrors = $result['hasErrors'] ?? false;
        $this->projectImportHasWarnings = $result['hasWarnings'] ?? ! empty($this->projectImportValidationWarnings);
        $this->projectImportConfirmedWarningSignature = null;
        $this->projectImportShowMapping = false;

        if (empty($this->projectImportPreviewData) && empty($this->projectImportValidationErrors)) {
            Notification::make()->title('Spreadsheet contains no data rows.')->warning()->send();
        }
    }

    public function importProjectImport(): void
    {
        if ($this->projectImportHasErrors || empty($this->projectImportPreviewData)) {
            Notification::make()
                ->title('Cannot import: fix validation errors first.')
                ->danger()
                ->send();

            return;
        }

        $context = $this->projectImportContext();
        $missingContext = array_keys(array_filter($context, fn ($value) => blank($value)));

        if (! empty($missingContext)) {
            Notification::make()
                ->title('Select the required import context before importing.')
                ->danger()
                ->send();

            return;
        }

        $contextResult = $this->projectImporter()->validateContext($this->projectImportPreviewData, $context);
        $this->projectImportValidationErrors = array_map(fn ($error) => "[Context] {$error}", $contextResult['errors'] ?? []);
        $this->projectImportValidationWarnings = array_map(fn ($warning) => "[Context] {$warning}", $contextResult['warnings'] ?? []);
        $this->projectImportHasErrors = ! empty($this->projectImportValidationErrors);
        $this->projectImportHasWarnings = ! empty($this->projectImportValidationWarnings);

        if ($this->projectImportHasErrors) {
            Notification::make()
                ->title('Context validation failed.')
                ->danger()
                ->send();

            return;
        }

        if ($this->projectImportHasWarnings) {
            $warningSignature = md5(json_encode([
                'context' => $context,
                'warnings' => $this->projectImportValidationWarnings,
            ]));

            if ($this->projectImportConfirmedWarningSignature !== $warningSignature) {
                $this->projectImportConfirmedWarningSignature = $warningSignature;

                Notification::make()
                    ->title('Assignment warnings found. Review them, then click import again to overwrite existing assignments.')
                    ->warning()
                    ->send();

                return;
            }
        }

        DB::beginTransaction();
        try {
            $result = $this->projectImporter()->import($this->projectImportPreviewData, $context);
            DB::commit();

            $this->projectImportCompleted = true;
            $this->projectImportImportedCount = $result['count'] ?? 0;
            $this->createdProjectIds = array_values(array_unique(array_merge(
                $this->createdProjectIds,
                $result['project_ids'] ?? [],
            )));
            $this->projectImportValidationWarnings = [];
            $this->projectImportHasWarnings = false;
            $this->projectImportConfirmedWarningSignature = null;

            Notification::make()
                ->title("Successfully imported {$this->projectImportImportedCount} projects.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();

            Notification::make()
                ->title('Import failed: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetProjectImport(): void
    {
        $this->projectImportUploadedFiles = [];
        $this->projectImportHeaders = [];
        $this->projectImportColumnMapping = [];
        $this->resetProjectImportPreviewState();
        $this->data['project_import_file'] = null;
    }

    protected function resetProjectImportPreviewState(): void
    {
        $this->projectImportPreviewData = [];
        $this->projectImportPreviewColumns = [];
        $this->projectImportValidationErrors = [];
        $this->projectImportValidationWarnings = [];
        $this->projectImportHasErrors = false;
        $this->projectImportHasWarnings = false;
        $this->projectImportShowMapping = false;
        $this->projectImportCompleted = false;
        $this->projectImportImportedCount = 0;
        $this->projectImportConfirmedWarningSignature = null;
    }

    protected function createProjectsManually(array $state): void
    {
        $projects = $state['manual_projects'] ?? [];

        if (empty($projects) || ! empty($this->createdProjectIds)) {
            return;
        }

        DB::beginTransaction();
        try {
            $reviewerIds = PhaseTemplate::with('reviewers:id')
                ->find($this->selectedPhaseTemplateId)
                ?->reviewers
                ?->pluck('id')
                ->all() ?? [];

            foreach ($projects as $projectData) {
                $project = Project::create([
                    'title' => $projectData['title'],
                    'semester_id' => $projectData['semester_id'] ?? $this->semesterId,
                    'course_id' => $projectData['course_id'],
                    'phase_template_id' => $projectData['phase_template_id'] ?? $this->selectedPhaseTemplateId,
                    'specialization_id' => $projectData['specialization_id'],
                    'supervisor_id' => $projectData['supervisor_id'],
                    'previous_phase_project_id' => $projectData['previous_phase_project_id'] ?? null,
                    'status' => $projectData['status'] ?? 'setup',
                ]);

                if (! empty($reviewerIds)) {
                    $project->reviewers()->syncWithoutDetaching($reviewerIds);
                }

                $this->createdProjectIds[] = $project->id;
            }

            DB::commit();

            Notification::make()
                ->title(count($projects).' project(s) created successfully.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();

            Notification::make()
                ->title('Failed to create projects: '.$e->getMessage())
                ->danger()
                ->send();

            throw new Halt;
        }
    }

    protected function projectImporter(): ProjectsBulkImporter
    {
        return app(ProjectsBulkImporter::class);
    }

    public function getProjectImportFieldLabels(): array
    {
        return $this->projectImporter()->systemFieldLabels();
    }

    protected function semesterOptions(): array
    {
        return Semester::query()
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(fn (Semester $semester): array => [
                $semester->id => $this->formatSemesterOption($semester),
            ])
            ->all();
    }

    protected function selectedSemesterOption(): array
    {
        if (! $this->semesterId) {
            return [];
        }

        $semester = Semester::find($this->semesterId);

        return $semester ? [$semester->id => $this->formatSemesterOption($semester)] : [];
    }

    protected function selectedPhaseTemplateOption(): array
    {
        if (! $this->selectedPhaseTemplateId) {
            return [];
        }

        $phaseTemplate = PhaseTemplate::find($this->selectedPhaseTemplateId);

        return $phaseTemplate ? [$phaseTemplate->id => $phaseTemplate->name] : [];
    }

    protected function phaseTemplateOptions(): array
    {
        return PhaseTemplate::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function phaseTemplateDescriptions(): array
    {
        return PhaseTemplate::query()
            ->withCount(['phaseRubricRules', 'reviewers', 'externals'])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (PhaseTemplate $phaseTemplate): array => [
                $phaseTemplate->id => number_format((float) $phaseTemplate->total_phase_marks, 2).' marks'
                    .' | '.$phaseTemplate->phase_rubric_rules_count.' rubric rules'
                    .' | '.$phaseTemplate->reviewers_count.' reviewers'
                    .' | '.$phaseTemplate->externals_count.' externals',
            ])
            ->all();
    }

    protected function semesterDetailsHtml(int $semesterId): HtmlString
    {
        $semester = $semesterId ? Semester::withCount('projects')->find($semesterId) : null;

        if (! $semester) {
            return new HtmlString('<span class="text-sm text-gray-500 dark:text-gray-400">No semester selected.</span>');
        }

        $dates = trim(($semester->start_date?->format('Y-m-d') ?? 'No start date').' to '.($semester->end_date?->format('Y-m-d') ?? 'No end date'));
        $status = ($semester->is_active ? 'Active' : 'Inactive').' / '.($semester->is_closed ? 'Closed' : 'Open');

        return new HtmlString(
            '<div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">'
            .'<p><strong>'.e($semester->name).'</strong> | '.e($semester->academic_year).'</p>'
            .'<p>'.e($dates).'</p>'
            .'<p>'.e($status).' | '.e((string) $semester->projects_count).' existing projects</p>'
            .'</div>'
        );
    }

    protected function projectImportContextHtml(): HtmlString
    {
        $semester = $this->semesterId ? Semester::find($this->semesterId) : null;
        $phaseTemplate = $this->selectedPhaseTemplateId ? PhaseTemplate::find($this->selectedPhaseTemplateId) : null;

        return new HtmlString(
            '<div class="grid gap-3 text-sm text-gray-700 dark:text-gray-300 md:grid-cols-2">'
            .'<div><span class="text-gray-500 dark:text-gray-400">Semester</span><p class="font-medium">'.e($semester?->name ?? 'Select a semester first').'</p></div>'
            .'<div><span class="text-gray-500 dark:text-gray-400">Phase Template</span><p class="font-medium">'.e($phaseTemplate?->name ?? 'Select a phase template first').'</p></div>'
            .'</div>'
        );
    }

    protected function formatSemesterOption(Semester $semester): string
    {
        $status = $semester->is_closed ? 'Closed' : 'Open';

        return "{$semester->name} ({$semester->academic_year}) - {$status}";
    }

    protected function syncCurrentCoordinatorToSemester(): void
    {
        $user = auth()->user();

        if (! $user?->hasRole('Coordinator') || ! $this->semesterId) {
            return;
        }

        Semester::find($this->semesterId)?->coordinators()->syncWithoutDetaching([$user->id]);
    }

    protected function syncManualProjectContextDefaults(): void
    {
        if (empty($this->data['manual_projects']) || ! is_array($this->data['manual_projects'])) {
            return;
        }

        $this->data['manual_projects'] = array_map(function (array $project): array {
            if ($this->semesterId) {
                $project['semester_id'] = $this->semesterId;
            }

            if ($this->selectedPhaseTemplateId) {
                $project['phase_template_id'] = $this->selectedPhaseTemplateId;
            }

            $project['status'] ??= 'setup';

            return $project;
        }, $this->data['manual_projects']);
    }

    protected function projectImportContext(): array
    {
        return [
            'semester_id' => $this->semesterId,
            'course_id' => $this->data['project_import_course_id'] ?? null,
            'phase_template_id' => $this->selectedPhaseTemplateId,
            'specialization_id' => $this->data['project_import_specialization_id'] ?? null,
        ];
    }

    protected function normalizeUploadedFiles(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($path) => filled($path)));
        }

        return filled($value) ? [$value] : [];
    }

    protected function resolveProjectImportFilePath(string $path): ?string
    {
        $filePath = storage_path('app/private/'.$path);

        if (! file_exists($filePath)) {
            $filePath = storage_path('app/'.$path);
        }

        return file_exists($filePath) ? $filePath : null;
    }

    // ──────────────────────────────────────────────────
    // Summary Helpers
    // ──────────────────────────────────────────────────

    public function getSemesterSummary(): ?array
    {
        if (! $this->semesterId) {
            return null;
        }

        $semester = Semester::find($this->semesterId);

        return $semester ? [
            'name' => $semester->name,
            'academic_year' => $semester->academic_year,
            'start_date' => $semester->start_date?->format('Y-m-d'),
            'end_date' => $semester->end_date?->format('Y-m-d'),
            'source' => $this->semesterWasCreated ? 'Created in this setup' : 'Existing semester selected',
            'project_count' => $semester->projects()->count(),
        ] : null;
    }

    public function getSelectedPhaseTemplateSummary(): ?array
    {
        if (! $this->selectedPhaseTemplateId) {
            return null;
        }

        $phaseTemplate = PhaseTemplate::query()
            ->withCount(['phaseRubricRules', 'reviewers', 'externals'])
            ->find($this->selectedPhaseTemplateId);

        return $phaseTemplate ? [
            'name' => $phaseTemplate->name,
            'total_phase_marks' => number_format((float) $phaseTemplate->total_phase_marks, 2),
            'rubric_rules_count' => $phaseTemplate->phase_rubric_rules_count,
            'reviewers_count' => $phaseTemplate->reviewers_count,
            'externals_count' => $phaseTemplate->externals_count,
        ] : null;
    }

    public function getProjectsSummary(): array
    {
        if (empty($this->createdProjectIds)) {
            return [];
        }

        return Project::whereIn('id', $this->createdProjectIds)
            ->with(['course', 'phaseTemplate', 'supervisor', 'students', 'reviewers'])
            ->get()
            ->map(fn (Project $p) => [
                'title' => $p->title,
                'course' => $p->course ? $p->course->code.' - '.$p->course->title : '-',
                'phase_template' => $p->phaseTemplate?->name ?? '-',
                'supervisor' => $p->supervisor?->name ?? '-',
                'status' => ucfirst($p->status),
                'students' => $p->students->pluck('name')->join(', '),
                'reviewers' => $p->reviewers->pluck('name')->join(', '),
            ])
            ->toArray();
    }

    // ──────────────────────────────────────────────────
    // Final Actions
    // ──────────────────────────────────────────────────

    public function finishSetup(): void
    {
        Notification::make()
            ->title('Semester setup complete!')
            ->body('The selected semester, phase template, and project setup choices have been saved.')
            ->success()
            ->send();

        if (! $this->semesterId) {
            $this->redirect(SemesterResource::getUrl());

            return;
        }

        $this->redirect(
            SemesterResource::getUrl('edit', [
                'record' => $this->semesterId,
            ])
        );
    }

    public function downloadProjectImportTemplate(): StreamedResponse
    {
        return $this->projectImporter()->downloadTemplate();
    }

    public function downloadProjectCsvTemplate(): StreamedResponse
    {
        return $this->downloadProjectImportTemplate();
    }
}
