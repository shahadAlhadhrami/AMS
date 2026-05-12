<?php

namespace App\Filament\Admin\Pages;

use App\Models\Course;
use App\Models\Department;
use App\Models\Specialization;
use App\Support\MasterDataSetup;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MasterDataSetupWizard extends Page
{
    public const STEP_DEPARTMENT = 1;

    public const STEP_SPECIALIZATION = 2;

    public const STEP_COURSE = 3;

    public const STEP_GRADING_SCALE = 4;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Master Data Setup';

    protected static ?string $title = 'Master Data Setup';

    protected static ?int $navigationSort = -10;

    protected string $view = 'filament.admin.pages.master-data-setup-wizard';

    public ?array $data = [];

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'master-data-setup';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return MasterDataSetup::shouldFocusNavigation();
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $progress = $this->savedProgress();
        $progressData = is_array($progress['data'] ?? null) ? $progress['data'] : [];

        $this->form->fill(array_replace([
            'department_id' => Department::query()->orderBy('name')->value('id'),
            'grading_scales' => MasterDataSetup::gradingScaleRows(),
        ], $progressData));
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Wizard::make([
                    $this->departmentStep(),
                    $this->specializationStep(),
                    $this->courseStep(),
                    $this->gradingScaleStep(),
                ])
                    ->startOnStep(fn (): int => $this->resumeStep())
                    ->submitAction(new HtmlString(
                        '<button type="button" wire:click="finishSetup" class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50" style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);">
                            <span>Finish setup</span>
                        </button>'
                    ))
                    ->persistStepInQueryString('step')
                    ->skippable(false),
            ])
            ->statePath('data');
    }

    public function updatedData(mixed $value = null, ?string $key = null): void
    {
        $this->saveProgress();
    }

    private function departmentStep(): Wizard\Step
    {
        return Wizard\Step::make('Department')
            ->icon('heroicon-o-building-office')
            ->schema([
                Forms\Components\Placeholder::make('department_status')
                    ->label('Current departments')
                    ->content(fn (): HtmlString => $this->currentDepartmentsHtml())
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('department_name')
                    ->label('Department name')
                    ->helperText(fn (): string => Department::query()->exists()
                        ? 'Leave blank if the current department is enough for now.'
                        : 'Add at least one department to continue.')
                    ->required(fn (): bool => ! Department::query()->exists())
                    ->maxLength(255),
                SchemaActions::make([
                    Action::make('addDepartment')
                        ->label('Add department')
                        ->icon('heroicon-o-plus')
                        ->action(fn () => $this->addDepartment()),
                ])
                    ->columnSpanFull(),
            ])
            ->afterValidation(function (): void {
                $this->saveDepartmentFromState();
                $this->saveProgress(self::STEP_SPECIALIZATION);
            });
    }

    private function specializationStep(): Wizard\Step
    {
        return Wizard\Step::make('Specialization')
            ->icon('heroicon-o-academic-cap')
            ->schema([
                Forms\Components\Placeholder::make('specialization_status')
                    ->label('Current specializations')
                    ->content(fn (): HtmlString => $this->currentSpecializationsHtml())
                    ->columnSpanFull(),
                Forms\Components\Select::make('department_id')
                    ->label('Department')
                    ->options(fn (): array => Department::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->default(fn (): ?int => Department::query()->orderBy('name')->value('id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('specialization_name')
                    ->label('Specialization name')
                    ->helperText(fn (): string => Specialization::query()->exists()
                        ? 'Leave blank if the current specialization is enough for now.'
                        : 'Add at least one specialization to continue.')
                    ->required(fn (): bool => ! Specialization::query()->exists())
                    ->maxLength(255),
                SchemaActions::make([
                    Action::make('addSpecialization')
                        ->label('Add specialization')
                        ->icon('heroicon-o-plus')
                        ->action(fn () => $this->addSpecialization()),
                ])
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->afterValidation(function (): void {
                $this->saveSpecializationFromState();
                $this->saveProgress(self::STEP_COURSE);
            });
    }

    private function courseStep(): Wizard\Step
    {
        return Wizard\Step::make('Course')
            ->icon('heroicon-o-book-open')
            ->schema([
                Forms\Components\Placeholder::make('course_status')
                    ->label('Current courses')
                    ->content(fn (): HtmlString => $this->currentCoursesHtml())
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('course_code')
                    ->label('Course code')
                    ->helperText(fn (): string => Course::query()->exists()
                        ? 'Leave blank if the current course is enough for now.'
                        : 'Add at least one course to continue.')
                    ->required(fn (): bool => ! Course::query()->exists())
                    ->maxLength(20),
                Forms\Components\TextInput::make('course_title')
                    ->label('Course title')
                    ->required(fn (): bool => ! Course::query()->exists())
                    ->maxLength(255),
                SchemaActions::make([
                    Action::make('addCourse')
                        ->label('Add course')
                        ->icon('heroicon-o-plus')
                        ->action(fn () => $this->addCourse()),
                ])
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->afterValidation(function (): void {
                $this->saveCourseFromState();
                $this->saveProgress(self::STEP_GRADING_SCALE);
            });
    }

    private function gradingScaleStep(): Wizard\Step
    {
        return Wizard\Step::make('Grading Scale')
            ->icon('heroicon-o-chart-bar')
            ->schema([
                Section::make('Default grading scale')
                    ->description('Use the default rows as-is or edit them before finishing setup.')
                    ->schema([
                        Forms\Components\Repeater::make('grading_scales')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Hidden::make('id'),
                                Forms\Components\TextInput::make('letter_grade')
                                    ->label('Grade')
                                    ->required()
                                    ->maxLength(10),
                                Forms\Components\TextInput::make('min_score')
                                    ->label('Min')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->lt('max_score'),
                                Forms\Components\TextInput::make('max_score')
                                    ->label('Max')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->gt('min_score'),
                                Forms\Components\TextInput::make('gpa_equivalent')
                                    ->label('GPA')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(4),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->reorderable(false)
                            ->addActionLabel('Add grade row')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function addDepartment(): void
    {
        $department = $this->saveDepartmentFromState();
        $this->saveProgress(self::STEP_DEPARTMENT);

        if (! $department) {
            Notification::make()
                ->title('Enter a department name first.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Department added.')
            ->success()
            ->send();
    }

    public function addSpecialization(): void
    {
        $specialization = $this->saveSpecializationFromState();
        $this->saveProgress(self::STEP_SPECIALIZATION);

        if (! $specialization) {
            Notification::make()
                ->title('Enter a specialization name first.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Specialization added.')
            ->success()
            ->send();
    }

    public function addCourse(): void
    {
        $course = $this->saveCourseFromState();
        $this->saveProgress(self::STEP_COURSE);

        if (! $course) {
            Notification::make()
                ->title('Enter a course code and title first.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Course added.')
            ->success()
            ->send();
    }

    public function finishSetup(): void
    {
        $state = $this->form->getState();

        $this->saveDepartmentFromState();
        $this->saveSpecializationFromState();
        $this->saveCourseFromState();
        $this->saveProgress(self::STEP_GRADING_SCALE);
        MasterDataSetup::syncGradingScales($state['grading_scales'] ?? []);

        if (! MasterDataSetup::isComplete()) {
            Notification::make()
                ->title('Master data setup is still incomplete.')
                ->body('Missing: '.implode(', ', MasterDataSetup::missingLabels()))
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Master data setup complete.')
            ->success()
            ->send();

        $this->clearProgress();

        $this->redirect(Dashboard::getUrl());
    }

    private function saveDepartmentFromState(): ?Department
    {
        $name = trim((string) ($this->data['department_name'] ?? ''));

        if ($name === '') {
            return null;
        }

        if ($this->departmentExists($name)) {
            throw ValidationException::withMessages([
                'data.department_name' => "The department \"{$name}\" has already been added.",
            ]);
        }

        $department = Department::query()->create(['name' => $name]);

        $this->data['department_id'] = $department->id;
        $this->data['department_name'] = null;

        return $department;
    }

    private function saveSpecializationFromState(): ?Specialization
    {
        $name = trim((string) ($this->data['specialization_name'] ?? ''));

        if ($name === '') {
            return null;
        }

        $departmentId = $this->data['department_id'] ?? Department::query()->orderBy('name')->value('id');

        if (! $departmentId) {
            throw ValidationException::withMessages([
                'data.department_id' => 'Create a department before adding a specialization.',
            ]);
        }

        if ($this->specializationExists($name, (int) $departmentId)) {
            $departmentName = Department::query()->find($departmentId)?->name ?? 'the selected department';

            throw ValidationException::withMessages([
                'data.specialization_name' => "The specialization \"{$name}\" already exists under {$departmentName}.",
            ]);
        }

        $specialization = Specialization::query()->create([
            'department_id' => $departmentId,
            'name' => $name,
        ]);

        $this->data['specialization_name'] = null;

        return $specialization;
    }

    private function saveCourseFromState(): ?Course
    {
        $code = trim((string) ($this->data['course_code'] ?? ''));
        $title = trim((string) ($this->data['course_title'] ?? ''));

        if ($code === '' && $title === '') {
            return null;
        }

        if ($code === '' || $title === '') {
            throw ValidationException::withMessages([
                'data.course_code' => 'Course code and title are both required when adding a course.',
            ]);
        }

        if ($this->courseCodeExists($code)) {
            throw ValidationException::withMessages([
                'data.course_code' => "The course code \"{$code}\" has already been added.",
            ]);
        }

        $course = Course::query()->create(['code' => $code, 'title' => $title]);

        $this->data['course_code'] = null;
        $this->data['course_title'] = null;

        return $course;
    }

    private function currentDepartmentsHtml(): HtmlString
    {
        $records = Department::query()
            ->orderBy('name')
            ->limit(3)
            ->get(['name']);

        return $this->summaryListHtml(
            $records,
            Department::query()->count(),
            'department',
            fn (Department $department): string => $department->name,
        );
    }

    private function currentSpecializationsHtml(): HtmlString
    {
        $records = Specialization::query()
            ->with('department:id,name')
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'department_id', 'name']);

        return $this->summaryListHtml(
            $records,
            Specialization::query()->count(),
            'specialization',
            fn (Specialization $specialization): string => $specialization->name.' - '.($specialization->department?->name ?? 'No department'),
        );
    }

    private function currentCoursesHtml(): HtmlString
    {
        $records = Course::query()
            ->orderBy('code')
            ->limit(5)
            ->get(['code', 'title']);

        return $this->summaryListHtml(
            $records,
            Course::query()->count(),
            'course',
            fn (Course $course): string => "{$course->code} - {$course->title}",
        );
    }

    /**
     * @param  EloquentCollection<int, Department|Specialization|Course>  $records
     */
    private function summaryListHtml(EloquentCollection $records, int $totalCount, string $singularLabel, callable $labelUsing): HtmlString
    {
        if ($records->isEmpty()) {
            return new HtmlString('<div class="text-sm text-gray-500 dark:text-gray-400">None added yet.</div>');
        }

        $pluralLabel = Str::plural($singularLabel);
        $shownCount = $records->count();
        $extraCount = max(0, $totalCount - $shownCount);
        $items = $records
            ->map(fn ($record): string => '<li>'.e($labelUsing($record)).'</li>')
            ->implode('');

        $extra = $extraCount > 0
            ? '<div class="text-xs text-gray-500 dark:text-gray-400">+'.e((string) $extraCount).' more not shown</div>'
            : '';

        return new HtmlString(
            '<div class="space-y-2">'
            .'<div class="text-sm font-medium text-gray-950 dark:text-white">'.e((string) $totalCount).' '.e($totalCount === 1 ? $singularLabel : $pluralLabel).' added</div>'
            .'<ul class="list-disc space-y-1 ps-5 text-sm text-gray-700 dark:text-gray-300">'.$items.'</ul>'
            .$extra
            .'</div>'
        );
    }

    private function departmentExists(string $name): bool
    {
        return Department::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->exists();
    }

    private function specializationExists(string $name, int $departmentId): bool
    {
        return Specialization::query()
            ->where('department_id', $departmentId)
            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->exists();
    }

    private function courseCodeExists(string $code): bool
    {
        return Course::query()
            ->whereRaw('lower(code) = ?', [mb_strtolower($code)])
            ->exists();
    }

    private function saveProgress(?int $step = null): void
    {
        $user = auth()->user();

        if (! $user || ! $this->canPersistProgress()) {
            return;
        }

        $user->forceFill([
            'master_data_setup_progress' => [
                'step' => $this->normalizeStep($step ?? $this->currentStepFromRequest()),
                'data' => $this->progressData(),
                'saved_at' => now()->toISOString(),
            ],
        ])->save();
    }

    private function clearProgress(): void
    {
        if (! $this->canPersistProgress()) {
            return;
        }

        auth()->user()?->forceFill([
            'master_data_setup_progress' => null,
        ])->save();
    }

    private function canPersistProgress(): bool
    {
        static $hasColumn = null;

        return $hasColumn ??= SchemaFacade::hasColumn('users', 'master_data_setup_progress');
    }

    private function resumeStep(): int
    {
        return $this->normalizeStep($this->savedProgress()['step'] ?? self::STEP_DEPARTMENT);
    }

    /**
     * @return array{step?: int, data?: array<string, mixed>, saved_at?: string}
     */
    private function savedProgress(): array
    {
        $progress = auth()->user()?->master_data_setup_progress;

        return is_array($progress) ? $progress : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function progressData(): array
    {
        return array_filter(
            $this->data ?? [],
            fn ($value): bool => $value !== null && $value !== ''
        );
    }

    private function currentStepFromRequest(): int
    {
        $step = request()->query('step');

        if (blank($step)) {
            $referer = request()->headers->get('referer');
            $query = [];

            if (filled($referer)) {
                parse_str((string) parse_url($referer, PHP_URL_QUERY), $query);
                $step = $query['step'] ?? null;
            }
        }

        if (blank($step)) {
            return $this->resumeStep();
        }

        if (is_numeric($step)) {
            return $this->normalizeStep((int) $step);
        }

        return match (true) {
            str_contains((string) $step, 'specialization') => self::STEP_SPECIALIZATION,
            str_contains((string) $step, 'course') => self::STEP_COURSE,
            str_contains((string) $step, 'grading-scale') => self::STEP_GRADING_SCALE,
            default => self::STEP_DEPARTMENT,
        };
    }

    private function normalizeStep(mixed $step): int
    {
        return min(self::STEP_GRADING_SCALE, max(self::STEP_DEPARTMENT, (int) $step));
    }
}
