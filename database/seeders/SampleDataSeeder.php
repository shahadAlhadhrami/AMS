<?php

namespace Database\Seeders;

use App\Models\ConsolidatedMark;
use App\Models\ConsolidatedMarkComponent;
use App\Models\Course;
use App\Models\Criterion;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\EvaluationScore;
use App\Models\GradingScale;
use App\Models\PhaseRubricRule;
use App\Models\PhaseTemplate;
use App\Models\Project;
use App\Models\RubricTemplate;
use App\Models\ScoreLevel;
use App\Models\Semester;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Use existing data where possible
        $admin = User::where('email', 'admin@ams.test')->first();
        $csDept = Department::firstOrCreate(['name' => 'Computer Science']);
        $engDept = Department::firstOrCreate(['name' => 'Engineering']);

        $seSpc = Specialization::firstOrCreate(['department_id' => $csDept->id, 'name' => 'Software Engineering']);
        $aiSpc = Specialization::firstOrCreate(['department_id' => $csDept->id, 'name' => 'Artificial Intelligence']);

        $fyp1 = Course::firstOrCreate(['code' => 'CS490'], ['title' => 'Final Year Project I']);
        $fyp2 = Course::firstOrCreate(['code' => 'CS491'], ['title' => 'Final Year Project II']);

        // Use existing users & reset passwords for known access
        $coordinator = User::where('email', 'coordinator@ams.test')->first();

        // Staff: use staff@ams.test (Supervisor+Reviewer, password=Staff123)
        $supervisor = User::where('email', 'staff@ams.test')->first();
        $reviewer = User::where('email', 'reviewer@ams.test')->first()
                    ?? User::where('email', 'bob@ams.test')->first();

        // Student: use alice@ams.test, set known password
        $student1 = User::where('email', 'alice@ams.test')->first();
        if ($student1) {
            $student1->update(['password' => Hash::make('Student123')]);
        }

        // Get more students
        $student2 = User::where('email', 'stu004@test.com')->first();
        if ($student2) {
            $student2->update(['password' => Hash::make('Student123')]);
        }

        // Ensure rubric templates exist
        $rubricSupervisor = RubricTemplate::firstOrCreate(
            ['name' => 'Supervisor Evaluation Rubric'],
            ['version' => 1, 'total_marks' => 40.00, 'is_locked' => true, 'created_by' => $admin->id]
        );
        $rubricReviewer = RubricTemplate::firstOrCreate(
            ['name' => 'Reviewer Evaluation Rubric'],
            ['version' => 1, 'total_marks' => 60.00, 'is_locked' => true, 'created_by' => $admin->id]
        );

        // Ensure criteria exist
        $criteria1 = Criterion::firstOrCreate(
            ['rubric_template_id' => $rubricSupervisor->id, 'title' => 'Technical Competence'],
            ['description' => 'Evaluates technical skills and knowledge application', 'max_score' => 15.00, 'is_individual' => true]
        );
        $criteria2 = Criterion::firstOrCreate(
            ['rubric_template_id' => $rubricSupervisor->id, 'title' => 'Project Management'],
            ['description' => 'Evaluates planning, organization and time management', 'max_score' => 10.00, 'is_individual' => false]
        );
        $criteria3 = Criterion::firstOrCreate(
            ['rubric_template_id' => $rubricSupervisor->id, 'title' => 'Communication Skills'],
            ['description' => 'Evaluates written and verbal communication ability', 'max_score' => 15.00, 'is_individual' => true]
        );
        $criteria4 = Criterion::firstOrCreate(
            ['rubric_template_id' => $rubricReviewer->id, 'title' => 'Innovation & Creativity'],
            ['description' => 'Evaluates originality and creative problem solving', 'max_score' => 20.00, 'is_individual' => false]
        );
        $criteria5 = Criterion::firstOrCreate(
            ['rubric_template_id' => $rubricReviewer->id, 'title' => 'Presentation Quality'],
            ['description' => 'Evaluates presentation delivery and materials', 'max_score' => 20.00, 'is_individual' => true]
        );
        $criteria6 = Criterion::firstOrCreate(
            ['rubric_template_id' => $rubricReviewer->id, 'title' => 'Report Quality'],
            ['description' => 'Evaluates documentation and report writing', 'max_score' => 20.00, 'is_individual' => true]
        );

        // Score levels
        foreach ([$criteria1, $criteria2, $criteria3, $criteria4, $criteria5, $criteria6] as $criterion) {
            $maxScore = $criterion->max_score;
            $levels = [
                ['label' => 'Excellent',    'score_value' => $maxScore,        'description' => 'Outstanding performance', 'sort_order' => 1],
                ['label' => 'Good',         'score_value' => $maxScore * 0.75, 'description' => 'Above average performance', 'sort_order' => 2],
                ['label' => 'Satisfactory', 'score_value' => $maxScore * 0.50, 'description' => 'Meets expectations', 'sort_order' => 3],
                ['label' => 'Poor',         'score_value' => $maxScore * 0.25, 'description' => 'Below expectations', 'sort_order' => 4],
            ];
            foreach ($levels as $level) {
                ScoreLevel::firstOrCreate(
                    ['criterion_id' => $criterion->id, 'label' => $level['label']],
                    $level
                );
            }
        }

        // Phase templates
        $phase1 = PhaseTemplate::firstOrCreate(
            ['name' => 'FYP Phase 1 - Proposal & Design'],
            ['total_phase_marks' => 100.00, 'created_by' => $admin->id]
        );
        $phase2 = PhaseTemplate::firstOrCreate(
            ['name' => 'FYP Phase 2 - Implementation & Testing'],
            ['total_phase_marks' => 100.00, 'created_by' => $admin->id]
        );

        // Phase rubric rules
        PhaseRubricRule::firstOrCreate(
            ['phase_template_id' => $phase1->id, 'rubric_template_id' => $rubricSupervisor->id, 'evaluator_role' => 'Supervisor'],
            ['fill_order' => 1, 'max_marks' => 40.00, 'aggregation_method' => 'AVERAGE']
        );
        PhaseRubricRule::firstOrCreate(
            ['phase_template_id' => $phase1->id, 'rubric_template_id' => $rubricReviewer->id, 'evaluator_role' => 'Reviewer'],
            ['fill_order' => 2, 'max_marks' => 60.00, 'aggregation_method' => 'AVERAGE']
        );

        // Semesters
        $sem1 = Semester::firstOrCreate(
            ['name' => 'Semester 1', 'academic_year' => '2025/2026'],
            ['start_date' => '2025-09-01', 'end_date' => '2026-01-15', 'is_active' => true, 'is_closed' => false]
        );
        $sem2 = Semester::firstOrCreate(
            ['name' => 'Semester 2', 'academic_year' => '2025/2026'],
            ['start_date' => '2026-02-01', 'end_date' => '2026-06-15', 'is_active' => true, 'is_closed' => false]
        );

        if ($coordinator) {
            $sem1->coordinators()->syncWithoutDetaching([$coordinator->id]);
            $sem2->coordinators()->syncWithoutDetaching([$coordinator->id]);
        }

        // Projects
        if ($supervisor && $student1) {
            $project1 = Project::firstOrCreate(
                ['title' => 'Smart Campus Navigation System', 'semester_id' => $sem1->id],
                [
                    'course_id' => $fyp1->id, 'phase_template_id' => $phase1->id,
                    'specialization_id' => $seSpc->id, 'supervisor_id' => $supervisor->id, 'status' => 'evaluating',
                ]
            );
            $project2 = Project::firstOrCreate(
                ['title' => 'AI-Powered Student Attendance Tracker', 'semester_id' => $sem1->id],
                [
                    'course_id' => $fyp1->id, 'phase_template_id' => $phase1->id,
                    'specialization_id' => $aiSpc->id, 'supervisor_id' => $supervisor->id, 'status' => 'setup',
                ]
            );
            $project3 = Project::firstOrCreate(
                ['title' => 'Blockchain-Based Certificate Verification', 'semester_id' => $sem2->id],
                [
                    'course_id' => $fyp2->id, 'phase_template_id' => $phase2->id,
                    'specialization_id' => $seSpc->id, 'supervisor_id' => $supervisor->id, 'status' => 'completed',
                ]
            );

            $project1->students()->syncWithoutDetaching([$student1->id]);
            if ($student2) $project1->students()->syncWithoutDetaching([$student2->id]);
            $project3->students()->syncWithoutDetaching([$student1->id]);

            if ($reviewer) {
                $project1->reviewers()->syncWithoutDetaching([$reviewer->id]);
                $project2->reviewers()->syncWithoutDetaching([$reviewer->id]);
                $project3->reviewers()->syncWithoutDetaching([$reviewer->id]);
            }

            // Evaluations
            $eval1 = Evaluation::firstOrCreate(
                ['project_id' => $project1->id, 'rubric_template_id' => $rubricSupervisor->id, 'evaluator_id' => $supervisor->id],
                ['evaluator_role' => 'Supervisor', 'fill_order' => 1, 'status' => 'submitted', 'general_feedback' => 'Excellent work on the technical implementation.']
            );

            if ($reviewer) {
                Evaluation::firstOrCreate(
                    ['project_id' => $project1->id, 'rubric_template_id' => $rubricReviewer->id, 'evaluator_id' => $reviewer->id],
                    ['evaluator_role' => 'Reviewer', 'fill_order' => 2, 'status' => 'pending']
                );
            }

            $eval3 = Evaluation::firstOrCreate(
                ['project_id' => $project3->id, 'rubric_template_id' => $rubricSupervisor->id, 'evaluator_id' => $supervisor->id],
                ['evaluator_role' => 'Supervisor', 'fill_order' => 1, 'status' => 'submitted', 'general_feedback' => 'Well-structured project with thorough documentation.']
            );

            // Evaluation scores for eval1
            $sl1 = ScoreLevel::where('criterion_id', $criteria1->id)->where('label', 'Excellent')->first();
            $sl2 = ScoreLevel::where('criterion_id', $criteria2->id)->where('label', 'Excellent')->first();
            $sl3 = ScoreLevel::where('criterion_id', $criteria3->id)->where('label', 'Good')->first();

            EvaluationScore::firstOrCreate(
                ['evaluation_id' => $eval1->id, 'criterion_id' => $criteria1->id, 'student_id' => $student1->id],
                ['score_level_id' => $sl1?->id, 'score_awarded' => 14.00, 'feedback' => 'Strong technical skills']
            );
            EvaluationScore::firstOrCreate(
                ['evaluation_id' => $eval1->id, 'criterion_id' => $criteria3->id, 'student_id' => $student1->id],
                ['score_level_id' => $sl3?->id, 'score_awarded' => 11.00, 'feedback' => 'Good communication']
            );
            EvaluationScore::firstOrCreate(
                ['evaluation_id' => $eval1->id, 'criterion_id' => $criteria2->id, 'student_id' => null],
                ['score_level_id' => $sl2?->id, 'score_awarded' => 9.00, 'feedback' => 'Great project management']
            );

            // Consolidated marks
            $cm = ConsolidatedMark::firstOrCreate(
                ['project_id' => $project3->id, 'phase_template_id' => $phase2->id, 'student_id' => $student1->id],
                ['total_calculated_score' => 82.50]
            );
            ConsolidatedMarkComponent::firstOrCreate(
                ['consolidated_mark_id' => $cm->id, 'source_label' => 'Supervisor Evaluation'],
                ['score' => 35.00]
            );
            ConsolidatedMarkComponent::firstOrCreate(
                ['consolidated_mark_id' => $cm->id, 'source_label' => 'Reviewer Evaluation'],
                ['score' => 47.50]
            );
        }

        $this->command->info('Sample data seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('  Admin:   admin@ams.test / Admin123');
        $this->command->info('  Staff:   staff@ams.test / Staff123');
        $this->command->info('  Student: alice@ams.test / Student123');
    }
}
