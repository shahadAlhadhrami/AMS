-- Domain 1: Users, Access & Master Data
-- Note: User Roles and Permissions will be handled automatically by spatie/laravel-permission package

CREATE TABLE `departments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL
);

CREATE TABLE `specializations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `department_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
);

CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `university_id` VARCHAR(255) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `specialization_id` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`specialization_id`) REFERENCES `specializations`(`id`) ON DELETE SET NULL
);

CREATE TABLE `courses` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL
);

CREATE TABLE `grading_scales` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `min_score` DECIMAL(5,2) NOT NULL,
    `max_score` DECIMAL(5,2) NOT NULL,
    `letter_grade` VARCHAR(10) NOT NULL,
    `gpa_equivalent` DECIMAL(3,2) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL
);


-- Domain 2: The Template Pool (The Workflow Engine)

CREATE TABLE `rubric_templates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `version` INT NOT NULL DEFAULT 1,
    `parent_template_id` BIGINT UNSIGNED NULL,
    `total_marks` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `is_locked` BOOLEAN NOT NULL DEFAULT 0,
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`parent_template_id`) REFERENCES `rubric_templates`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `criteria` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `max_score` DECIMAL(8,2) NOT NULL,
    `is_individual` BOOLEAN NOT NULL,
    `rubric_template_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`rubric_template_id`) REFERENCES `rubric_templates`(`id`) ON DELETE CASCADE
);

CREATE TABLE `score_levels` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `criterion_id` BIGINT UNSIGNED NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    `score_value` DECIMAL(8,2) NOT NULL,
    `description` TEXT NULL,
    `sort_order` INT NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`criterion_id`) REFERENCES `criteria`(`id`) ON DELETE CASCADE
);

CREATE TABLE `phase_templates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `total_phase_marks` DECIMAL(8,2) NOT NULL,
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `phase_rubric_rules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `phase_template_id` BIGINT UNSIGNED NOT NULL,
    `rubric_template_id` BIGINT UNSIGNED NOT NULL,
    `evaluator_role` VARCHAR(255) NOT NULL,
    `fill_order` INT NOT NULL,
    `max_marks` DECIMAL(8,2) NOT NULL,
    `aggregation_method` ENUM('AVERAGE', 'WEIGHTED_AVERAGE', 'SUM', 'MAX') NOT NULL DEFAULT 'AVERAGE',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`phase_template_id`) REFERENCES `phase_templates`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rubric_template_id`) REFERENCES `rubric_templates`(`id`) ON DELETE CASCADE
);

-- Domain 3: Academic Setup (The Active Sandbox)

CREATE TABLE `semesters` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `academic_year` VARCHAR(255) NOT NULL,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT 1,
    `is_closed` BOOLEAN NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL
);

CREATE TABLE `coordinator_semester` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `semester_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`semester_id`) REFERENCES `semesters`(`id`) ON DELETE CASCADE
);

CREATE TABLE `projects` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `semester_id` BIGINT UNSIGNED NOT NULL,
    `course_id` BIGINT UNSIGNED NOT NULL,
    `phase_template_id` BIGINT UNSIGNED NOT NULL,
    `specialization_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `supervisor_id` BIGINT UNSIGNED NOT NULL,
    `previous_phase_project_id` BIGINT UNSIGNED NULL,
    `status` ENUM('setup', 'evaluating', 'completed') NOT NULL DEFAULT 'setup',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`semester_id`) REFERENCES `semesters`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`phase_template_id`) REFERENCES `phase_templates`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`specialization_id`) REFERENCES `specializations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`supervisor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`previous_phase_project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
);

CREATE TABLE `project_student` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `project_reviewer` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Domain 4: Execution & Scoring (The Grading Interface)

CREATE TABLE `evaluations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `rubric_template_id` BIGINT UNSIGNED NOT NULL,
    `evaluator_id` BIGINT UNSIGNED NOT NULL,
    `evaluator_role` VARCHAR(255) NOT NULL,
    `on_behalf_of_user_id` BIGINT UNSIGNED NULL,
    `evidence_attachment_path` VARCHAR(255) NULL,
    `status` ENUM('pending', 'draft', 'submitted') NOT NULL DEFAULT 'pending',
    `general_feedback` TEXT NULL,
    `unlocked_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `unique_evaluation` (`project_id`, `rubric_template_id`, `evaluator_id`),
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rubric_template_id`) REFERENCES `rubric_templates`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`evaluator_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`on_behalf_of_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`unlocked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

CREATE TABLE `evaluation_scores` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `evaluation_id` BIGINT UNSIGNED NOT NULL,
    `criterion_id` BIGINT UNSIGNED NOT NULL,
    `score_level_id` BIGINT UNSIGNED NULL,
    `student_id` BIGINT UNSIGNED NULL,
    `score_awarded` DECIMAL(8,2) NOT NULL,
    `feedback` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`criterion_id`) REFERENCES `criteria`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`score_level_id`) REFERENCES `score_levels`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `consolidated_marks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `phase_template_id` BIGINT UNSIGNED NOT NULL,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `total_calculated_score` DECIMAL(8,2) NOT NULL,
    `override_score` DECIMAL(8,2) NULL,
    `override_reason` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`phase_template_id`) REFERENCES `phase_templates`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `consolidated_mark_components` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `consolidated_mark_id` BIGINT UNSIGNED NOT NULL,
    `source_label` VARCHAR(255) NOT NULL,
    `score` DECIMAL(8,2) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`consolidated_mark_id`) REFERENCES `consolidated_marks`(`id`) ON DELETE CASCADE
);
