<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['is_approved', 'deleted_at'], 'users_approval_list_idx');
            $table->index(['specialization_id', 'deleted_at'], 'users_specialization_list_idx');
        });

        Schema::table('semesters', function (Blueprint $table) {
            $table->index(['is_active', 'is_closed', 'deleted_at'], 'semesters_active_closed_idx');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index(['semester_id', 'status', 'deleted_at'], 'projects_semester_status_idx');
            $table->index(['course_id', 'deleted_at'], 'projects_course_list_idx');
            $table->index(['supervisor_id', 'status', 'deleted_at'], 'projects_supervisor_status_idx');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->index(['evaluator_id', 'status', 'deleted_at'], 'evaluations_evaluator_status_idx');
            $table->index(['project_id', 'status', 'deleted_at'], 'evaluations_project_status_idx');
            $table->index(['status', 'deleted_at'], 'evaluations_status_list_idx');
        });

        Schema::table('rubric_templates', function (Blueprint $table) {
            $table->index(['rubric_folder_id', 'deleted_at'], 'rubric_templates_folder_list_idx');
            $table->index(['created_by', 'deleted_at'], 'rubric_templates_creator_idx');
            $table->index(['is_locked', 'deleted_at'], 'rubric_templates_locked_idx');
        });

        Schema::table('rubric_folders', function (Blueprint $table) {
            $table->index(['parent_id', 'deleted_at'], 'rubric_folders_parent_idx');
        });

        Schema::table('project_student', function (Blueprint $table) {
            $table->index(['user_id', 'project_id', 'deleted_at'], 'project_student_user_project_idx');
            $table->index(['project_id', 'deleted_at'], 'project_student_project_idx');
        });

        Schema::table('project_reviewer', function (Blueprint $table) {
            $table->index(['user_id', 'project_id', 'deleted_at'], 'project_reviewer_user_project_idx');
            $table->index(['project_id', 'deleted_at'], 'project_reviewer_project_idx');
        });

        Schema::table('coordinator_semester', function (Blueprint $table) {
            $table->index(['user_id', 'semester_id', 'deleted_at'], 'coordinator_semester_user_semester_idx');
            $table->index(['semester_id', 'deleted_at'], 'coordinator_semester_semester_idx');
        });

        Schema::table('consolidated_marks', function (Blueprint $table) {
            $table->index(['student_id', 'deleted_at'], 'consolidated_marks_student_idx');
            $table->index(['project_id', 'phase_template_id', 'deleted_at'], 'consolidated_marks_project_phase_idx');
        });

        Schema::table('evaluation_scores', function (Blueprint $table) {
            $table->index(['evaluation_id', 'student_id', 'deleted_at'], 'evaluation_scores_evaluation_student_idx');
            $table->index(['criterion_id', 'deleted_at'], 'evaluation_scores_criterion_idx');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_scores', function (Blueprint $table) {
            $table->dropIndex('evaluation_scores_evaluation_student_idx');
            $table->dropIndex('evaluation_scores_criterion_idx');
        });

        Schema::table('consolidated_marks', function (Blueprint $table) {
            $table->dropIndex('consolidated_marks_student_idx');
            $table->dropIndex('consolidated_marks_project_phase_idx');
        });

        Schema::table('coordinator_semester', function (Blueprint $table) {
            $table->dropIndex('coordinator_semester_user_semester_idx');
            $table->dropIndex('coordinator_semester_semester_idx');
        });

        Schema::table('project_reviewer', function (Blueprint $table) {
            $table->dropIndex('project_reviewer_user_project_idx');
            $table->dropIndex('project_reviewer_project_idx');
        });

        Schema::table('project_student', function (Blueprint $table) {
            $table->dropIndex('project_student_user_project_idx');
            $table->dropIndex('project_student_project_idx');
        });

        Schema::table('rubric_folders', function (Blueprint $table) {
            $table->dropIndex('rubric_folders_parent_idx');
        });

        Schema::table('rubric_templates', function (Blueprint $table) {
            $table->dropIndex('rubric_templates_folder_list_idx');
            $table->dropIndex('rubric_templates_creator_idx');
            $table->dropIndex('rubric_templates_locked_idx');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndex('evaluations_evaluator_status_idx');
            $table->dropIndex('evaluations_project_status_idx');
            $table->dropIndex('evaluations_status_list_idx');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_semester_status_idx');
            $table->dropIndex('projects_course_list_idx');
            $table->dropIndex('projects_supervisor_status_idx');
        });

        Schema::table('semesters', function (Blueprint $table) {
            $table->dropIndex('semesters_active_closed_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_approval_list_idx');
            $table->dropIndex('users_specialization_list_idx');
        });
    }
};
