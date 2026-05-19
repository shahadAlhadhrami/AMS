<?php

namespace Tests\Feature\Staff;

use App\Filament\Staff\Pages\EvaluationForm;
use App\Filament\Staff\Pages\ProjectDetail;
use App\Filament\Staff\Widgets\ReviewAssignmentsWidget;
use App\Filament\Staff\Widgets\SupervisedProjectsWidget;
use App\Models\Course;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\PhaseTemplate;
use App\Models\Project;
use App\Models\RubricTemplate;
use App\Models\Semester;
use App\Models\Specialization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupervisedProjectsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervised_projects_do_not_offer_reviewer_only_assessments(): void
    {
        $this->seed(RoleSeeder::class);

        $staff = $this->createStaffUser();
        $project = $this->createSupervisedProject($staff);

        $reviewerEvaluation = Evaluation::create([
            'project_id' => $project->id,
            'rubric_template_id' => $this->createRubricTemplate($staff, 'Proposal (External) - 10 marks')->id,
            'evaluator_id' => $staff->id,
            'evaluator_role' => 'Reviewer',
            'fill_order' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($staff);

        Livewire::test(SupervisedProjectsWidget::class)
            ->assertCanSeeTableRecords([$project])
            ->assertTableActionHidden('fillAssessment', $project->getKey());

        Livewire::test(ReviewAssignmentsWidget::class)
            ->assertCanSeeTableRecords([$reviewerEvaluation])
            ->assertTableActionVisible('fillAssessment', $reviewerEvaluation->getKey());
    }

    public function test_staff_dashboard_ignores_assignments_for_deleted_projects(): void
    {
        $this->seed(RoleSeeder::class);

        $staff = $this->createStaffUser();
        $project = $this->createSupervisedProject($staff);

        $reviewerEvaluation = Evaluation::create([
            'project_id' => $project->id,
            'rubric_template_id' => $this->createRubricTemplate($staff, 'Proposal (External) - 10 marks')->id,
            'evaluator_id' => $staff->id,
            'evaluator_role' => 'Reviewer',
            'fill_order' => 1,
            'status' => 'pending',
        ]);

        $project->delete();

        $this->actingAs($staff);

        Livewire::test(ReviewAssignmentsWidget::class)
            ->assertCountTableRecords(0)
            ->assertCanNotSeeTableRecords([$reviewerEvaluation]);

        $this->get('/staff/dashboard')->assertOk();
    }

    public function test_supervised_projects_open_the_supervisor_assessment_when_roles_overlap(): void
    {
        $this->seed(RoleSeeder::class);

        $staff = $this->createStaffUser();
        $project = $this->createSupervisedProject($staff);

        $reviewerEvaluation = Evaluation::create([
            'project_id' => $project->id,
            'rubric_template_id' => $this->createRubricTemplate($staff, 'Proposal (External) - 10 marks')->id,
            'evaluator_id' => $staff->id,
            'evaluator_role' => 'Reviewer',
            'fill_order' => 1,
            'status' => 'pending',
        ]);

        $supervisorEvaluation = Evaluation::create([
            'project_id' => $project->id,
            'rubric_template_id' => $this->createRubricTemplate($staff, 'Supervisor Evaluation - 20 marks')->id,
            'evaluator_id' => $staff->id,
            'evaluator_role' => 'Supervisor',
            'fill_order' => 1,
            'status' => 'pending',
        ]);

        $this->assertLessThan($supervisorEvaluation->id, $reviewerEvaluation->id);

        $this->actingAs($staff);

        Livewire::test(SupervisedProjectsWidget::class)
            ->assertTableActionVisible('fillAssessment', $project->getKey())
            ->assertTableActionHasUrl(
                'fillAssessment',
                EvaluationForm::getUrl(['evaluation' => $supervisorEvaluation->id], panel: 'staff'),
                $project->getKey(),
            );
    }

    public function test_project_detail_separates_supervisor_and_reviewer_assessments_by_context(): void
    {
        $this->seed(RoleSeeder::class);

        $staff = $this->createStaffUser();
        $project = $this->createSupervisedProject($staff);

        Evaluation::create([
            'project_id' => $project->id,
            'rubric_template_id' => $this->createRubricTemplate($staff, 'Proposal (External) - 10 marks')->id,
            'evaluator_id' => $staff->id,
            'evaluator_role' => 'Reviewer',
            'fill_order' => 1,
            'status' => 'pending',
        ]);

        Evaluation::create([
            'project_id' => $project->id,
            'rubric_template_id' => $this->createRubricTemplate($staff, 'Review I (Supervisor) - 10 marks')->id,
            'evaluator_id' => $staff->id,
            'evaluator_role' => 'Supervisor',
            'fill_order' => 2,
            'status' => 'pending',
        ]);

        $this->actingAs($staff);

        $this->get(ProjectDetail::getUrl([
            'project' => $project->id,
            'context' => 'supervisor',
        ], panel: 'staff'))
            ->assertOk()
            ->assertSee('Supervisor Assessments')
            ->assertSee('Review I (Supervisor) - 10 marks')
            ->assertDontSee('Proposal (External) - 10 marks');

        $this->get(ProjectDetail::getUrl([
            'project' => $project->id,
            'context' => 'reviewer',
        ], panel: 'staff'))
            ->assertOk()
            ->assertSee('Review Assignments')
            ->assertSee('Proposal (External) - 10 marks')
            ->assertDontSee('Review I (Supervisor) - 10 marks');
    }

    private function createStaffUser(): User
    {
        $user = User::factory()->create(['is_approved' => true]);
        $user->assignRole('Reviewer/Supervisor');

        return $user;
    }

    private function createSupervisedProject(User $staff): Project
    {
        $department = Department::create(['name' => 'Information Technology']);
        $specialization = Specialization::create([
            'department_id' => $department->id,
            'name' => 'Software Engineering',
        ]);
        $course = Course::create([
            'code' => 'IT4001',
            'title' => 'B.Tech Project Phase I',
        ]);
        $semester = Semester::create([
            'name' => 'Spring 2026',
            'academic_year' => '2025/2026',
        ]);
        $phaseTemplate = PhaseTemplate::create([
            'name' => 'Phase 1',
            'total_phase_marks' => 30,
            'created_by' => $staff->id,
        ]);

        $project = Project::create([
            'semester_id' => $semester->id,
            'course_id' => $course->id,
            'phase_template_id' => $phaseTemplate->id,
            'specialization_id' => $specialization->id,
            'title' => 'Smart Campus Companion App',
            'supervisor_id' => $staff->id,
            'status' => 'evaluating',
        ]);

        $project->reviewers()->attach($staff);

        return $project;
    }

    private function createRubricTemplate(User $staff, string $name): RubricTemplate
    {
        return RubricTemplate::create([
            'name' => $name,
            'total_marks' => 10,
            'created_by' => $staff->id,
        ]);
    }
}
