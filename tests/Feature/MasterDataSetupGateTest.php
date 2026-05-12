<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\MasterDataSetupWizard;
use App\Models\Course;
use App\Models\Department;
use App\Models\Specialization;
use App\Models\User;
use App\Support\MasterDataSetup;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterDataSetupGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_is_redirected_to_master_data_setup_when_foundations_are_missing(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();

        $this->get('/admin')
            ->assertRedirect('/admin/master-data-setup');
    }

    public function test_master_data_setup_progress_is_saved_while_typing(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();

        Livewire::test(MasterDataSetupWizard::class)
            ->set('data.department_name', 'Information Technology');

        $progress = $user->refresh()->master_data_setup_progress;

        $this->assertSame(MasterDataSetupWizard::STEP_DEPARTMENT, $progress['step']);
        $this->assertSame('Information Technology', $progress['data']['department_name']);
        $this->assertNotEmpty($progress['saved_at']);
    }

    public function test_master_data_setup_progress_moves_forward_after_completed_step(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();

        Livewire::test(MasterDataSetupWizard::class)
            ->set('data.department_name', 'Information Technology')
            ->goToNextWizardStep()
            ->assertWizardCurrentStep(MasterDataSetupWizard::STEP_SPECIALIZATION);

        $this->assertDatabaseHas('departments', ['name' => 'Information Technology']);
        $this->assertSame(
            MasterDataSetupWizard::STEP_SPECIALIZATION,
            $user->refresh()->master_data_setup_progress['step'],
        );
    }

    public function test_master_data_setup_can_add_multiple_departments_without_leaving_the_step(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();

        Livewire::test(MasterDataSetupWizard::class)
            ->set('data.department_name', 'Information Technology')
            ->call('addDepartment')
            ->assertSet('data.department_name', null)
            ->assertWizardCurrentStep(MasterDataSetupWizard::STEP_DEPARTMENT)
            ->set('data.department_name', 'Engineering')
            ->call('addDepartment')
            ->assertSet('data.department_name', null);

        $this->assertDatabaseHas('departments', ['name' => 'Information Technology']);
        $this->assertDatabaseHas('departments', ['name' => 'Engineering']);
        $this->assertSame(2, Department::count());
    }

    public function test_master_data_setup_rejects_duplicate_department_names(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();
        Department::create(['name' => 'Information Technology']);

        Livewire::test(MasterDataSetupWizard::class)
            ->set('data.department_name', 'information technology')
            ->call('addDepartment')
            ->assertHasErrors(['data.department_name']);

        $this->assertSame(1, Department::count());
    }

    public function test_master_data_setup_can_add_multiple_specializations_without_leaving_the_step(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();
        $department = Department::create(['name' => 'Information Technology']);

        Livewire::test(MasterDataSetupWizard::class)
            ->set('data.department_id', $department->id)
            ->set('data.specialization_name', 'Software Engineering')
            ->call('addSpecialization')
            ->assertSet('data.specialization_name', null)
            ->assertWizardCurrentStep(MasterDataSetupWizard::STEP_SPECIALIZATION)
            ->set('data.specialization_name', 'Cybersecurity')
            ->call('addSpecialization')
            ->assertSet('data.specialization_name', null);

        $this->assertDatabaseHas('specializations', ['name' => 'Software Engineering']);
        $this->assertDatabaseHas('specializations', ['name' => 'Cybersecurity']);
        $this->assertSame(2, Specialization::count());
    }

    public function test_master_data_setup_rejects_duplicate_specializations_in_the_same_department(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();
        $department = Department::create(['name' => 'Information Technology']);
        Specialization::create([
            'department_id' => $department->id,
            'name' => 'Software Engineering',
        ]);

        Livewire::test(MasterDataSetupWizard::class)
            ->set('data.department_id', $department->id)
            ->set('data.specialization_name', 'software engineering')
            ->call('addSpecialization')
            ->assertHasErrors(['data.specialization_name']);

        $this->assertSame(1, Specialization::count());
    }

    public function test_master_data_setup_can_add_multiple_courses_without_leaving_the_step(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();

        Livewire::test(MasterDataSetupWizard::class)
            ->set('data.course_code', 'IT4001')
            ->set('data.course_title', 'B.Tech Project Phase I')
            ->call('addCourse')
            ->assertSet('data.course_code', null)
            ->assertSet('data.course_title', null)
            ->assertWizardCurrentStep(MasterDataSetupWizard::STEP_COURSE)
            ->set('data.course_code', 'IT4002')
            ->set('data.course_title', 'B.Tech Project Phase II')
            ->call('addCourse')
            ->assertSet('data.course_code', null)
            ->assertSet('data.course_title', null);

        $this->assertDatabaseHas('courses', ['code' => 'IT4001']);
        $this->assertDatabaseHas('courses', ['code' => 'IT4002']);
        $this->assertSame(2, Course::count());
    }

    public function test_master_data_setup_rejects_duplicate_course_codes(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();
        Course::create([
            'code' => 'IT4001',
            'title' => 'B.Tech Project Phase I',
        ]);

        Livewire::test(MasterDataSetupWizard::class)
            ->set('data.course_code', 'it4001')
            ->set('data.course_title', 'B.Tech Project Phase I')
            ->call('addCourse')
            ->assertHasErrors(['data.course_code']);

        $this->assertSame(1, Course::count());
    }

    public function test_master_data_setup_shows_readable_current_record_summaries(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();
        $department = Department::create(['name' => 'Information Technology']);
        Specialization::create([
            'department_id' => $department->id,
            'name' => 'Software Engineering',
        ]);
        Course::create([
            'code' => 'IT4001',
            'title' => 'B.Tech Project Phase I',
        ]);

        Livewire::test(MasterDataSetupWizard::class)
            ->assertSee('1 department added')
            ->assertSee('Information Technology')
            ->assertSee('1 specialization added')
            ->assertSee('Software Engineering - Information Technology')
            ->assertSee('1 course added')
            ->assertSee('IT4001 - B.Tech Project Phase I');
    }

    public function test_master_data_setup_resumes_saved_step_and_fields(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create([
            'is_approved' => true,
            'master_data_setup_progress' => [
                'step' => MasterDataSetupWizard::STEP_COURSE,
                'data' => [
                    'course_code' => 'IT4001',
                    'course_title' => 'B.Tech Project Phase I',
                ],
                'saved_at' => now()->toISOString(),
            ],
        ]);
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();

        Livewire::test(MasterDataSetupWizard::class)
            ->assertSet('data.course_code', 'IT4001')
            ->assertSet('data.course_title', 'B.Tech Project Phase I')
            ->assertWizardCurrentStep(MasterDataSetupWizard::STEP_COURSE);
    }
}
