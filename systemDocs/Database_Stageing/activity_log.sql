-- -------------------------------------------------------------
-- TablePlus 6.8.2(656)
--
-- https://tableplus.com/
--
-- Database: ams
-- Generation Time: 2026-05-05 21:50:42.7130
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `allowed_email_domains`;
CREATE TABLE `allowed_email_domains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `allowed_email_domains_domain_unique` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `consolidated_mark_components`;
CREATE TABLE `consolidated_mark_components` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consolidated_mark_id` bigint unsigned NOT NULL,
  `source_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consolidated_mark_components_consolidated_mark_id_foreign` (`consolidated_mark_id`),
  CONSTRAINT `consolidated_mark_components_consolidated_mark_id_foreign` FOREIGN KEY (`consolidated_mark_id`) REFERENCES `consolidated_marks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `consolidated_marks`;
CREATE TABLE `consolidated_marks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `phase_template_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `total_calculated_score` decimal(8,2) NOT NULL,
  `override_score` decimal(8,2) DEFAULT NULL,
  `override_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consolidated_marks_project_id_foreign` (`project_id`),
  KEY `consolidated_marks_phase_template_id_foreign` (`phase_template_id`),
  KEY `consolidated_marks_student_id_foreign` (`student_id`),
  CONSTRAINT `consolidated_marks_phase_template_id_foreign` FOREIGN KEY (`phase_template_id`) REFERENCES `phase_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consolidated_marks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consolidated_marks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `coordinator_semester`;
CREATE TABLE `coordinator_semester` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `semester_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coordinator_semester_user_id_foreign` (`user_id`),
  KEY `coordinator_semester_semester_id_foreign` (`semester_id`),
  CONSTRAINT `coordinator_semester_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coordinator_semester_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `criteria`;
CREATE TABLE `criteria` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `max_score` decimal(8,2) NOT NULL,
  `is_individual` tinyint(1) NOT NULL,
  `rubric_template_id` bigint unsigned NOT NULL,
  `deliverable_id` bigint unsigned DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `criteria_rubric_template_id_foreign` (`rubric_template_id`),
  KEY `criteria_deliverable_id_foreign` (`deliverable_id`),
  CONSTRAINT `criteria_deliverable_id_foreign` FOREIGN KEY (`deliverable_id`) REFERENCES `deliverables` (`id`) ON DELETE SET NULL,
  CONSTRAINT `criteria_rubric_template_id_foreign` FOREIGN KEY (`rubric_template_id`) REFERENCES `rubric_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `deliverables`;
CREATE TABLE `deliverables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rubric_template_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_marks` decimal(8,2) NOT NULL DEFAULT '0.00',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deliverables_rubric_template_id_foreign` (`rubric_template_id`),
  CONSTRAINT `deliverables_rubric_template_id_foreign` FOREIGN KEY (`rubric_template_id`) REFERENCES `rubric_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `evaluation_scores`;
CREATE TABLE `evaluation_scores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `evaluation_id` bigint unsigned NOT NULL,
  `criterion_id` bigint unsigned NOT NULL,
  `score_level_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `score_awarded` decimal(8,2) NOT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluation_scores_evaluation_id_foreign` (`evaluation_id`),
  KEY `evaluation_scores_criterion_id_foreign` (`criterion_id`),
  KEY `evaluation_scores_score_level_id_foreign` (`score_level_id`),
  KEY `evaluation_scores_student_id_foreign` (`student_id`),
  CONSTRAINT `evaluation_scores_criterion_id_foreign` FOREIGN KEY (`criterion_id`) REFERENCES `criteria` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluation_scores_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluation_scores_score_level_id_foreign` FOREIGN KEY (`score_level_id`) REFERENCES `score_levels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluation_scores_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE `evaluations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `rubric_template_id` bigint unsigned NOT NULL,
  `evaluator_id` bigint unsigned NOT NULL,
  `evaluator_role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fill_order` int DEFAULT NULL,
  `on_behalf_of_user_id` bigint unsigned DEFAULT NULL,
  `evidence_attachment_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','draft','submitted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `general_feedback` text COLLATE utf8mb4_unicode_ci,
  `unlocked_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_evaluation` (`project_id`,`rubric_template_id`,`evaluator_id`),
  KEY `evaluations_rubric_template_id_foreign` (`rubric_template_id`),
  KEY `evaluations_evaluator_id_foreign` (`evaluator_id`),
  KEY `evaluations_on_behalf_of_user_id_foreign` (`on_behalf_of_user_id`),
  KEY `evaluations_unlocked_by_foreign` (`unlocked_by`),
  CONSTRAINT `evaluations_evaluator_id_foreign` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_on_behalf_of_user_id_foreign` FOREIGN KEY (`on_behalf_of_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluations_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_rubric_template_id_foreign` FOREIGN KEY (`rubric_template_id`) REFERENCES `rubric_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_unlocked_by_foreign` FOREIGN KEY (`unlocked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `grading_scales`;
CREATE TABLE `grading_scales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `min_score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) NOT NULL,
  `letter_grade` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gpa_equivalent` decimal(3,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `phase_rubric_rules`;
CREATE TABLE `phase_rubric_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `phase_template_id` bigint unsigned NOT NULL,
  `rubric_template_id` bigint unsigned NOT NULL,
  `evaluator_role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fill_order` int NOT NULL,
  `max_marks` decimal(8,2) NOT NULL,
  `aggregation_method` enum('AVERAGE','WEIGHTED_AVERAGE','SUM','MAX') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AVERAGE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phase_rubric_rules_phase_template_id_foreign` (`phase_template_id`),
  KEY `phase_rubric_rules_rubric_template_id_foreign` (`rubric_template_id`),
  CONSTRAINT `phase_rubric_rules_phase_template_id_foreign` FOREIGN KEY (`phase_template_id`) REFERENCES `phase_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phase_rubric_rules_rubric_template_id_foreign` FOREIGN KEY (`rubric_template_id`) REFERENCES `rubric_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `phase_templates`;
CREATE TABLE `phase_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_phase_marks` decimal(8,2) NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phase_templates_created_by_foreign` (`created_by`),
  CONSTRAINT `phase_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `project_reviewer`;
CREATE TABLE `project_reviewer` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_reviewer_project_id_foreign` (`project_id`),
  KEY `project_reviewer_user_id_foreign` (`user_id`),
  CONSTRAINT `project_reviewer_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_reviewer_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `project_student`;
CREATE TABLE `project_student` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_student_project_id_foreign` (`project_id`),
  KEY `project_student_user_id_foreign` (`user_id`),
  CONSTRAINT `project_student_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_student_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `semester_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  `phase_template_id` bigint unsigned NOT NULL,
  `specialization_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supervisor_id` bigint unsigned NOT NULL,
  `previous_phase_project_id` bigint unsigned DEFAULT NULL,
  `status` enum('setup','evaluating','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'setup',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projects_semester_id_foreign` (`semester_id`),
  KEY `projects_course_id_foreign` (`course_id`),
  KEY `projects_phase_template_id_foreign` (`phase_template_id`),
  KEY `projects_specialization_id_foreign` (`specialization_id`),
  KEY `projects_supervisor_id_foreign` (`supervisor_id`),
  KEY `projects_previous_phase_project_id_foreign` (`previous_phase_project_id`),
  CONSTRAINT `projects_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_phase_template_id_foreign` FOREIGN KEY (`phase_template_id`) REFERENCES `phase_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_previous_phase_project_id_foreign` FOREIGN KEY (`previous_phase_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_specialization_id_foreign` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `rubric_folders`;
CREATE TABLE `rubric_folders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rubric_folders_parent_id_foreign` (`parent_id`),
  KEY `rubric_folders_created_by_foreign` (`created_by`),
  CONSTRAINT `rubric_folders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rubric_folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `rubric_folders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `rubric_templates`;
CREATE TABLE `rubric_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int NOT NULL DEFAULT '1',
  `parent_template_id` bigint unsigned DEFAULT NULL,
  `rubric_folder_id` bigint unsigned DEFAULT NULL,
  `total_marks` decimal(8,2) NOT NULL DEFAULT '0.00',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rubric_templates_parent_template_id_foreign` (`parent_template_id`),
  KEY `rubric_templates_created_by_foreign` (`created_by`),
  KEY `rubric_templates_rubric_folder_id_foreign` (`rubric_folder_id`),
  CONSTRAINT `rubric_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rubric_templates_parent_template_id_foreign` FOREIGN KEY (`parent_template_id`) REFERENCES `rubric_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rubric_templates_rubric_folder_id_foreign` FOREIGN KEY (`rubric_folder_id`) REFERENCES `rubric_folders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `score_levels`;
CREATE TABLE `score_levels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `criterion_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score_value` decimal(8,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `score_levels_criterion_id_foreign` (`criterion_id`),
  CONSTRAINT `score_levels_criterion_id_foreign` FOREIGN KEY (`criterion_id`) REFERENCES `criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `semesters`;
CREATE TABLE `semesters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `specializations`;
CREATE TABLE `specializations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `specializations_department_id_foreign` (`department_id`),
  CONSTRAINT `specializations_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `university_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialization_id` bigint unsigned DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '1',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `app_authentication_secret` text COLLATE utf8mb4_unicode_ci,
  `app_authentication_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_university_id_unique` (`university_id`),
  KEY `users_specialization_id_foreign` (`specialization_id`),
  CONSTRAINT `users_specialization_id_foreign` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `allowed_email_domains` (`id`, `domain`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'utas.edu.om', 1, '2026-03-11 17:09:15', '2026-03-14 19:01:51'),
(3, 'nct.edu.om', 0, '2026-03-14 19:02:41', '2026-03-14 19:03:02');

INSERT INTO `consolidated_mark_components` (`id`, `consolidated_mark_id`, `source_label`, `score`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Supervisor Evaluation', 35.00, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(2, 1, 'Reviewer Evaluation', 47.50, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL);

INSERT INTO `consolidated_marks` (`id`, `project_id`, `phase_template_id`, `student_id`, `total_calculated_score`, `override_score`, `override_reason`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 10, 6, 6, 82.50, 100.00, 'after review we have added', '2026-03-02 04:57:43', '2026-03-03 10:47:54', NULL);

INSERT INTO `coordinator_semester` (`id`, `user_id`, `semester_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 5, 7, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(4, 5, 8, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL);

INSERT INTO `courses` (`id`, `code`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'CS101', 'Introduction to Programming', '2026-03-01 19:16:16', '2026-03-03 10:49:35', '2026-03-03 10:49:35'),
(3, 'CS490', 'Final Year Project I', '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(4, 'CS491', 'Final Year Project II', '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(5, 'EN480', 'Capstone Project', '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL);

INSERT INTO `criteria` (`id`, `title`, `description`, `max_score`, `is_individual`, `rubric_template_id`, `deliverable_id`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 'Literature Review', 'Quality of literature review', 5.00, 0, 3, 1, 0, '2026-03-01 23:28:37', '2026-03-25 11:39:41', NULL),
(6, 'Presentation', 'Individual presentation skills', 5.00, 1, 3, 1, 0, '2026-03-01 23:28:37', '2026-03-25 11:39:41', NULL),
(7, 'Report Quality', 'Quality of the written report', 10.00, 0, 4, 2, 0, '2026-03-01 23:28:37', '2026-03-25 11:39:41', NULL),
(8, 'Technical Competence', 'Evaluates technical skills and knowledge application', 15.00, 1, 5, 3, 0, '2026-03-02 04:57:43', '2026-03-25 11:39:41', NULL),
(9, 'Project Management', 'Evaluates planning, organization and time management', 10.00, 0, 5, 3, 0, '2026-03-02 04:57:43', '2026-03-25 11:39:41', NULL),
(10, 'Communication Skills', 'Evaluates written and verbal communication ability', 15.00, 1, 5, 3, 0, '2026-03-02 04:57:43', '2026-03-25 11:39:41', NULL),
(11, 'Innovation & Creativity', 'Evaluates originality and creative problem solving', 20.00, 0, 6, 4, 0, '2026-03-02 04:57:43', '2026-03-25 11:39:41', NULL),
(12, 'Presentation Quality', 'Evaluates presentation delivery and materials', 20.00, 1, 6, 4, 0, '2026-03-02 04:57:43', '2026-03-25 11:39:41', NULL),
(13, 'Report Quality', 'Evaluates documentation and report writing', 20.00, 1, 6, 4, 0, '2026-03-02 04:57:43', '2026-03-25 11:39:41', NULL),
(14, 'presentation', 'fdsfjdsfjsfjsdsdfjsf', 5.00, 1, 7, 5, 0, '2026-03-03 10:54:01', '2026-03-25 11:39:41', NULL),
(15, 'Innovation & Creativity', 'Evaluates originality and creative problem solving', 20.00, 0, 8, 6, 0, '2026-03-03 10:56:29', '2026-03-25 11:39:41', NULL),
(16, 'Presentation Quality', 'Evaluates presentation delivery and materials', 20.00, 1, 8, 6, 0, '2026-03-03 10:56:29', '2026-03-25 11:39:41', NULL),
(17, 'Report Quality', 'Evaluates documentation and report writing', 20.00, 1, 8, 6, 0, '2026-03-03 10:56:29', '2026-03-25 11:39:41', NULL),
(18, 'idea clearly', 'the student talk a clearly about idea', 100.00, 1, 7, 5, 0, '2026-03-14 19:23:54', '2026-03-25 11:39:41', NULL),
(19, 'Conduct and Management', NULL, 5.00, 1, 9, 7, 0, '2026-03-14 19:26:55', '2026-03-25 11:39:41', NULL),
(20, 'Introduction, Problem statement & Project Significance', 'Understanding of existing system, problem statement, objectives, scope and significance of the proposed system', 5.00, 0, 14, 8, 0, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(21, 'Related Literature Study', 'Study of related literature and recommendations for improvisation based on recent concepts or technologies', 5.00, 0, 14, 8, 1, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(22, 'Presentation & Viva', 'Quality of oral presentation and ability to defend the project within the time limit', 5.00, 0, 14, 9, 0, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(23, 'Individual Contribution', 'Completion of individually assigned tasks', 5.00, 1, 14, 9, 1, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL);

INSERT INTO `deliverables` (`id`, `rubric_template_id`, `title`, `max_marks`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 'General', 10.00, 0, '2026-03-25 11:39:41', '2026-03-25 11:39:41', NULL),
(2, 4, 'General', 10.00, 0, '2026-03-25 11:39:41', '2026-03-25 11:39:41', NULL),
(3, 5, 'General', 40.00, 0, '2026-03-25 11:39:41', '2026-03-25 11:39:41', NULL),
(4, 6, 'General', 60.00, 0, '2026-03-25 11:39:41', '2026-03-25 11:39:41', NULL),
(5, 7, 'General', 105.00, 0, '2026-03-25 11:39:41', '2026-03-25 11:39:41', NULL),
(6, 8, 'General', 60.00, 0, '2026-03-25 11:39:41', '2026-03-25 11:39:41', NULL),
(7, 9, 'General', 5.00, 0, '2026-03-25 11:39:41', '2026-03-25 11:39:41', NULL),
(8, 14, 'Project Analysis', 10.00, 1, '2026-03-25 16:09:45', '2026-03-25 17:25:09', NULL),
(9, 14, 'Presentation', 10.00, 2, '2026-03-25 16:09:45', '2026-03-25 17:25:09', NULL);

INSERT INTO `departments` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Computer Science', '2026-03-01 19:13:24', '2026-03-01 19:13:24', NULL),
(3, 'Engineering', '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(4, 'finanace', '2026-03-14 18:53:04', '2026-03-14 18:53:04', NULL);

INSERT INTO `evaluation_scores` (`id`, `evaluation_id`, `criterion_id`, `score_level_id`, `student_id`, `score_awarded`, `feedback`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 5, 7, NULL, 5.00, NULL, '2026-03-01 23:47:27', '2026-03-01 23:47:27', NULL),
(2, 1, 6, 11, 6, 4.00, NULL, '2026-03-01 23:47:27', '2026-03-01 23:47:27', NULL),
(3, 1, 6, 10, 9, 5.00, NULL, '2026-03-01 23:47:27', '2026-03-01 23:47:27', NULL),
(4, 2, 7, 12, NULL, 10.00, 'Good report', '2026-03-02 00:02:00', '2026-03-02 00:02:45', '2026-03-02 00:02:45'),
(5, 2, 7, 13, NULL, 8.00, NULL, '2026-03-02 00:08:34', '2026-03-02 00:08:34', NULL),
(6, 3, 8, 15, 6, 14.00, 'Strong technical skills', '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(7, 3, 10, 24, 6, 11.00, 'Good communication', '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(8, 3, 9, 19, NULL, 9.00, 'Great project management', '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL);

INSERT INTO `evaluations` (`id`, `project_id`, `rubric_template_id`, `evaluator_id`, `evaluator_role`, `fill_order`, `on_behalf_of_user_id`, `evidence_attachment_path`, `status`, `general_feedback`, `unlocked_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 7, 3, 8, 'Supervisor', 1, 1, NULL, 'submitted', 'Good overall performance by the team. The project demonstrates solid understanding of the subject matter.', 1, '2026-03-01 23:35:39', '2026-03-01 23:56:12', NULL),
(2, 7, 4, 7, 'Reviewer', 2, NULL, NULL, 'draft', 'Excellent report quality with thorough literature review.', NULL, '2026-03-01 23:35:39', '2026-03-02 00:08:34', NULL),
(3, 8, 5, 15, 'Supervisor', 1, NULL, NULL, 'submitted', 'Excellent work on the technical implementation.', NULL, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(4, 8, 6, 16, 'Reviewer', 2, NULL, NULL, 'pending', NULL, NULL, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(5, 10, 5, 15, 'Supervisor', 1, NULL, NULL, 'submitted', 'Well-structured project with thorough documentation.', NULL, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL);

INSERT INTO `grading_scales` (`id`, `min_score`, `max_score`, `letter_grade`, `gpa_equivalent`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 80.00, 100.00, 'A', 4.00, '2026-03-01 19:16:47', '2026-03-14 19:14:09', '2026-03-14 19:14:09'),
(5, 60.00, 79.00, 'B', 3.00, '2026-03-01 19:17:08', '2026-03-14 19:16:13', '2026-03-14 19:16:13'),
(6, 90.00, 100.00, 'A+', 4.00, '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(7, 80.00, 84.99, 'B+', 3.50, '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(8, 70.00, 74.99, 'C+', 2.50, '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(9, 65.00, 69.99, 'C', 2.00, '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(10, 50.00, 64.99, 'D', 1.00, '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(11, 0.00, 49.99, 'F', 0.00, '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL);

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_03_01_000001_create_departments_table', 1),
(5, '2025_03_01_000002_create_specializations_table', 1),
(6, '2025_03_01_000003_modify_users_table_add_ams_columns', 1),
(7, '2025_03_01_000004_create_courses_table', 1),
(8, '2025_03_01_000005_create_grading_scales_table', 1),
(9, '2025_03_01_000006_create_rubric_templates_table', 1),
(10, '2025_03_01_000007_create_criteria_table', 1),
(11, '2025_03_01_000008_create_score_levels_table', 1),
(12, '2025_03_01_000009_create_phase_templates_table', 1),
(13, '2025_03_01_000010_create_phase_rubric_rules_table', 1),
(14, '2025_03_01_000011_create_semesters_table', 1),
(15, '2025_03_01_000012_create_coordinator_semester_table', 1),
(16, '2025_03_01_000013_create_projects_table', 1),
(17, '2025_03_01_000014_create_project_student_table', 1),
(18, '2025_03_01_000015_create_project_reviewer_table', 1),
(19, '2025_03_01_000016_create_evaluations_table', 1),
(20, '2025_03_01_000017_create_evaluation_scores_table', 1),
(21, '2025_03_01_000018_create_consolidated_marks_table', 1),
(22, '2025_03_01_000019_create_consolidated_mark_components_table', 1),
(23, '2026_03_01_114216_create_permission_tables', 1),
(24, '2026_03_01_114525_add_two_factor_columns_to_users_table', 1),
(25, '2026_03_01_114550_create_activity_log_table', 1),
(26, '2026_03_01_114551_add_event_column_to_activity_log_table', 1),
(27, '2026_03_01_114552_add_batch_uuid_column_to_activity_log_table', 1),
(28, '2026_03_01_233703_add_fill_order_to_evaluations_table', 2),
(29, '2026_03_11_170158_create_allowed_email_domains_table', 3),
(30, '2026_03_25_000001_create_deliverables_table', 4),
(31, '2026_03_25_000002_migrate_existing_criteria_to_deliverables', 4),
(32, '2026_03_25_000003_create_rubric_folders_table', 5),
(33, '2026_03_25_000004_drop_percentage_range_from_score_levels', 5),
(34, '2026_03_27_000001_add_is_approved_to_users_table', 6),
(35, '2026_03_28_000001_merge_supervisor_reviewer_roles', 7);

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 18),
(2, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 12),
(2, 'App\\Models\\User', 24),
(2, 'App\\Models\\User', 25),
(2, 'App\\Models\\User', 26),
(5, 'App\\Models\\User', 6),
(5, 'App\\Models\\User', 9),
(5, 'App\\Models\\User', 10),
(5, 'App\\Models\\User', 13),
(5, 'App\\Models\\User', 14),
(5, 'App\\Models\\User', 19),
(5, 'App\\Models\\User', 20),
(5, 'App\\Models\\User', 23),
(6, 'App\\Models\\User', 7),
(6, 'App\\Models\\User', 8),
(6, 'App\\Models\\User', 11),
(6, 'App\\Models\\User', 15),
(6, 'App\\Models\\User', 16),
(6, 'App\\Models\\User', 21),
(6, 'App\\Models\\User', 22);

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('26s2020@utas.edu.om', '$2y$12$542tPdsV7fBW.dfb8DSlmeYky6cIfUE0Ri3s6Qc7G2OZPrbiPH1ey', '2026-03-27 11:09:57');

INSERT INTO `phase_rubric_rules` (`id`, `phase_template_id`, `rubric_template_id`, `evaluator_role`, `fill_order`, `max_marks`, `aggregation_method`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 4, 3, 'Supervisor', 1, 40.00, 'AVERAGE', '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(4, 4, 4, 'Reviewer', 2, 60.00, 'AVERAGE', '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(5, 5, 5, 'Supervisor', 1, 40.00, 'AVERAGE', '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(6, 5, 6, 'Reviewer', 2, 60.00, 'AVERAGE', '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(7, 4, 3, 'Supervisor', 1, 10.00, 'SUM', '2026-03-03 10:59:05', '2026-03-03 10:59:05', NULL),
(8, 7, 3, 'Supervisor', 1, 100.00, 'SUM', '2026-03-25 18:45:45', '2026-03-25 18:45:45', NULL);

INSERT INTO `phase_templates` (`id`, `name`, `total_phase_marks`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 'B.Tech Phase I', 100.00, 1, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(5, 'FYP Phase 1 - Proposal & Design', 100.00, 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(6, 'FYP Phase 2 - Implementation & Testing', 100.00, 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(7, 'test', 100.00, 1, '2026-03-25 18:44:40', '2026-03-25 18:44:40', NULL);

INSERT INTO `project_reviewer` (`id`, `project_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 7, 7, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(5, 8, 16, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(6, 9, 16, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(7, 10, 16, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL);

INSERT INTO `project_student` (`id`, `project_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 7, 6, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(8, 7, 9, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(9, 8, 6, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(10, 8, 9, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(11, 10, 6, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(12, 7, 19, '2026-04-24 15:30:45', '2026-04-24 15:30:45', NULL),
(13, 7, 13, '2026-04-24 15:31:22', '2026-04-24 15:31:22', NULL);

INSERT INTO `projects` (`id`, `semester_id`, `course_id`, `phase_template_id`, `specialization_id`, `title`, `supervisor_id`, `previous_phase_project_id`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 6, 3, 4, 2, 'AI-Based Assessment System', 8, NULL, 'evaluating', '2026-03-01 23:28:37', '2026-04-24 15:31:13', NULL),
(8, 7, 3, 5, 2, 'Smart Campus Navigation System', 15, NULL, 'evaluating', '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(9, 7, 3, 5, 4, 'AI-Powered Student Attendance Tracker', 15, NULL, 'setup', '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(10, 8, 4, 6, 2, 'Blockchain-Based Certificate Verification', 15, NULL, 'completed', '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL);

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', '2026-03-01 18:44:17', '2026-03-01 18:44:17'),
(2, 'Coordinator', 'web', '2026-03-01 18:44:17', '2026-03-01 18:44:17'),
(5, 'Student', 'web', '2026-03-01 18:44:17', '2026-03-01 18:44:17'),
(6, 'Reviewer/Supervisor', 'web', '2026-03-27 22:58:32', '2026-03-27 22:58:32');

INSERT INTO `rubric_templates` (`id`, `name`, `version`, `parent_template_id`, `rubric_folder_id`, `total_marks`, `is_locked`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 'Supervisor Assessment Rubric', 1, NULL, NULL, 10.00, 1, 1, '2026-03-01 23:28:37', '2026-03-01 23:35:39', NULL),
(4, 'Reviewer Assessment Rubric', 1, NULL, NULL, 10.00, 1, 1, '2026-03-01 23:28:37', '2026-03-01 23:35:39', NULL),
(5, 'Supervisor Evaluation Rubric', 1, NULL, NULL, 40.00, 1, 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(6, 'Reviewer Evaluation Rubric', 1, NULL, NULL, 60.00, 1, 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(7, 'test', 1, NULL, NULL, 105.00, 0, 1, '2026-03-03 10:52:20', '2026-03-14 19:23:54', NULL),
(8, 'Reviewer Evaluation Rubric', 2, 6, NULL, 60.00, 0, 1, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(9, 'RUBRICS FOR FINAL REVIEW – PHASE - II', 1, NULL, NULL, 5.00, 0, 1, '2026-03-14 19:25:32', '2026-03-14 19:26:55', NULL),
(10, 'Phase 1', 1, NULL, NULL, 0.00, 0, 1, '2026-03-25 12:21:33', '2026-03-25 12:36:52', '2026-03-25 12:36:52'),
(14, 'testuploud', 1, NULL, NULL, 20.00, 0, 1, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL);

INSERT INTO `score_levels` (`id`, `criterion_id`, `label`, `score_value`, `description`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 5, 'Excellent', 5.00, NULL, 1, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(8, 5, 'Good', 4.00, NULL, 2, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(9, 5, 'Average', 3.00, NULL, 3, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(10, 6, 'Excellent', 5.00, NULL, 1, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(11, 6, 'Good', 4.00, NULL, 2, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(12, 7, 'Excellent', 10.00, NULL, 1, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(13, 7, 'Good', 8.00, NULL, 2, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(14, 7, 'Average', 6.00, NULL, 3, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(15, 8, 'Excellent', 15.00, 'Outstanding performance', 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(16, 8, 'Good', 11.25, 'Above average performance', 2, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(17, 8, 'Satisfactory', 7.50, 'Meets expectations', 3, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(18, 8, 'Poor', 3.75, 'Below expectations', 4, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(19, 9, 'Excellent', 10.00, 'Outstanding performance', 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(20, 9, 'Good', 7.50, 'Above average performance', 2, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(21, 9, 'Satisfactory', 5.00, 'Meets expectations', 3, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(22, 9, 'Poor', 2.50, 'Below expectations', 4, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(23, 10, 'Excellent', 15.00, 'Outstanding performance', 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(24, 10, 'Good', 11.25, 'Above average performance', 2, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(25, 10, 'Satisfactory', 7.50, 'Meets expectations', 3, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(26, 10, 'Poor', 3.75, 'Below expectations', 4, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(27, 11, 'Excellent', 20.00, 'Outstanding performance', 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(28, 11, 'Good', 15.00, 'Above average performance', 2, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(29, 11, 'Satisfactory', 10.00, 'Meets expectations', 3, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(30, 11, 'Poor', 5.00, 'Below expectations', 4, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(31, 12, 'Excellent', 20.00, 'Outstanding performance', 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(32, 12, 'Good', 15.00, 'Above average performance', 2, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(33, 12, 'Satisfactory', 10.00, 'Meets expectations', 3, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(34, 12, 'Poor', 5.00, 'Below expectations', 4, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(35, 13, 'Excellent', 20.00, 'Outstanding performance', 1, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(36, 13, 'Good', 15.00, 'Above average performance', 2, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(37, 13, 'Satisfactory', 10.00, 'Meets expectations', 3, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(38, 13, 'Poor', 5.00, 'Below expectations', 4, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(39, 14, 'exelernt ', 6.00, 'jdfkdjfsld;flkjsdfkjskljfksodjflksdjfksl;djf;kjsdslkfjdsfsdf', 1, '2026-03-03 10:54:01', '2026-03-03 10:54:01', NULL),
(40, 15, 'Excellent', 20.00, 'Outstanding performance', 1, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(41, 15, 'Good', 15.00, 'Above average performance', 2, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(42, 15, 'Satisfactory', 10.00, 'Meets expectations', 3, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(43, 15, 'Poor', 5.00, 'Below expectations', 4, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(44, 16, 'Excellent', 20.00, 'Outstanding performance', 1, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(45, 16, 'Good', 15.00, 'Above average performance', 2, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(46, 16, 'Satisfactory', 10.00, 'Meets expectations', 3, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(47, 16, 'Poor', 5.00, 'Below expectations', 4, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(48, 17, 'Excellent', 20.00, 'Outstanding performance', 1, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(49, 17, 'Good', 15.00, 'Above average performance', 2, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(50, 17, 'Satisfactory', 10.00, 'Meets expectations', 3, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(51, 17, 'Poor', 5.00, 'Below expectations', 4, '2026-03-03 10:56:29', '2026-03-03 10:56:29', NULL),
(52, 18, 'Excellent', 100.00, 'Regularly Attended and discussed the task as per the work plan.', 1, '2026-03-14 19:23:54', '2026-03-14 19:23:54', NULL),
(53, 18, 'Very Good', 80.00, 'Regularly attended and discussed the task with a slight deviation from the work plan.', 2, '2026-03-14 19:23:54', '2026-03-14 19:23:54', NULL),
(54, 20, 'Excellent', 5.00, 'Understanding the existing system, problem statement, objectives, scope, and significance of the proposed system are excellent.', 0, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(55, 20, 'Very Good', 4.00, 'Understanding the existing system, problem statement, objectives, scope, and significance of the proposed system are very good.', 1, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(56, 20, 'Good', 3.00, 'Understanding the existing system, problem statement, objectives, scope, and significance of the proposed system are good.', 2, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(57, 20, 'Satisfactory', 2.00, 'Understanding the existing system, problem statement, objectives, scope, and significance of the proposed system are satisfactory.', 3, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(58, 20, 'Poor', 1.00, 'Understanding the existing system, problem statement, objectives, scope, and significance of the proposed system are poor.', 4, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(59, 20, 'Very Poor', 0.25, 'Understanding the existing system, problem statement, objectives, scope, and significance of the proposed system are very poor.', 5, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(60, 21, 'Excellent', 5.00, 'The project very clearly studied the related literature and recommends improvisation as required by the recent concepts or technologies.', 0, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(61, 21, 'Very Good', 4.00, 'The project clearly studied the related literature and recommends improvisation as required by the recent concepts or technologies.', 1, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(62, 21, 'Good', 3.00, 'The project mostly studied the related literature and recommends improvisation as required by the recent concepts or technologies.', 2, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(63, 21, 'Satisfactory', 2.00, 'The project partially studied the related literature and recommends improvisation as required by the recent concepts or technologies.', 3, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(64, 21, 'Poor', 1.00, 'The project minimally studied the related literature and recommends improvisation as required by the recent concepts or technologies.', 4, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(65, 21, 'Very Poor', 0.25, 'The project did not study the related literature and recommends improvisation as required by the recent concepts or technologies.', 5, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(66, 22, 'Excellent', 5.00, 'The oral presentation effectively communicated the overview of the project process within the time limit and very well defended.', 0, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(67, 22, 'Very Good', 4.00, 'The oral presentation clearly communicated the overview of the project process within the time limit and defended favorably.', 1, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(68, 22, 'Good', 3.00, 'The oral presentation partially communicated the overview of the project process within the time limit and defended partially.', 2, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(69, 22, 'Satisfactory', 2.00, 'The oral presentation minimally communicated the overview of the project process within the time limit and defended minimally.', 3, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(70, 22, 'Poor', 1.00, 'The oral presentation poorly communicated the overview of the project process within the time limit and defended poorly.', 4, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(71, 22, 'Very Poor', 0.25, 'The oral presentation did not communicate the overview of the project process within the time limit and not able to defend.', 5, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(72, 23, 'Excellent', 5.00, 'Effectively completed the assigned task.', 0, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(73, 23, 'Very Good', 4.00, 'Most of the assigned tasks were completed.', 1, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(74, 23, 'Good', 3.00, 'Partially completed the assigned task.', 2, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(75, 23, 'Satisfactory', 2.00, 'Minimally completed the assigned task.', 3, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(76, 23, 'Poor', 1.00, 'Poorly completed the assigned task.', 4, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL),
(77, 23, 'Very Poor', 0.25, 'Not completed the assigned task.', 5, '2026-03-25 16:09:45', '2026-03-25 16:09:45', NULL);

INSERT INTO `semesters` (`id`, `name`, `academic_year`, `start_date`, `end_date`, `is_active`, `is_closed`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 'Fall 2026', '2025-2026', '2026-09-01', '2026-12-31', 1, 0, '2026-03-01 23:28:37', '2026-03-01 23:28:37', NULL),
(7, 'Semester 1', '2025/2026', '2025-09-01', '2026-01-15', 1, 0, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(8, 'Semester 2', '2025/2026', '2026-02-01', '2026-06-15', 1, 0, '2026-03-02 04:57:43', '2026-03-02 04:57:43', NULL),
(9, 'Spring 2027', '2026-2027', NULL, NULL, 1, 0, '2026-04-30 21:46:42', '2026-04-30 21:46:42', NULL),
(10, 'fall2', '2026-2027', '2026-08-17', '2026-12-28', 1, 0, '2026-04-30 21:49:14', '2026-04-30 21:49:14', NULL);

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1IbakuOeig1FXgs8zYk8HBUdtij01LWdzY1okMs3', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.4 Safari/605.1.15', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiNWlGYzVTNmZ5M1BDZTduaGVlSVFIYXJIWEIxWGxMeEdlQkVkSmplRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vYW1zLnRlc3QvYWRtaW4iO3M6NToicm91dGUiO3M6MzA6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmRhc2hib2FyZCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImM5NDgxYWU3YjNjNjhjNzJlNDViNzg2YTg2Y2Y0MWYwMjFiYjkyNzUzMTRmNDEwYmQyMGI3OTIzZTBkY2RlZGUiO3M6NjoidGFibGVzIjthOjU6e3M6NDA6ImVmZjI1OWVmYjFhN2FlNjJkODM1NTZlZDgxNWYyYWRhX2NvbHVtbnMiO2E6Njp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6InRpdGxlIjtzOjU6ImxhYmVsIjtzOjU6IlRpdGxlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoic2VtZXN0ZXIubmFtZSI7czo1OiJsYWJlbCI7czo4OiJTZW1lc3RlciI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6InN1cGVydmlzb3IubmFtZSI7czo1OiJsYWJlbCI7czoxMDoiU3VwZXJ2aXNvciI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6InN0dWRlbnRzX2NvdW50IjtzOjU6ImxhYmVsIjtzOjg6IlN0dWRlbnRzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJzdGF0dXMiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyNzoic3VibWl0dGVkX2V2YWx1YXRpb25zX2NvdW50IjtzOjU6ImxhYmVsIjtzOjIzOiJTdWJtaXR0ZWQgLyBUb3RhbCBFdmFscyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiNjQwYjYxZWE5YTY1NGU0YWU4MTBmYmY2ODZhMDA2NWJfY29sdW1ucyI7YTo1OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTM6InVuaXZlcnNpdHlfaWQiO3M6NToibGFiZWwiO3M6MTM6IlVuaXZlcnNpdHkgaWQiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6Im5hbWUiO3M6NToibGFiZWwiO3M6NDoiTmFtZSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NToiZW1haWwiO3M6NToibGFiZWwiO3M6NToiRW1haWwiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJyb2xlcy5uYW1lIjtzOjU6ImxhYmVsIjtzOjU6IlJvbGVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxOToic3BlY2lhbGl6YXRpb24ubmFtZSI7czo1OiJsYWJlbCI7czoxNDoiU3BlY2lhbGl6YXRpb24iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6IjY2MmFiMzRlNjQ4N2U0MTI4MzBkZjIyYWMyZjAzZTk0X2NvbHVtbnMiO2E6NDp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjExOiJkZXNjcmlwdGlvbiI7czo1OiJsYWJlbCI7czo2OiJBY3Rpb24iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJzdWJqZWN0X3R5cGUiO3M6NToibGFiZWwiO3M6NjoiRW50aXR5IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToiY2F1c2VyLm5hbWUiO3M6NToibGFiZWwiO3M6NDoiVXNlciI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6NDoiV2hlbiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiOTExZjYzMjIxNDNkY2Y0NDYzMWU5ZDM5YzhkM2RhYmVfY29sdW1ucyI7YTo1OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NDoibmFtZSI7czo1OiJsYWJlbCI7czo0OiJOYW1lIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNzoidG90YWxfcGhhc2VfbWFya3MiO3M6NToibGFiZWwiO3M6MTE6IlRvdGFsIE1hcmtzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoiY3JlYXRvci5uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJDcmVhdGVkIEJ5IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyNDoicGhhc2VfcnVicmljX3J1bGVzX2NvdW50IjtzOjU6ImxhYmVsIjtzOjEyOiJSdWJyaWMgUnVsZXMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjEwOiJDcmVhdGVkIGF0IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MDtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MTtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO2I6MTt9fXM6NDA6IjQ2Y2RmN2E5MDZmMDIwM2QyNzllMmNlYzEwNzRmZTQ1X2NvbHVtbnMiO2E6NTp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE5OiJydWJyaWNUZW1wbGF0ZS5uYW1lIjtzOjU6ImxhYmVsIjtzOjE1OiJSdWJyaWMgVGVtcGxhdGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE0OiJldmFsdWF0b3Jfcm9sZSI7czo1OiJsYWJlbCI7czoxNDoiRXZhbHVhdG9yIFJvbGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJmaWxsX29yZGVyIjtzOjU6ImxhYmVsIjtzOjEwOiJGaWxsIE9yZGVyIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo5OiJtYXhfbWFya3MiO3M6NToibGFiZWwiO3M6OToiTWF4IE1hcmtzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxODoiYWdncmVnYXRpb25fbWV0aG9kIjtzOjU6ImxhYmVsIjtzOjExOiJBZ2dyZWdhdGlvbiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319fXM6ODoiZmlsYW1lbnQiO2E6MDp7fX0=', 1777585873),
('7eNiroICDXB5ENQbFmeJiBlvOXIFVRIMoPrdvLQp', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiSnJBVVJVd3ZVSU9Eb0FJcmFJbUQ0TExjd0l4UW52RHlUZUQxSHNGbyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ0OiJodHRwczovL2Ftcy50ZXN0L2FkbWluL3NlbWVzdGVyLXNldHVwLXdpemFyZCI7czo1OiJyb3V0ZSI7czo0MjoiZmlsYW1lbnQuYWRtaW4ucGFnZXMuc2VtZXN0ZXItc2V0dXAtd2l6YXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiYzk0ODFhZTdiM2M2OGM3MmU0NWI3ODZhODZjZjQxZjAyMWJiOTI3NTMxNGY0MTBiZDIwYjc5MjNlMGRjZGVkZSI7fQ==', 1777584182),
('BYoZtnXxCW7WqjIGdAwkJ6AOpoglrhMZorYBToyk', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.4 Safari/605.1.15', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSlI1clNKTTI2UUJldVF4cDdLNnE4Q1M3WWxvd2RkNFY4NURjUGJHVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTY6Imh0dHBzOi8vYW1zLnRlc3QiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1777768818),
('p4MeAa5mJwamp03WFKFIivCKP1TECMVbbei8R1V0', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.4 Safari/605.1.15', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoibnZyVDc2c3BOVDVNREZ0YTVwdmVaMXNTUEtIbW5XRE1ocVJ2ck5aWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vYW1zLnRlc3QvYWRtaW4iO3M6NToicm91dGUiO3M6MzA6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImM5NDgxYWU3YjNjNjhjNzJlNDViNzg2YTg2Y2Y0MWYwMjFiYjkyNzUzMTRmNDEwYmQyMGI3OTIzZTBkY2RlZGUiO3M6NjoidGFibGVzIjthOjE6e3M6NDA6IjY2MmFiMzRlNjQ4N2U0MTI4MzBkZjIyYWMyZjAzZTk0X2NvbHVtbnMiO2E6NDp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjExOiJkZXNjcmlwdGlvbiI7czo1OiJsYWJlbCI7czo2OiJBY3Rpb24iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJzdWJqZWN0X3R5cGUiO3M6NToibGFiZWwiO3M6NjoiRW50aXR5IjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToiY2F1c2VyLm5hbWUiO3M6NToibGFiZWwiO3M6NDoiVXNlciI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6ImNyZWF0ZWRfYXQiO3M6NToibGFiZWwiO3M6NDoiV2hlbiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319fX0=', 1777753546),
('pZr10cTvL9vXx7792RO61R3JlZnBVyiuYwxehmIG', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiODY3RHgxSENqaWczWUc4S0g2dXNsNXNVT3NtSDFlbzRibTcxc3dYRiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ0OiJodHRwczovL2Ftcy50ZXN0L2FkbWluL3NlbWVzdGVyLXNldHVwLXdpemFyZCI7czo1OiJyb3V0ZSI7czo0MjoiZmlsYW1lbnQuYWRtaW4ucGFnZXMuc2VtZXN0ZXItc2V0dXAtd2l6YXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiYzk0ODFhZTdiM2M2OGM3MmU0NWI3ODZhODZjZjQxZjAyMWJiOTI3NTMxNGY0MTBiZDIwYjc5MjNlMGRjZGVkZSI7czo2OiJ0YWJsZXMiO2E6Mjp7czo0MDoiZWZmMjU5ZWZiMWE3YWU2MmQ4MzU1NmVkODE1ZjJhZGFfY29sdW1ucyI7YTo2OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NToidGl0bGUiO3M6NToibGFiZWwiO3M6NToiVGl0bGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJzZW1lc3Rlci5uYW1lIjtzOjU6ImxhYmVsIjtzOjg6IlNlbWVzdGVyIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToic3VwZXJ2aXNvci5uYW1lIjtzOjU6ImxhYmVsIjtzOjEwOiJTdXBlcnZpc29yIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoic3R1ZGVudHNfY291bnQiO3M6NToibGFiZWwiO3M6ODoiU3R1ZGVudHMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6InN0YXR1cyI7czo1OiJsYWJlbCI7czo2OiJTdGF0dXMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjI3OiJzdWJtaXR0ZWRfZXZhbHVhdGlvbnNfY291bnQiO3M6NToibGFiZWwiO3M6MjM6IlN1Ym1pdHRlZCAvIFRvdGFsIEV2YWxzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiI2NjJhYjM0ZTY0ODdlNDEyODMwZGYyMmFjMmYwM2U5NF9jb2x1bW5zIjthOjQ6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMToiZGVzY3JpcHRpb24iO3M6NToibGFiZWwiO3M6NjoiQWN0aW9uIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoic3ViamVjdF90eXBlIjtzOjU6ImxhYmVsIjtzOjY6IkVudGl0eSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTE6ImNhdXNlci5uYW1lIjtzOjU6ImxhYmVsIjtzOjQ6IlVzZXIiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjQ6IldoZW4iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fX19', 1777583909),
('qdxPMlRxZvMIpdYK8BtA1dRNVLsBJQX0TpbzE1Ub', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV0NzUnJ2aFhTbzZIc1haYkI3WVI4V1lnV2RRa0NZMzRqSUx5QWlDNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vYW1zLnRlc3QvP2hlcmQ9cHJldmlldyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1777583163),
('sE2IIimmnh6klazwWz6aYsIBXJPdla5TIENJkR7W', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.4 Safari/605.1.15', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiclp2cGR6MGhEZGkybGt1aHNYdmdCQ2thYktTeUNxS0E4NlFzcHl1NiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vYW1zLnRlc3QvYWRtaW4iO3M6NToicm91dGUiO3M6MzA6ImZpbGFtZW50LmFkbWluLnBhZ2VzLmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImM5NDgxYWU3YjNjNjhjNzJlNDViNzg2YTg2Y2Y0MWYwMjFiYjkyNzUzMTRmNDEwYmQyMGI3OTIzZTBkY2RlZGUiO3M6NjoidGFibGVzIjthOjE6e3M6NDA6ImVmZjI1OWVmYjFhN2FlNjJkODM1NTZlZDgxNWYyYWRhX2NvbHVtbnMiO2E6Njp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6InRpdGxlIjtzOjU6ImxhYmVsIjtzOjU6IlRpdGxlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoic2VtZXN0ZXIubmFtZSI7czo1OiJsYWJlbCI7czo4OiJTZW1lc3RlciI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6InN1cGVydmlzb3IubmFtZSI7czo1OiJsYWJlbCI7czoxMDoiU3VwZXJ2aXNvciI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6InN0dWRlbnRzX2NvdW50IjtzOjU6ImxhYmVsIjtzOjg6IlN0dWRlbnRzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJzdGF0dXMiO3M6NToibGFiZWwiO3M6NjoiU3RhdHVzIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoyNzoic3VibWl0dGVkX2V2YWx1YXRpb25zX2NvdW50IjtzOjU6ImxhYmVsIjtzOjIzOiJTdWJtaXR0ZWQgLyBUb3RhbCBFdmFscyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319fX0=', 1777830851),
('XNbf27QfFd9g7CEakFIbgWKKM4jgupGkhCids79x', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiQWhmYkowMnhDSkpsSFRTZ21GcGhzT1VlcUZrVW5XZlhNUUx2bmVzZSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ0OiJodHRwczovL2Ftcy50ZXN0L2FkbWluL3NlbWVzdGVyLXNldHVwLXdpemFyZCI7czo1OiJyb3V0ZSI7czo0MjoiZmlsYW1lbnQuYWRtaW4ucGFnZXMuc2VtZXN0ZXItc2V0dXAtd2l6YXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJmaWxhbWVudCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiYzk0ODFhZTdiM2M2OGM3MmU0NWI3ODZhODZjZjQxZjAyMWJiOTI3NTMxNGY0MTBiZDIwYjc5MjNlMGRjZGVkZSI7fQ==', 1777585609);

INSERT INTO `specializations` (`id`, `department_id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 2, 'Software Engineering', '2026-03-01 19:13:43', '2026-03-01 19:13:43', NULL),
(3, 2, 'Information Systems', '2026-03-01 19:15:45', '2026-03-01 19:15:57', '2026-03-01 19:15:57'),
(4, 2, 'Artificial Intelligence', '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(5, 3, 'Mechanical Engineering', '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(6, 3, 'eng test', '2026-03-08 17:57:35', '2026-03-08 17:57:35', NULL),
(7, 4, 'Marketing', '2026-03-14 18:54:46', '2026-03-14 18:54:46', NULL),
(8, 4, 'Accounting', '2026-03-14 18:55:00', '2026-03-14 18:55:00', NULL);

INSERT INTO `users` (`id`, `university_id`, `name`, `email`, `specialization_id`, `email_verified_at`, `is_approved`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `app_authentication_secret`, `app_authentication_recovery_codes`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'ADM001', 'Super Admin', 'admin@ams.test', NULL, '2026-03-02 05:15:16', 1, '$2y$12$oIbiMaowQlLNEP4Rd3SMruPrxPkDow4AZTvW6C1BnkTIVgwP5CUwC', NULL, NULL, NULL, 'eyJpdiI6IkZwcXFRem11WVBaMGNnTDVNTjVPSlE9PSIsInZhbHVlIjoiUnlxaUtoRkRQYUNBMmo0M1ZqVURzVlFKcHhXUGhPL3JpWWF6ZWYyaFNEST0iLCJtYWMiOiIwYzBiMGFiNTc3Y2NiODA0OWRjOGZlNGNjZGZhYzU2NDJlMGZlYjVlNTI4NGM0MWE4MGZkM2VlN2MwNDVjMzRlIiwidGFnIjoiIn0=', 'eyJpdiI6IkdwbDBJd1BKMGxSNWoxenZiVXVsQ1E9PSIsInZhbHVlIjoic0c2UlZPVnlTM1ZzYk5oUkhONHEvT0RxaU1aVWpRRDFocUU1MFE1RWx1d0N5V2RxbDQ1ZkYxZ3h2cjdvazgzelRiZzQrU0tiaU5FUDRldXJWelFTdlhDMHRRd3lyN01wMzFZRy9YV2dYTlQ1TDZiUGZlVnBhTFV1TmorVDN6V1NwOTk0b0tybFR3SkJjbnBTeWxMNnY5emExSHc2L2pDRWozNUg3MGt6bENVTFlzZXBJajVoWVY0dkNKaWtLYm9PS1lkMW9Hc25BMUNWOWtVZ2Q0VTNvR0pCTlg3a2ZHZXlMQ1JQakQzY1VKb3ZsclovSG90WmZ5bXU1d283cmZBMkFMWldwcDI2TlJkVjhSaUxQclF5ZmVaSytJMlB3WDA4TkpkQ0VmZ3VJbEt2QkNHR2VFLzRBc1ZDWm0rK0Q2eEJ4d3g3TFdLb3dvSTY0TFZ0Nlh6QmRhL08xRGFxUWVNeGYrUkN3V1EybFkyVzc5QnVmUnBZVW5iRXBmVUZCd1hLV0lrQUpSVDFSZGhiWmQ0QmVuUnhJOTJ2aWwxT2lXeE1WQTNPbHh1ZElPUzZqWkF4UVRyc3llVmJ5SmhzN2pZVVVxL0pIOVN0K2p1dUFySlNJUjQzbk9kNThWMmQ0N2MydlVVN21NaXRBMUxvdjk5WHVLdEFvQVVwNnpFSnQxSTJmSnVxY2dJR2l4dXE0SHF6LzhPZFE3dStreElUVlAzRDlydks4OFhzaG85WURseDZVMkZVYlVlWkhZVTF4WnZMSUZaL05FY2ZZWlRwN1RGZkNsYTVWVG95US8xbWZ2WHdnTjVQNUV0TTd4QWlGOU1uTksyaGtOMTV5M1lrZ0p1R1JOeDhDUy9VSUU4Y2FPaW90MEl3V1FqSEtYVVBDYkhmWEYzZGI0aytuL009IiwibWFjIjoiYTVkMzkzNTM2NzM5NzlkZmM3NTFkNWQ2YzVlNDk3NGJhYzI2YzNjM2RhNzNlNzE5YzkzMmQ3YzA0MGM0MTgyMSIsInRhZyI6IiJ9', 'zH8lpPGcy1e9O3KHKBqaeFXffMm8xNMO7PqZayuCDzohEwBuaYdc4IIONqKA', '2026-03-01 18:44:17', '2026-03-02 05:15:16', NULL),
(5, 'COORD001', 'Jane Coordinator', 'coordinator@ams.test', NULL, '2026-03-02 05:15:16', 1, '$2y$12$kBEoUaTX80orJZfQNXRU8eHjwVqNcWBzLzNdde2ZTEvZDMtbmpPGu', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-01 19:17:38', '2026-03-02 05:15:16', NULL),
(6, 'STU001', 'Alice Student', 'alice@ams.test', NULL, '2026-03-11 17:42:46', 1, '$2y$12$8aFrweHsBzdZgqATzvgLzOkvzuDssTlzLucqFRhvLwufOlaJoX0fq', NULL, NULL, NULL, NULL, NULL, 'f3HZfi355ZXgANjg2eAfb4cDxjnu9l1dbzoNb09GTYtGwEfTdT66xA3cYN7M', '2026-03-01 19:32:02', '2026-03-11 17:42:46', NULL),
(7, 'STU002', 'Bob Reviewer', 'bob@ams.test', NULL, NULL, 1, '$2y$12$WOsXIxVBuCQVsncm.y7.2OaofCaSvax56vKV838UlbC7FYqAo.fL.', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-01 19:32:03', '2026-03-01 23:49:30', NULL),
(8, 'STU003', 'Carol Supervisor', 'carol@ams.test', NULL, NULL, 1, '$2y$12$mWtZ.lmDfv5KmVmyXa3SnuFX60mSuM7TDuQpxHnHIIRyhuU9mMu.6', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-01 19:32:03', '2026-03-01 23:49:30', NULL),
(9, 'STU004', 'Student 4', 'stu004@test.com', 8, NULL, 1, '$2y$12$eLSBR/cVoUnF6tcMYYLfqeglLfC0qawpL0ofYpX8ZXLfByirU1daO', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-01 22:49:48', '2026-03-14 18:55:43', NULL),
(10, 'STU005', 'Student 5', 'stu005@test.com', 7, NULL, 1, '$2y$12$AIQ2m5JNFZmxAHtrYK5o8OEKY2WuijpdKeZeg4cAvR6L.y03b5oI2', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-01 22:49:48', '2026-03-14 18:55:55', NULL),
(11, 'REV002', 'Reviewer Two', 'rev002@test.com', NULL, NULL, 1, '$2y$12$Zj0RwPkz5hNSQcbM/nGBuewJx6bgdzQduh7WSjwe3rIzPw6pS33A.', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-01 22:49:48', '2026-03-01 22:49:48', NULL),
(12, 'COORD002', 'Coordinator Two', 'coord002@test.com', NULL, NULL, 1, '$2y$12$TMkDvAzAiybSDzrtuju/0OgqK5L4kIjOZsNMBIVmzSaUirrgG3xri', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-01 22:49:48', '2026-03-01 22:49:48', NULL),
(13, 'STU006', 'Student 6', 'stu006@test.com', NULL, NULL, 1, '$2y$12$vIZuRumptwkNLbuck5Ms0uFDl2eT0bavoKTmIBPX2NeMOJKBqW0Jq', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-01 22:50:23', '2026-03-01 22:50:23', NULL),
(14, 'STU007', 'Student 7', 'stu007@test.com', NULL, NULL, 1, '$2y$12$FqmQ8PCqYCvk252JCMa6N.UjePfmcBFjO3AB4q01WbgqmzhELf542', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-01 22:50:23', '2026-03-01 22:50:23', NULL),
(15, 'STF001', 'Dr. James Wilson', 'staff@ams.test', 2, '2026-03-02 05:15:16', 1, '$2y$12$pwoQiRijzlBbrbGTcL6dGOWaEVcLkBPqpvXs4nw5lzCDEg9Be.51W', NULL, NULL, NULL, 'eyJpdiI6ImFTZC9vaTJXdkZETGVQZ1RZY3VTOGc9PSIsInZhbHVlIjoiMzBLb2RPN00vTFRQTEs3TU80aGhrd3pRWjFmTEhOYmRSbExvalgwSE11ND0iLCJtYWMiOiJmZmQ2MzQ4ZGJmNzM2MzVhYjk2NzFmNWZmYTgxZTQ1YmYwMGU4MzE1Y2YzYTZmN2NjZjUwZjgyNDM5Zjc2NDEwIiwidGFnIjoiIn0=', 'eyJpdiI6IjJXTnNOeitsSGJxaFdXT2ptWEs3Q1E9PSIsInZhbHVlIjoiWjZ4UTBYcXdBWlUxOHhaT0o5VzFQeU9PWnFWS055YTU2bVoxd1k5Z0FhTzhRQVpyS1JjVTQreThkSCtLUHJwZkxYV1VEU0gxaktPSG84cUNWS3NnQ1dSVGtad00zNGp5Mnk1am9Da2x5Y01qRTRqdmJxYlJGemVUZVhPOTg2STdLZTFJbnh0eEVHNjhBYTlLVWw2NldHWmlpaWhWOEhwK2ZBcG85bU9RdWJqWWQ4WjZmQmV1dXI1aGlvOER3eGdtVU43Y1czY1ZlUUcrSlBSMTkwZmZBaVo1RENPQS9Da0N6b3ZPMnNNRFNRUWlEU0V0djI1WlE0ZW5pdjU5SUllbURjdS9UNXNSclRQSVNXeUJ3MnEwWUY2N2tibGIwV1NLcm5HVFlYN2xROHcvbU1BY1VkUXkvS3BKNEV4Sjc4eTg3aWsvalQ3UDkrZ2FnTFJ6VGk0dWZUZjhZaEo0dlZaazJoRUhrNkI3QmVOS0tZMitTWkQyWTRmcHh3ZWJOUnluTjhqYm0vaEo2QVFrV1B3VysrLzhIYndBbmV5b2Y3WTY4RjkwbTBXMUpQMDJlR0dUWVdIUlZVbmJIb3hVQjFBLyt4MkVtTXhyWWdPc3o2Y3hXR2JJK0NCQnI2VDJ3aFJSckxKWE1GcmhuZW5YUGZxMlhvMmVNMnJnc0NlR3VKYktIQlh3dSt1Zjhubkd3dC8vZElTRGhORXdWYjZadHEvK0VUeFc5TGxiMzNZQ1puM29SNXpOcVBQUENBQ1RtSFVMQXdHRENZTEVSdWovTkkrOUNCbERlQzl1ZjJEcWwxR3cxRVU4RFdpaXNoNnljVVNDSzlFQVVLR2FvUFpsZTRNd2dwZGExVWFyNU9Wbm1CWDh3bTZOV0FVdUxNa25BVzcwTzlVdm9nL0pqOGZtS0FQUVNCUzRhNzJhVkcyaTFjeHIiLCJtYWMiOiJlM2EzZDI4ZDZkNmU2ZTM0NTg2M2MzNjdhZmMwYzI1ODFjZmQ5NTQ0MGY4ZWEyYzY5ZWE4YWE2ZDg0MzQyZjcyIiwidGFnIjoiIn0=', '7gBVpHYWmhaKd4wJk5aGVzOfV6GbLAnDAj4T5PElnIZ22LhYBHHFM8LMqHwk', '2026-03-02 04:55:14', '2026-03-14 17:58:44', NULL),
(16, 'STF002', 'Dr. Emily Park', 'reviewer@ams.test', 4, '2026-03-02 05:15:16', 1, '$2y$12$UiI62M.Y0/A9tgf7MKFdQeAweduhT4N5AEgPIAroQ2kpo3nIiun4W', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-02 04:55:14', '2026-03-02 05:15:16', NULL),
(18, 'ADM002', 'Screenshot Admin', 'screenshotadmin@ams.test', NULL, '2026-03-02 05:15:46', 1, '$2y$12$twDfBe.Xz9aJ/Kihz8ziOOToeXjhHsaafZIK5kk0IwFG9OSFeUXXu', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-02 05:15:16', '2026-03-02 05:15:46', NULL),
(19, '26s2020', 'Almuntaser Alsarmi', '26s2020@utas.edu.om', 2, '2026-03-11 17:24:36', 1, '$2y$12$OZiahCnjEq4Zlbg.rl8Pd.Z/JE6NEPsAploKethIBnPgCWKCINikK', NULL, NULL, NULL, 'eyJpdiI6InNnZ3BIZmVtQWVidDlvVFJqaEdCUUE9PSIsInZhbHVlIjoiL2NrY3FxR3VKYnVEbkU5SThBQVVJUmwwek1CUnNwNmRwNmVaUWtzT1Jnaz0iLCJtYWMiOiI5NGE1YjgxMjU2ODBlN2U3ZWZhZjJmZGYyN2E2NWM0MGU1NTQyZGRhMGE1ZTY4OTQxM2MzZjBmNzFmZDM4ZjUyIiwidGFnIjoiIn0=', 'eyJpdiI6IjJEN0ZJMVRqVnZzS0Y3SlVLbkt0R1E9PSIsInZhbHVlIjoiQWUrL0tmWVNzZ00rQ0dkVlh3RENwYm9sQnlDdWlsU3AyTnhYS3Y4enZHUzA0Ry8zdnBoV2lLQ1hISjhXZHUzYkFrOUN4NCtvOEJSMVdTNytKZVNoT1FEb1oreENXUEpYODRHWFFocWJZVmNLRGNXYkc1SU96c3JlYmg3d2UvTDRxQlVmNlVKUWt6OGZESDlTTHhmZ1plc3BOMy9SL3R6MkJsWmpoUVIxTU03bjBRUGV0ZFAxekU5Y21Lak1GMHNoNCtILzRqZTgxbjhMTkhDUTRrQlBMNXlQODlTYkFSR0M4ZDlwTUhrR2hyZnlSa0haa2VjS3RCeFAxby9BeCtiQ25vbjdRMkpIbGV0cmZXSUdvNkwxdzJ1M1JxQk1LaWFuK1J6SGdiS2d3dVBYZ1BNUldMUlNKM3A1TVM1VEhnQjFmNXZxNndxa1VETU1lUjFqOU5pSWRtL1VGbEwxQTBYa1NIbGlOR1g0M1V3QUFETVNCNWlVN3VQdG9DS3gzUnJSc3hVam9YbmFPR3lpNmRmQUlvOElxOUtGeDVoZXdOZCsyWCtyUm5QeHdDWnBCLzc4elg1Y09OMWEvbzZTNXBJejdDOVhsekxvNFNUU2xEdWN0UXdxZ3pkYVRBSWxFVGcvVXNiQzB3ZXFBc1A2UUVrY1l0NnZDRE5aRjlaRjI3UExPM1diNWhCaHpYRjBhVW1XcS9ieEVINEUxRUs0b25GTGNObU9kV3R3VUhrYTFRUGVDVmQ5RkpWeHNOaFJFTzNvVHQ5cGJ2bWxxNk1qR2pWeE9GMU8vdUw0akVpMGtSeEhDaEIyMUVlTVdpWVpsdE5LVy8rUVpZZFVPUkZUQmRjNlUxTlJIRW1SaU80dHM4aTVmUFMraGNRcFlNSzFSU0N1dVdsdDlJVTIwV1RMYkgxbVlGQWY1WWdLVW0vWjJLTksiLCJtYWMiOiJjMTk0Yzk4MDVkNTg4YjQ2YThhMGFjMTA3MmZmNzk0MjI4NDBiNmYzYjQ0MjI1Mzc0ZWQ1NjZkMGY0NWE1MWNiIiwidGFnIjoiIn0=', '6V8y4idHiLREcnBhii310K8fqCJHboJry08edmOGepsTLBJoFq67fkm8QEXc', '2026-03-11 17:13:07', '2026-03-16 19:07:57', NULL),
(20, 's2020s20', 'Almuntaser Alsarmi', '26s2067@utas.edu.om', 2, '2026-03-14 18:08:30', 1, '$2y$12$yiUc/SdonLoON/pWA.t8Jeqd2Pi/1/20RoGsOymXfZCLFQ/IQvcPq', NULL, NULL, NULL, NULL, NULL, 'tbymawoz8kJsNam1uOy1gsapyVqWwhiKEMcJCbIduLnDA08a4vN1KqgvuZzm', '2026-03-14 18:05:41', '2026-03-14 18:13:28', NULL),
(21, 'IT001234', 'Ali Al-Busaidi', 'ali@example.edu', NULL, NULL, 1, '$2y$12$AA77e0QdRNO1.ZpKk7jaiOjVMCd5omC/feKDm3J7UPvhUvwOg160W', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14 18:37:30', '2026-03-14 18:37:30', NULL),
(22, 'IT001235', 'Sara Al-Habsi', 'sara@example.edu', NULL, NULL, 1, '$2y$12$AvfAuQ/QRgE3wm/KdjxONO0WUR/AL/Gmk2C/O85EvmZsjoaJ7OjmC', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14 18:37:30', '2026-03-14 18:37:30', NULL),
(23, 'IT001236', 'Mohammed Al-Sadi', 'mohammed@example.edu', NULL, NULL, 1, '$2y$12$4l..nW.WqA1.HJmZS4PBeu5rtz9SnzE7EXnf.WGZcgYiM/5yx1guy', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14 18:37:30', '2026-03-14 18:37:30', NULL),
(24, 'ttestt33', 'noomtyy', 'omdnjs@ams.test', NULL, NULL, 0, '$2y$12$uelxdE4rd0LX.Am86qz1O.D5kazqzF6Z6XE/UCo5XfrvMZt94a7Py', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-27 00:54:36', '2026-03-27 00:54:36', NULL),
(25, '26s2sdsd', 'monta', 'oman.m123456@gmail.com', NULL, NULL, 1, '$2y$12$i9ccFBjswP8lwmMGcNXBhefis5EKR.2JVGE.NWx7kOa12Gy543soq', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-27 11:11:53', '2026-03-27 11:12:29', NULL),
(26, '12345678', 'Test User', 'test@example.com', NULL, NULL, 0, '$2y$12$jKkn8l9I3tIBdAA35KCtLOJnlFmFYAJvClA9peOetLV6yqpaXEGSu', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-30 21:22:00', '2026-04-30 21:22:00', NULL);



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;