<?php

namespace Tests\Unit;

use App\Filament\Admin\Pages\MasterDataSetupWizard;
use App\Filament\Admin\Resources\CourseResource;
use App\Models\Course;
use App\Models\Department;
use App\Models\GradingScale;
use App\Models\Specialization;
use App\Models\User;
use App\Support\MasterDataSetup;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MasterDataSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_grading_scales_are_seeded_once(): void
    {
        MasterDataSetup::ensureDefaultGradingScales();
        MasterDataSetup::ensureDefaultGradingScales();

        $this->assertSame(8, GradingScale::count());
        $this->assertDatabaseHas('grading_scales', [
            'letter_grade' => 'A',
            'min_score' => 90.00,
            'max_score' => 100.00,
            'gpa_equivalent' => 4.00,
        ]);
        $this->assertDatabaseHas('grading_scales', [
            'letter_grade' => 'F',
            'min_score' => 0.00,
            'max_score' => 59.99,
            'gpa_equivalent' => 0.00,
        ]);
    }

    public function test_master_data_setup_requires_one_foundation_record_each(): void
    {
        MasterDataSetup::ensureDefaultGradingScales();

        $this->assertFalse(MasterDataSetup::isComplete());
        $this->assertSame(['Department', 'Specialization', 'Course'], MasterDataSetup::missingLabels());

        $department = Department::create(['name' => 'Information Technology']);
        Specialization::create([
            'department_id' => $department->id,
            'name' => 'Software Engineering',
        ]);
        Course::create([
            'code' => 'IT4001',
            'title' => 'B.Tech Project Phase I',
        ]);

        $this->assertTrue(MasterDataSetup::isComplete());
        $this->assertSame([], MasterDataSetup::missingLabels());
    }

    public function test_grading_scale_sync_rejects_overlapping_ranges(): void
    {
        $this->expectException(ValidationException::class);

        MasterDataSetup::syncGradingScales([
            ['letter_grade' => 'A', 'min_score' => 80, 'max_score' => 100, 'gpa_equivalent' => 4],
            ['letter_grade' => 'B', 'min_score' => 70, 'max_score' => 85, 'gpa_equivalent' => 3],
        ]);
    }

    public function test_navigation_focuses_super_admin_on_master_data_setup_until_complete(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $this->actingAs($user);

        MasterDataSetup::ensureDefaultGradingScales();

        $this->assertTrue(MasterDataSetupWizard::shouldRegisterNavigation());
        $this->assertFalse(CourseResource::shouldRegisterNavigation());

        $department = Department::create(['name' => 'Information Technology']);
        Specialization::create([
            'department_id' => $department->id,
            'name' => 'Software Engineering',
        ]);
        Course::create([
            'code' => 'IT4001',
            'title' => 'B.Tech Project Phase I',
        ]);

        $this->assertFalse(MasterDataSetupWizard::shouldRegisterNavigation());
        $this->assertTrue(CourseResource::shouldRegisterNavigation());
    }
}
