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

-- INSERT INTO `password_reset_tokens` omitted (do not overwrite target users/sessions)

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

-- INSERT INTO `sessions` omitted (do not overwrite target users/sessions)

INSERT INTO `specializations` (`id`, `department_id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 2, 'Software Engineering', '2026-03-01 19:13:43', '2026-03-01 19:13:43', NULL),
(3, 2, 'Information Systems', '2026-03-01 19:15:45', '2026-03-01 19:15:57', '2026-03-01 19:15:57'),
(4, 2, 'Artificial Intelligence', '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(5, 3, 'Mechanical Engineering', '2026-03-02 04:55:13', '2026-03-02 04:55:13', NULL),
(6, 3, 'eng test', '2026-03-08 17:57:35', '2026-03-08 17:57:35', NULL),
(7, 4, 'Marketing', '2026-03-14 18:54:46', '2026-03-14 18:54:46', NULL),
(8, 4, 'Accounting', '2026-03-14 18:55:00', '2026-03-14 18:55:00', NULL);

-- INSERT INTO `users` omitted (do not overwrite target users/sessions)



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;