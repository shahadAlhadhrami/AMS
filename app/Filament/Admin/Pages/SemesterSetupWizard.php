<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\SemesterResource;
use App\Models\Course;
use App\Models\PhaseTemplate;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialization;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SemesterSetupWizard extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-sparkles';

    protected static string | \UnitEnum | null $navigationGroup = 'Academic Setup';

    protected static ?string $navigationLabel = 'Semester Setup Wizard';

    protected static ?string $title = 'Semester Setup Wizard';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.semester-setup-wizard';

    public ?array $data = [];

    // Cross-step state
    public ?int $createdSemesterId = null;

    public array $selectedPhaseTemplateIds = [];

    public array $createdProjectIds = [];

    // CSV import state (Step 3)
    public array $csvPreviewData = [];

    public array $csvValidationErrors = [];

    public bool $csvHasErrors = false;

    public bool $csvValidated = false;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['Super Admin', 'Coordinator']);
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->form->fill();
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
        return Wizard\Step::make('Create Semester')
            ->icon('heroicon-o-academic-cap')
            ->completedIcon('heroicon-s-academic-cap')
            ->description('Define the new semester')
            ->schema([
                Forms\Components\TextInput::make('semester_name')
                    ->label('Semester Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Fall 2026'),

                Forms\Components\TextInput::make('academic_year')
                    ->label('Academic Year')
                    ->required()
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
            ->afterValidation(function () {
                $state = $this->data;

                if ($this->createdSemesterId) {
                    $semester = Semester::find($this->createdSemesterId);
                    if ($semester) {
                        $semester->update([
                            'name' => $state['semester_name'],
                            'academic_year' => $state['academic_year'],
                            'start_date' => $state['start_date'] ?? null,
                            'end_date' => $state['end_date'] ?? null,
                        ]);
                    }
                } else {
                    $semester = Semester::create([
                        'name' => $state['semester_name'],
                        'academic_year' => $state['academic_year'],
                        'start_date' => $state['start_date'] ?? null,
                        'end_date' => $state['end_date'] ?? null,
                        'is_active' => true,
                        'is_closed' => false,
                    ]);

                    $this->createdSemesterId = $semester->id;

                    // Auto-assign current coordinator
                    $user = auth()->user();
                    if ($user->hasRole('Coordinator')) {
                        $semester->coordinators()->syncWithoutDetaching([$user->id]);
                    }
                }

                Log::info('Reached afterValidation!'); Notification::make()
                    ->title('Semester saved successfully.')
                    ->success()
                    ->send();
            });
    }

    protected function getStep2SelectPhaseTemplates(): Wizard\Step
    {
        return Wizard\Step::make('Select Phase Templates')
            ->icon('heroicon-o-rectangle-stack')
            ->completedIcon('heroicon-s-rectangle-stack')
            ->description('Choose templates for projects')
            ->schema([
                Forms\Components\Placeholder::make('phase_template_info')
                    ->content('Select one or more phase templates from the pool. These will be available as options when creating projects in the next step.')
                    ->columnSpanFull(),

                Forms\Components\CheckboxList::make('phase_template_ids')
                    ->label('Phase Templates')
                    ->options(function () {
                        return PhaseTemplate::query()
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (PhaseTemplate $pt) => [
                                $pt->id => "{$pt->name} ({$pt->total_phase_marks} marks)",
                            ]);
                    })
                    ->required()
                    ->columns(2)
                    ->searchable()
                    ->bulkToggleable(),
            ])
            ->afterValidation(function () {
                $state = $this->data;
                $this->selectedPhaseTemplateIds = $state['phase_template_ids'] ?? [];
            });
    }

    protected function getStep3ImportProjects(): Wizard\Step
    {
        return Wizard\Step::make('Import/Create Projects')
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
                    ->live()
                    ->required()
                    ->columnSpanFull(),

                // === MANUAL ENTRY SECTION ===
                Section::make('Manual Project Entry')
                    ->visible(fn (Get $get) => $get('project_entry_method') === 'manual')
                    ->schema([
                        Forms\Components\Repeater::make('manual_projects')
                            ->label('Projects')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\Select::make('course_id')
                                    ->label('Course')
                                    ->options(function () {
                                        return Course::query()
                                            ->orderBy('code')
                                            ->get()
                                            ->mapWithKeys(fn ($c) => [
                                                $c->id => "{$c->code} - {$c->title}",
                                            ]);
                                    })
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('phase_template_id')
                                    ->label('Phase Template')
                                    ->options(function () {
                                        if (empty($this->selectedPhaseTemplateIds)) {
                                            return PhaseTemplate::query()
                                                ->orderBy('name')
                                                ->get()
                                                ->mapWithKeys(fn ($pt) => [$pt->id => $pt->name]);
                                        }

                                        return PhaseTemplate::query()
                                            ->whereIn('id', $this->selectedPhaseTemplateIds)
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn ($pt) => [$pt->id => $pt->name]);
                                    })
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('specialization_id')
                                    ->label('Specialization')
                                    ->options(function () {
                                        return Specialization::query()
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn ($s) => [$s->id => $s->name]);
                                    })
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('supervisor_id')
                                    ->label('Supervisor')
                                    ->options(function () {
                                        try {
                                            return User::role('Supervisor')
                                                ->orderBy('name')
                                                ->get()
                                                ->mapWithKeys(fn ($u) => [
                                                    $u->id => "{$u->name} ({$u->university_id})",
                                                ]);
                                        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                                            return [];
                                        }
                                    })
                                    ->searchable()
                                    ->required(),

                                Forms\Components\Select::make('student_ids')
                                    ->label('Students')
                                    ->multiple()
                                    ->options(function () {
                                        try {
                                            return User::role('Student')
                                                ->orderBy('name')
                                                ->get()
                                                ->mapWithKeys(fn ($u) => [
                                                    $u->id => "{$u->name} ({$u->university_id})",
                                                ]);
                                        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                                            return [];
                                        }
                                    })
                                    ->searchable()
                                    ->maxItems(4),

                                Forms\Components\Select::make('reviewer_ids')
                                    ->label('Reviewers')
                                    ->multiple()
                                    ->options(function () {
                                        try {
                                            return User::role('Reviewer')
                                                ->orderBy('name')
                                                ->get()
                                                ->mapWithKeys(fn ($u) => [
                                                    $u->id => "{$u->name} ({$u->university_id})",
                                                ]);
                                        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                                            return [];
                                        }
                                    })
                                    ->searchable(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'New Project')
                            ->defaultItems(1)
                            ->addActionLabel('Add Another Project')
                            ->columnSpanFull(),
                    ]),

                // === CSV IMPORT SECTION ===
                Section::make('CSV Import')
                    ->visible(fn (Get $get) => $get('project_entry_method') === 'csv')
                    ->schema([
                        Forms\Components\Placeholder::make('csv_instructions')
                            ->content(new HtmlString(
                                '<strong>CSV Columns:</strong> <code>title, course_code, phase_template_name, '
                                . 'specialization_name, supervisor_university_id, '
                                . 'student_university_ids, reviewer_university_ids</code>'
                                . '<br><br>Separate multiple student/reviewer IDs with <code>|</code> (pipe). '
                                . 'The semester is automatically set from Step 1.'
                            ))
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('projects_csv')
                            ->label('CSV File')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/vnd.ms-excel',
                            ])
                            ->disk('local')
                            ->directory('csv-imports')
                            ->visibility('private')
                            ->columnSpanFull(),
                    ]),
            ])
            ->afterValidation(function () {
                $state = $this->data;
                $method = $state['project_entry_method'] ?? 'manual';

                if ($method === 'manual') {
                    $this->createProjectsManually($state);
                } elseif ($method === 'csv') {
                    $this->createProjectsFromCsv();
                }
                // 'skip' does nothing
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

    // ──────────────────────────────────────────────────
    // CSV Preview & Validation
    // ──────────────────────────────────────────────────

    public function previewCsv(): void
    {
        $state = $this->data;
        $csvPath = $state['projects_csv'] ?? null;

        $this->csvPreviewData = [];
        $this->csvValidationErrors = [];
        $this->csvHasErrors = false;
        $this->csvValidated = false;

        if (! $csvPath) {
            Log::info('Reached afterValidation!'); Notification::make()->title('No CSV file uploaded.')->danger()->send();

            return;
        }

        $filePath = storage_path('app/private/' . $csvPath);
        if (! file_exists($filePath)) {
            $filePath = storage_path('app/' . $csvPath);
        }

        if (! file_exists($filePath)) {
            Log::info('Reached afterValidation!'); Notification::make()->title('CSV file not found.')->danger()->send();

            return;
        }

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            Log::info('Reached afterValidation!'); Notification::make()->title('Unable to read the CSV file.')->danger()->send();

            return;
        }

        $headers = fgetcsv($handle, length: 0, escape: '');
        if (! $headers) {
            fclose($handle);
            Log::info('Reached afterValidation!'); Notification::make()->title('CSV file is empty or has no headers.')->danger()->send();

            return;
        }

        $headers = array_map('trim', array_map('strtolower', $headers));
        $requiredHeaders = [
            'title', 'course_code', 'phase_template_name',
            'specialization_name', 'supervisor_university_id',
            'student_university_ids', 'reviewer_university_ids',
        ];
        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (! empty($missingHeaders)) {
            fclose($handle);
            Log::info('Reached afterValidation!'); Notification::make()
                ->title('Missing required columns: ' . implode(', ', $missingHeaders))
                ->danger()
                ->send();

            return;
        }

        $rows = [];
        $rowNumber = 1;
        while (($row = fgetcsv($handle, length: 0, escape: '')) !== false) {
            $rowNumber++;
            $rowData = array_combine($headers, array_pad($row, count($headers), ''));
            $rowData['_row'] = $rowNumber;
            $rows[] = $rowData;
        }
        fclose($handle);

        if (empty($rows)) {
            Log::info('Reached afterValidation!'); Notification::make()->title('CSV contains no data rows.')->warning()->send();

            return;
        }

        $this->validateCsvRows($rows);
        $this->csvValidated = true;
    }

    protected function validateCsvRows(array $rows): void
    {
        $studentSemesterTracker = [];
        $semesterId = $this->createdSemesterId;

        foreach ($rows as $row) {
            $rowNum = $row['_row'];
            $rowErrors = [];

            $title = trim($row['title'] ?? '');
            if (empty($title)) {
                $rowErrors[] = 'title is required';
            }

            $courseCode = trim($row['course_code'] ?? '');
            $course = $courseCode ? Course::where('code', $courseCode)->first() : null;
            if (! $course) {
                $rowErrors[] = "Course '{$courseCode}' not found";
            }

            $ptName = trim($row['phase_template_name'] ?? '');
            $phaseTemplate = $ptName ? PhaseTemplate::where('name', $ptName)->first() : null;
            if (! $phaseTemplate) {
                $rowErrors[] = "Phase template '{$ptName}' not found";
            } elseif (! empty($this->selectedPhaseTemplateIds) && ! in_array($phaseTemplate->id, $this->selectedPhaseTemplateIds)) {
                $rowErrors[] = "Phase template '{$ptName}' was not selected in Step 2";
            }

            $specName = trim($row['specialization_name'] ?? '');
            $spec = $specName ? Specialization::where('name', $specName)->first() : null;
            if (! $spec) {
                $rowErrors[] = "Specialization '{$specName}' not found";
            }

            $supervisorUid = trim($row['supervisor_university_id'] ?? '');
            $supervisor = $supervisorUid ? User::where('university_id', $supervisorUid)->first() : null;
            if (! $supervisor) {
                $rowErrors[] = "Supervisor '{$supervisorUid}' not found";
            } elseif (! $supervisor->hasRole('Supervisor')) {
                $rowErrors[] = "User '{$supervisorUid}' does not have the Supervisor role";
            }

            $studentUids = array_filter(array_map('trim', explode('|', $row['student_university_ids'] ?? '')));
            $studentIds = [];
            foreach ($studentUids as $uid) {
                if (empty($uid)) {
                    continue;
                }
                $student = User::where('university_id', $uid)->first();
                if (! $student) {
                    $rowErrors[] = "Student '{$uid}' not found";
                } elseif (! $student->hasRole('Student')) {
                    $rowErrors[] = "User '{$uid}' does not have the Student role";
                } else {
                    $studentIds[] = $student->id;

                    if ($semesterId) {
                        $existsInDb = Project::where('semester_id', $semesterId)
                            ->whereHas('students', fn ($q) => $q->where('users.id', $student->id))
                            ->exists();
                        if ($existsInDb) {
                            $rowErrors[] = "Student '{$uid}' is already in another project this semester";
                        }

                        $trackKey = $semesterId . '-' . $student->id;
                        if (isset($studentSemesterTracker[$trackKey])) {
                            $rowErrors[] = "Student '{$uid}' appears in multiple projects in this CSV";
                        }
                        $studentSemesterTracker[$trackKey] = $rowNum;
                    }
                }
            }

            if (count($studentIds) > 4) {
                $rowErrors[] = 'Maximum 4 students per project (found ' . count($studentIds) . ')';
            }

            $reviewerUids = array_filter(array_map('trim', explode('|', $row['reviewer_university_ids'] ?? '')));
            $reviewerIds = [];
            foreach ($reviewerUids as $uid) {
                if (empty($uid)) {
                    continue;
                }
                $reviewer = User::where('university_id', $uid)->first();
                if (! $reviewer) {
                    $rowErrors[] = "Reviewer '{$uid}' not found";
                } elseif (! $reviewer->hasRole('Reviewer')) {
                    $rowErrors[] = "User '{$uid}' does not have the Reviewer role";
                } else {
                    $reviewerIds[] = $reviewer->id;

                    if ($supervisor && $reviewer->id === $supervisor->id) {
                        $rowErrors[] = "Supervisor '{$supervisorUid}' cannot also be a reviewer";
                    }
                }
            }

            $status = empty($rowErrors) ? 'valid' : 'error';

            $this->csvPreviewData[] = [
                'row' => $rowNum,
                'title' => $title,
                'course_code' => $courseCode,
                'phase_template_name' => $ptName,
                'supervisor' => $supervisorUid,
                'student_count' => count($studentIds),
                'reviewer_count' => count($reviewerIds),
                'status' => $status,
                'errors' => $rowErrors,
                'resolved' => [
                    'course_id' => $course?->id,
                    'phase_template_id' => $phaseTemplate?->id,
                    'specialization_id' => $spec?->id,
                    'supervisor_id' => $supervisor?->id,
                    'student_ids' => $studentIds,
                    'reviewer_ids' => $reviewerIds,
                ],
            ];

            if (! empty($rowErrors)) {
                $this->csvHasErrors = true;
                foreach ($rowErrors as $error) {
                    $this->csvValidationErrors[] = "Row {$rowNum}: {$error}";
                }
            }
        }
    }

    // ──────────────────────────────────────────────────
    // Project Creation
    // ──────────────────────────────────────────────────

    protected function createProjectsManually(array $state): void
    {
        $projects = $state['manual_projects'] ?? [];

        if (empty($projects)) {
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($projects as $projectData) {
                $project = Project::create([
                    'title' => $projectData['title'],
                    'semester_id' => $this->createdSemesterId,
                    'course_id' => $projectData['course_id'],
                    'phase_template_id' => $projectData['phase_template_id'],
                    'specialization_id' => $projectData['specialization_id'],
                    'supervisor_id' => $projectData['supervisor_id'],
                    'status' => 'setup',
                ]);

                if (! empty($projectData['student_ids'])) {
                    $project->students()->attach($projectData['student_ids']);
                }
                if (! empty($projectData['reviewer_ids'])) {
                    $project->reviewers()->attach($projectData['reviewer_ids']);
                }

                $this->createdProjectIds[] = $project->id;
            }

            DB::commit();

            Log::info('Reached afterValidation!'); Notification::make()
                ->title(count($projects) . ' project(s) created successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::info('Reached afterValidation!'); Notification::make()
                ->title('Failed to create projects: ' . $e->getMessage())
                ->danger()
                ->send();

            throw new \Filament\Support\Exceptions\Halt;
        }
    }

    protected function createProjectsFromCsv(): void
    {
        if ($this->csvHasErrors || empty($this->csvPreviewData)) {
            Log::info('Reached afterValidation!'); Notification::make()
                ->title('Cannot import: fix validation errors or preview CSV first.')
                ->danger()
                ->send();

            throw new \Filament\Support\Exceptions\Halt;
        }

        DB::beginTransaction();
        try {
            foreach ($this->csvPreviewData as $row) {
                $resolved = $row['resolved'];

                $project = Project::create([
                    'title' => $row['title'],
                    'semester_id' => $this->createdSemesterId,
                    'course_id' => $resolved['course_id'],
                    'phase_template_id' => $resolved['phase_template_id'],
                    'specialization_id' => $resolved['specialization_id'],
                    'supervisor_id' => $resolved['supervisor_id'],
                    'status' => 'setup',
                ]);

                if (! empty($resolved['student_ids'])) {
                    $project->students()->attach($resolved['student_ids']);
                }
                if (! empty($resolved['reviewer_ids'])) {
                    $project->reviewers()->attach($resolved['reviewer_ids']);
                }

                $this->createdProjectIds[] = $project->id;
            }

            DB::commit();

            Log::info('Reached afterValidation!'); Notification::make()
                ->title(count($this->csvPreviewData) . ' project(s) imported successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::info('Reached afterValidation!'); Notification::make()
                ->title('Import failed: ' . $e->getMessage())
                ->danger()
                ->send();

            throw new \Filament\Support\Exceptions\Halt;
        }
    }

    // ──────────────────────────────────────────────────
    // Summary Helpers
    // ──────────────────────────────────────────────────

    public function getSemesterSummary(): ?array
    {
        if (! $this->createdSemesterId) {
            return null;
        }

        $semester = Semester::find($this->createdSemesterId);

        return $semester ? [
            'name' => $semester->name,
            'academic_year' => $semester->academic_year,
            'start_date' => $semester->start_date?->format('Y-m-d'),
            'end_date' => $semester->end_date?->format('Y-m-d'),
        ] : null;
    }

    public function getSelectedPhaseTemplateNames(): array
    {
        return PhaseTemplate::whereIn('id', $this->selectedPhaseTemplateIds)
            ->pluck('name')
            ->toArray();
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
                'course' => $p->course->code . ' - ' . $p->course->title,
                'phase_template' => $p->phaseTemplate->name,
                'supervisor' => $p->supervisor->name,
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
        Log::info('Reached afterValidation!'); Notification::make()
            ->title('Semester setup complete!')
            ->body('The semester and all projects have been created successfully.')
            ->success()
            ->send();

        $this->redirect(
            SemesterResource::getUrl('edit', [
                'record' => $this->createdSemesterId,
            ])
        );
    }

    public function downloadProjectCsvTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'title', 'course_code', 'phase_template_name',
                'specialization_name', 'supervisor_university_id',
                'student_university_ids', 'reviewer_university_ids',
            ]);
            fputcsv($file, [
                'Smart Campus App', 'CS101', 'Phase 1 Template',
                'Software Engineering', 'SUP001',
                'STU001|STU002|STU003', 'REV001|REV002',
            ]);
            fclose($file);
        }, 'wizard_projects_template.csv');
    }
}
