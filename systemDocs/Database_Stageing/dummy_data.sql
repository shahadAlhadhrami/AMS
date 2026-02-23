-- Disable foreign key checks for clean inserts if re-running
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `consolidated_mark_components`;
TRUNCATE TABLE `consolidated_marks`;
TRUNCATE TABLE `evaluation_scores`;
TRUNCATE TABLE `evaluations`;
TRUNCATE TABLE `project_reviewer`;
TRUNCATE TABLE `project_student`;
TRUNCATE TABLE `projects`;
TRUNCATE TABLE `coordinator_semester`;
TRUNCATE TABLE `semesters`;
TRUNCATE TABLE `phase_rubric_rules`;
TRUNCATE TABLE `phase_templates`;
TRUNCATE TABLE `score_levels`;
TRUNCATE TABLE `criteria`;
TRUNCATE TABLE `rubric_templates`;
TRUNCATE TABLE `grading_scales`;
TRUNCATE TABLE `courses`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `specializations`;
TRUNCATE TABLE `departments`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Departments & Specializations
INSERT INTO `departments` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Information Technology', NOW(), NOW());

INSERT INTO `specializations` (`id`, `department_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Software Engineering', NOW(), NOW()),
(2, 1, 'IT Networking', NOW(), NOW()),
(3, 1, 'Information Security', NOW(), NOW());

-- 2. Courses
INSERT INTO `courses` (`id`, `code`, `title`, `created_at`, `updated_at`) VALUES
(1, 'IT4001', 'B.Tech Project Phase I', NOW(), NOW()),
(2, 'IT4002', 'B.Tech Project Phase II', NOW(), NOW());

-- 3. Grading Scales
INSERT INTO `grading_scales` (`min_score`, `max_score`, `letter_grade`, `gpa_equivalent`, `created_at`, `updated_at`) VALUES
(90.00, 100.00, 'A', 4.00, NOW(), NOW()),
(85.00, 89.99, 'A-', 3.70, NOW(), NOW()),
(80.00, 84.99, 'B+', 3.30, NOW(), NOW()),
(75.00, 79.99, 'B', 3.00, NOW(), NOW()),
(70.00, 74.99, 'C+', 2.70, NOW(), NOW()),
(65.00, 69.99, 'C', 2.30, NOW(), NOW()),
(60.00, 64.99, 'D', 1.00, NOW(), NOW()),
(0.00, 59.99, 'F', 0.00, NOW(), NOW());

-- 4. Users
-- Default hashed password for all dummy accounts: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (which equals 'password')
INSERT INTO `users` (`id`, `university_id`, `name`, `email`, `password`, `specialization_id`, `created_at`, `updated_at`) VALUES
(1, 'ADM001', 'Super Admin', 'admin@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NOW(), NOW()),
(2, 'STAFF001', 'Dr. Khalid (Coordinator)', 'khalid@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
(3, 'STAFF002', 'Dr. Ahmed (Supervisor/Reviewer)', 'ahmed@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
(4, 'STAFF003', 'Dr. Fatma (Supervisor/Reviewer)', 'fatma@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NOW(), NOW()),
(5, 'STAFF004', 'Dr. Salim (Supervisor/Reviewer)', 'salim@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, NOW(), NOW()),
(6, 'STAFF005', 'Dr. Maryam (Supervisor/Reviewer)', 'maryam@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
(7, 'STU1001', 'Ali Abdullah', 'ali@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
(8, 'STU1002', 'Sara Mohammed', 'sara@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
(9, 'STU1003', 'Mohammed Al-Balushi', 'mohammed@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NOW(), NOW()),
(10, 'STU1004', 'Aisha Al-Harthy', 'aisha@utas.edu.om', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, NOW(), NOW());

-- 5. Rubric Templates
INSERT INTO `rubric_templates` (`id`, `name`, `version`, `parent_template_id`, `total_marks`, `is_locked`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Phase 1 - Proposal (Reviewer)', 1, NULL, 10.00, 1, 2, NOW(), NOW()),
(2, 'Phase 1 - Review I (Reviewer)', 1, NULL, 20.00, 1, 2, NOW(), NOW()),
(3, 'Phase 1 - Final (Reviewer)', 1, NULL, 30.00, 1, 2, NOW(), NOW());

-- 6. Criteria
-- Criteria for "Phase 1 Final Reviewer (30 marks)"
-- Notice `is_individual`. The report/design is shared (0). Presentations/Viva are individual (1).
INSERT INTO `criteria` (`id`, `title`, `description`, `max_score`, `is_individual`, `rubric_template_id`, `created_at`, `updated_at`) VALUES
(1, 'Score & Problem Identification', 'Objectives and scope are clearly mentioned.', 2.00, 0, 1, NOW(), NOW()),
(2, 'Technology & Innovation', 'Latest technologies and highly innovative ideas used.', 2.00, 0, 1, NOW(), NOW()),
(6, 'Final Outcome / Prototype', 'Literature study and Technical Analysis requirements.', 5.00, 0, 3, NOW(), NOW()),
(7, 'Report', 'Structure, Comments, Formatting, and Citations.', 5.00, 0, 3, NOW(), NOW()),
(8, 'Project Design', 'Methodologies, Low and High Level Project Design.', 10.00, 0, 3, NOW(), NOW()),

-- THESE ARE INDIVIDUAL SCORES:
(9, 'Presentation', 'Oral presentation communicated effectively within time limit.', 5.00, 1, 3, NOW(), NOW()),
(10, 'Individual Contribution and Viva', 'Demonstration of program capabilities and ability to defend answers.', 5.00, 1, 3, NOW(), NOW());

-- 7. Score Levels
-- Representing the dropdown selections examiners can pick from (mapped to criterion 10 'Viva')
INSERT INTO `score_levels` (`id`, `criterion_id`, `label`, `score_value`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 10, 'Excellent', 5.00, 'Precisely demonstrated product functionalities and defended effectively.', 1, NOW(), NOW()),
(2, 10, 'Very Good', 4.00, 'Clearly demonstrated and defended favorably.', 2, NOW(), NOW()),
(3, 10, 'Good', 3.00, 'Partially demonstrated and defended.', 3, NOW(), NOW()),
(4, 10, 'Satisfactory', 2.00, 'Minimally demonstrated and defended poorly.', 4, NOW(), NOW()),
(5, 10, 'Poor', 1.00, 'Poorly demonstrated; unable to defend.', 5, NOW(), NOW()),
(6, 10, 'Very Poor', 0.00, 'Not able to demonstrate or defend.', 6, NOW(), NOW());

-- 8. Phase Templates
INSERT INTO `phase_templates` (`id`, `name`, `total_phase_marks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'B.Tech Phase I', 100.00, 2, NOW(), NOW());

-- 9. Phase Rubric Rules
INSERT INTO `phase_rubric_rules` (`id`, `phase_template_id`, `rubric_template_id`, `evaluator_role`, `fill_order`, `max_marks`, `aggregation_method`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Reviewer', 1, 10.00, 'AVERAGE', NOW(), NOW()),
(2, 1, 2, 'Reviewer', 2, 20.00, 'AVERAGE', NOW(), NOW()),
(3, 1, 3, 'Reviewer', 3, 30.00, 'AVERAGE', NOW(), NOW());

-- 10. Semesters
INSERT INTO `semesters` (`id`, `name`, `academic_year`, `start_date`, `end_date`, `is_active`, `is_closed`, `created_at`, `updated_at`) VALUES
(1, 'Fall 2026', '2026-2027', '2026-09-01', '2027-01-15', 1, 0, NOW(), NOW());

INSERT INTO `coordinator_semester` (`id`, `user_id`, `semester_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, NOW(), NOW());

-- 11. Projects
-- Project 1 forms a Software Engineering team. Project 2 forms an IT Networking team.
INSERT INTO `projects` (`id`, `semester_id`, `course_id`, `phase_template_id`, `specialization_id`, `title`, `supervisor_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'AI-Based Assessment System', 3, 'evaluating', NOW(), NOW()),
(2, 1, 1, 1, 2, 'Smart Campus Network Infrastructure', 4, 'setup', NOW(), NOW());

-- 12. Project Group Assignments (Students & Reviewers)
INSERT INTO `project_student` (`id`, `project_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 7, NOW(), NOW()), -- Ali on Proj 1
(2, 1, 8, NOW(), NOW()), -- Sara on Proj 1
(3, 2, 9, NOW(), NOW()), -- Mohammed on Proj 2
(4, 2, 10, NOW(), NOW()); -- Aisha on Proj 2

INSERT INTO `project_reviewer` (`id`, `project_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 5, NOW(), NOW()), -- Dr. Salim reviews Proj 1
(2, 1, 6, NOW(), NOW()), -- Dr. Maryam reviews Proj 1
(3, 2, 3, NOW(), NOW()), -- Dr. Ahmed reviews Proj 2
(4, 2, 5, NOW(), NOW()); -- Dr. Salim reviews Proj 2

-- 13. Evaluations
-- Dr. Salim has finished grading Project 1 Final Review. Dr. Maryam is currently drafting it.
INSERT INTO `evaluations` (`id`, `project_id`, `rubric_template_id`, `evaluator_id`, `evaluator_role`, `status`, `general_feedback`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 5, 'Reviewer', 'submitted', 'A stellar prototype and excellent research. Well done.', NOW(), NOW()),
(2, 1, 3, 6, 'Reviewer', 'draft', 'Still calculating the presentation scores...', NOW(), NOW());

-- 14. Evaluation Scores
-- Dr. Salim (Evaluation 1) grading Project 1 Final Review
INSERT INTO `evaluation_scores` (`id`, `evaluation_id`, `criterion_id`, `score_level_id`, `student_id`, `score_awarded`, `feedback`, `created_at`, `updated_at`) VALUES

-- --- GROUP SCORES: (student_id is NULL, applies to both Ali and Sara) ---
(1, 1, 6, NULL, NULL, 4.50, 'Thorough literature study. Needs minor diagram fixes.', NOW(), NOW()),
(2, 1, 7, NULL, NULL, 5.00, 'Impeccable academic formatting.', NOW(), NOW()),
(3, 1, 8, NULL, NULL, 9.00, 'High-level ERD and design methodology was incredibly mature.', NOW(), NOW()),

-- --- INDIVIDUAL SCORES FOR ALI: (student_id = 7) ---
(4, 1, 9, NULL, 7, 4.00, 'Ali spoke very clearly and managed time well.', NOW(), NOW()),
(5, 1, 10, 1, 7, 5.00, 'Excellent viva. Ali knew the DB constraints perfectly.', NOW(), NOW()), -- Tied to "Excellent" Scale Label (ID 1)

-- --- INDIVIDUAL SCORES FOR SARA: (student_id = 8) ---
(6, 1, 9, NULL, 8, 5.00, 'Sara commanded the stage and presentation.', NOW(), NOW()),
(7, 1, 10, 2, 8, 4.00, 'Very good defense, but hesitated on routing questions.', NOW(), NOW()); -- Tied to "Very Good" Scale Label (ID 2)

-- 15. Consolidated Marks
-- (Normally this is generated automatically by a background scheduled job or event trigger once all evaluations move to 'submitted')
INSERT INTO `consolidated_marks` (`id`, `project_id`, `phase_template_id`, `student_id`, `total_calculated_score`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 7, 27.50, NOW(), NOW()), -- Ali's preliminary Phase 1 score (out of 30 reviewer points)
(2, 1, 1, 8, 27.50, NOW(), NOW()); -- Sara's preliminary Phase 1 score  (out of 30 reviewer points)

-- Note: The consolidated mark component table breaks down WHERE those points came from
INSERT INTO `consolidated_mark_components` (`id`, `consolidated_mark_id`, `source_label`, `score`, `created_at`, `updated_at`) VALUES
(1, 1, 'Phase 1 - Final (Reviewer)', 27.50, NOW(), NOW()), -- Ali
(2, 2, 'Phase 1 - Final (Reviewer)', 27.50, NOW(), NOW()); -- Sara
