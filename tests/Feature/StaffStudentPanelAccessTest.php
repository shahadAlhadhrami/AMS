<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffStudentPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_user_can_reach_staff_panel_home(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Reviewer/Supervisor');

        $this->actingAs($user)
            ->get('/staff')
            ->assertRedirect('/staff/dashboard');

        $this->actingAs($user)
            ->get('/staff/dashboard')
            ->assertOk();
    }

    public function test_student_user_can_reach_student_panel_home(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Student');

        $this->actingAs($user)
            ->get('/student')
            ->assertRedirect('/student/dashboard');

        $this->actingAs($user)
            ->get('/student/dashboard')
            ->assertOk();
    }

    public function test_staff_user_cannot_access_student_panel(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Reviewer/Supervisor');

        $this->actingAs($user)
            ->get('/student')
            ->assertForbidden();
    }

    public function test_student_user_cannot_access_staff_panel(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Student');

        $this->actingAs($user)
            ->get('/staff')
            ->assertForbidden();
    }
}
