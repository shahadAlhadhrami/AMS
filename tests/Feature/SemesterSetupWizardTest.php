<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\SemesterSetupWizard;
use App\Models\Course;
use App\Models\Department;
use App\Models\PhaseTemplate;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SemesterSetupWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_semester_setup_can_select_an_existing_semester_without_creating_one(): void
    {
        $this->seed(RoleSeeder::class);
        $this->actingAsSuperAdmin();

        $semester = Semester::create([
            'name' => 'Fall 2026',
            'academic_year' => '2025-2026',
            'is_active' => true,
            'is_closed' => false,
        ]);

        Livewire::test(SemesterSetupWizard::class)
            ->set('data.semester_mode', 'existing')
            ->set('data.semester_id', $semester->id)
            ->goToWizardStep(2)
            ->assertSet('semesterId', $semester->id)
            ->assertSet('semesterWasCreated', false);

        $this->assertSame(1, Semester::count());
    }

    public function test_semester_setup_can_create_a_new_semester_when_needed(): void
    {
        $this->seed(RoleSeeder::class);
        $this->actingAsSuperAdmin();

        Livewire::test(SemesterSetupWizard::class)
            ->set('data.semester_mode', 'create')
            ->set('data.semester_name', 'Spring 2027')
            ->set('data.academic_year', '2026-2027')
            ->set('data.start_date', '2027-01-12')
            ->set('data.end_date', '2027-05-20')
            ->goToWizardStep(2)
            ->assertSet('semesterWasCreated', true)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('semesters', [
            'name' => 'Spring 2027',
            'academic_year' => '2026-2027',
            'is_active' => true,
            'is_closed' => false,
        ]);
    }

    public function test_manual_project_creation_uses_selected_semester_and_single_phase_template(): void
    {
        $this->seed(RoleSeeder::class);
        $superAdmin = $this->actingAsSuperAdmin();

        $semester = Semester::create([
            'name' => 'Fall 2026',
            'academic_year' => '2025-2026',
            'is_active' => true,
            'is_closed' => false,
        ]);
        $department = Department::create(['name' => 'Information Technology']);
        $specialization = Specialization::create([
            'department_id' => $department->id,
            'name' => 'Software Engineering',
        ]);
        $course = Course::create([
            'code' => 'IT4001',
            'title' => 'B.Tech Project Phase I',
        ]);
        $phaseTemplate = PhaseTemplate::create([
            'name' => 'FYP Phase 1',
            'total_phase_marks' => 100,
            'created_by' => $superAdmin->id,
        ]);
        $supervisor = User::factory()->create(['university_id' => 'e1001']);
        $supervisor->assignRole('Reviewer/Supervisor');

        Livewire::test(SemesterSetupWizard::class)
            ->set('data.semester_mode', 'existing')
            ->set('data.semester_id', $semester->id)
            ->goToWizardStep(2)
            ->set('data.phase_template_id', $phaseTemplate->id)
            ->goToWizardStep(3)
            ->assertHasNoErrors()
            ->assertWizardCurrentStep(3)
            ->set('data.project_entry_method', 'manual')
            ->set('data.manual_projects', [[
                'title' => 'Smart Campus Companion',
                'semester_id' => $semester->id,
                'course_id' => $course->id,
                'phase_template_id' => $phaseTemplate->id,
                'specialization_id' => $specialization->id,
                'supervisor_id' => $supervisor->id,
                'previous_phase_project_id' => null,
                'status' => 'setup',
            ]])
            ->goToWizardStep(4)
            ->assertWizardCurrentStep(4)
            ->assertHasNoErrors();

        $project = Project::firstOrFail();

        $this->assertSame('Smart Campus Companion', $project->title);
        $this->assertSame($semester->id, $project->semester_id);
        $this->assertSame($phaseTemplate->id, $project->phase_template_id);
    }

    private function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        return $user;
    }
}
