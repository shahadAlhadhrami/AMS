# AMS Functional Requirements Specification

> **Version:** 1.0
> **Date:** 2026-03-01
> **Status:** Draft
> **System:** Assessment Management System (AMS)
> **Tech Stack:** Laravel 12 + FilamentPHP v5 + MySQL 8.x

---

## 1. Introduction

### 1.1 Purpose
This document provides a comprehensive list of all functional requirements for the AMS. Each requirement is traceable to the system design decisions in `system.md`, the data flow processes in `DFD.md`, and the database schema in `database_schema.sql`.

### 1.2 Scope
The AMS is a centralized, secure web-based system for managing and consolidating course project assessment marks. It covers:
- Configurable evaluation workflows (not hardcoded phases)
- Group and individual grading within the same rubric
- Multi-role support (Super Admin, Coordinator, Supervisor, Reviewer, Student)
- Audit-logged grade management with proxy marking and coordinator overrides

**Out of scope:** File/document uploads, student work submissions, cloud hosting, SSO integration, non-assessment academic components.

### 1.3 Definitions

| Term | Definition |
|------|-----------|
| AMS | Assessment Management System |
| MFA | Multi-Factor Authentication |
| SIS | Student Information System (external, e.g., Moodle, Banner) |
| RBAC | Role-Based Access Control |
| Rubric Template | A reusable grading form blueprint with criteria and score levels |
| Phase Template | A blueprint defining which rubrics are filled, in what order, by which role |
| Template Pool | The collection of all rubric and phase templates available for reuse |
| Consolidated Marks | The final calculated score per student, derived from all evaluation submissions |
| Proxy Marking | When a Coordinator enters marks on behalf of an absent evaluator |
| Fill Order | The sequential order in which rubrics must be completed within a phase |

### 1.4 Related Documents
- `systemDocs/SystemDef/system.md` — System definition and design decisions
- `systemDocs/DFD.md` — Data Flow Diagrams (Level 0 and Level 1)
- `systemDocs/Database_Stageing/database_schema.sql` — Database schema (16 tables, 4 domains)
- `systemDocs/Database_Stageing/dummy_data.sql` — Sample test data

---

## 2. Module 1: Authentication & User Management (FR-AU)

| ID | Description | Actor(s) | Priority | Tables Touched | DFD Process |
|----|------------|----------|----------|---------------|-------------|
| FR-AU-001 | The system shall provide login via email and password using Laravel Fortify. | All Users | Must | `users` | 1.0 |
| FR-AU-002 | The system shall support MFA enrollment and TOTP-based verification via Laravel Fortify. | All Users | Must | `users` | 1.0 |
| FR-AU-003 | The system shall enforce password policies including rejection of known-leaked passwords via Laravel Pwned Passwords. | All Users | Must | `users` | 1.0 |
| FR-AU-004 | The system shall bootstrap a Super Admin account via `php artisan db:seed` during initial installation. | System/DevOps | Must | `users`, spatie permission tables | 1.0 |
| FR-AU-005 | The system shall allow manual creation of individual user accounts via the UI. Super Admin creates Coordinators; Coordinators create Staff and Students. | Super Admin, Coordinator | Must | `users`, spatie permission tables | 1.0 |
| FR-AU-006 | The system shall allow bulk import of users via CSV upload (university_id, name, email, role). CSV format to be defined at implementation. Import uses **all-or-nothing** strategy. | Coordinator | Must | `users`, spatie permission tables | 1.0 |
| FR-AU-007 | The system shall allow assigning and revoking roles (super_admin, coordinator, supervisor, reviewer, student) to users via spatie/laravel-permission. | Super Admin, Coordinator | Must | spatie permission tables | 1.0 |
| FR-AU-008 | The system shall allow editing user profile fields (name, email, specialization). | Super Admin, Coordinator | Must | `users` | 1.0 |
| FR-AU-009 | The system shall support soft-deleting (deactivating) user accounts. | Super Admin, Coordinator | Should | `users` | 1.0 |
| FR-AU-010 | The system shall allow a single user to hold multiple roles simultaneously (e.g., Supervisor + Reviewer for different projects). | System | Must | spatie permission tables | 1.0 |
| FR-AU-011 | The system shall integrate brute-force login protection via Fail2Ban. | System | Should | None (infrastructure) | 1.0 |

---

## 3. Module 2: Master Data Management (FR-MD)

| ID | Description | Actor(s) | Priority | Tables Touched | DFD Process |
|----|------------|----------|----------|---------------|-------------|
| FR-MD-001 | The system shall provide full CRUD for Departments. | Super Admin | Must | `departments` | 1.0 |
| FR-MD-002 | The system shall provide full CRUD for Specializations, each linked to a Department. | Super Admin | Must | `specializations`, `departments` | 1.0 |
| FR-MD-003 | The system shall provide full CRUD for Courses (code + title). | Super Admin | Must | `courses` | 1.0 |
| FR-MD-004 | The system shall provide full CRUD for Grading Scales (min_score, max_score, letter_grade, gpa_equivalent). | Super Admin | Must | `grading_scales` | 1.0 |
| FR-MD-005 | The system shall validate that grading scale score ranges do not overlap. | System | Should | `grading_scales` | 1.0 |
| FR-MD-006 | The system shall soft-delete all master data entities (preserving audit trail). | Super Admin | Must | All Domain 1 tables | 1.0 |

---

## 4. Module 3: Template Pool / Workflow Engine (FR-TP)

| ID | Description | Actor(s) | Priority | Tables Touched | DFD Process |
|----|------------|----------|----------|---------------|-------------|
| FR-TP-001 | The system shall allow creating a rubric template with a name (version auto-set to 1). | Coordinator | Must | `rubric_templates` | 2.0 |
| FR-TP-002 | The system shall allow adding criteria to a rubric template, each with title, description, max_score, and is_individual flag (true = score per student, false = score per project). | Coordinator | Must | `criteria`, `rubric_templates` | 2.0 |
| FR-TP-003 | The system shall support inline criteria creation within rubric template editing (no navigation away required). | Coordinator | Must | `criteria` | 2.0 |
| FR-TP-004 | The system shall optionally allow saving inline-created criteria to a standalone pool for reuse across other rubrics. | Coordinator | Should | `criteria` | 2.0 |
| FR-TP-005 | The system shall allow adding score levels (rating scale) to each criterion with label, score_value, description, and sort_order. | Coordinator | Must | `score_levels`, `criteria` | 2.0 |
| FR-TP-006 | The system shall allow cloning a rubric template, creating a new record with incremented version and parent_template_id linkage. All criteria and score levels are duplicated. | Coordinator | Must | `rubric_templates`, `criteria`, `score_levels` | 2.0 |
| FR-TP-007 | The system shall lock a rubric template (is_locked=true) to prevent editing once it is used in an evaluation. Locking happens automatically on first evaluation use and can be triggered manually by the Coordinator. | System, Coordinator | Must | `rubric_templates` | 2.0 |
| FR-TP-008 | The system shall allow archiving (soft-deleting) a rubric template. | Coordinator | Should | `rubric_templates` | 2.0 |
| FR-TP-009 | The system shall allow importing a rubric template from a formatted CSV file (one rubric at a time), creating the template along with all its criteria and score levels. CSV format to be defined at implementation. Import uses **all-or-nothing** strategy: any validation error aborts the entire import. | Coordinator | Must | `rubric_templates`, `criteria`, `score_levels` | 2.0 |
| FR-TP-010 | The system shall auto-calculate the rubric template's total_marks as the sum of its criteria max_score values. | System | Must | `rubric_templates`, `criteria` | 2.0 |
| FR-TP-011 | The system shall allow creating a phase template with a name and total_phase_marks. | Coordinator | Must | `phase_templates` | 2.0 |
| FR-TP-012 | The system shall allow mapping rubric templates to a phase template via phase_rubric_rules, specifying evaluator_role, fill_order, max_marks, and aggregation_method. | Coordinator | Must | `phase_rubric_rules`, `phase_templates`, `rubric_templates` | 2.0 |
| FR-TP-013 | The system shall support configurable aggregation methods per rubric-phase rule: AVERAGE, WEIGHTED_AVERAGE, SUM, MAX. | Coordinator | Must | `phase_rubric_rules` | 2.0 |
| FR-TP-014 | The system shall enforce fill_order sequencing on a **per-evaluator** basis: an evaluator cannot fill a rubric at fill_order N until they have submitted their own rubric at fill_order N-1. Each evaluator progresses independently. | System | Must | `phase_rubric_rules`, `evaluations` | 2.0, 4.0 |
| FR-TP-015 | The system shall display all rubric templates in a browsable "pool" view, showing version lineage, lock status, and usage. | Coordinator | Should | `rubric_templates` | 2.0 |

---

## 5. Module 4: Semester & Academic Setup (FR-SA)

| ID | Description | Actor(s) | Priority | Tables Touched | DFD Process |
|----|------------|----------|----------|---------------|-------------|
| FR-SA-001 | The system shall allow creating a semester with name, academic_year, start_date, and end_date. | Coordinator | Must | `semesters` | 3.0 |
| FR-SA-002 | The system shall allow activating/deactivating a semester (is_active toggle). | Coordinator | Must | `semesters` | 3.0 |
| FR-SA-003 | The system shall allow closing a semester (is_closed=true), making all data within it read-only. | Coordinator | Must | `semesters` | 3.0 |
| FR-SA-004 | The system shall support assigning one or more Coordinators to a semester via the coordinator_semester pivot. | Super Admin, Coordinator | Must | `coordinator_semester` | 3.0 |
| FR-SA-005 | The system shall allow creating a project with title, semester, course, phase_template, specialization, and supervisor. | Coordinator | Must | `projects` | 3.0 |
| FR-SA-006 | The system shall allow assigning 1 to 4 students to a project. | Coordinator | Must | `project_student` | 3.0 |
| FR-SA-007 | The system shall assign exactly one supervisor per project. The supervisor cannot also be a reviewer for the same project. | Coordinator | Must | `projects` | 3.0 |
| FR-SA-008 | The system shall allow assigning one or more reviewers to a project (flexible count). | Coordinator | Must | `project_reviewer` | 3.0 |
| FR-SA-009 | The system shall enforce that the supervisor of a project cannot be assigned as a reviewer of the same project. | System | Must | `projects`, `project_reviewer` | 3.0 |
| FR-SA-010 | The system shall allow bulk CSV import for creating projects with student, supervisor, and reviewer assignments in one operation. CSV format to be defined at implementation. Import uses **all-or-nothing** strategy. | Coordinator | Must | `projects`, `project_student`, `project_reviewer` | 3.0 |
| FR-SA-011 | The system shall allow linking a Phase 2 project to its Phase 1 predecessor via previous_phase_project_id. | Coordinator | Should | `projects` | 3.0 |
| FR-SA-012 | The system shall track project status progression: setup → evaluating → completed. | System | Must | `projects` | 3.0, 4.0 |
| FR-SA-013 | The system shall validate that a student can only belong to one project per semester. | System | Must | `project_student`, `projects` | 3.0 |

---

## 6. Module 5: Assessment Execution (FR-AE)

| ID | Description | Actor(s) | Priority | Tables Touched | DFD Process |
|----|------------|----------|----------|---------------|-------------|
| FR-AE-001 | The system shall dynamically render an evaluation form based on the rubric template's criteria and score levels. | Supervisor, Reviewer | Must | `evaluations`, `rubric_templates`, `criteria`, `score_levels` | 4.0 |
| FR-AE-002 | For criteria where is_individual=false (group), the system shall accept a single score that applies to the entire project (student_id=NULL in evaluation_scores). | Supervisor, Reviewer | Must | `evaluation_scores` | 4.0 |
| FR-AE-003 | For criteria where is_individual=true, the system shall require a separate score for each student in the project (one evaluation_score row per student). | Supervisor, Reviewer | Must | `evaluation_scores` | 4.0 |
| FR-AE-004 | The system shall allow saving an evaluation as draft (status=draft), preserving all entered scores as editable. | Supervisor, Reviewer | Must | `evaluations` | 4.0 |
| FR-AE-005 | The system shall allow submitting an evaluation (status=submitted), which locks all scores from further editing. | Supervisor, Reviewer | Must | `evaluations` | 4.0 |
| FR-AE-006 | The system shall allow evaluators to select a predefined score level from a dropdown or enter a manual score within the criterion's max_score range. | Supervisor, Reviewer | Must | `evaluation_scores`, `score_levels` | 4.0 |
| FR-AE-007 | The system shall allow per-criterion text feedback that is visible to students. | Supervisor, Reviewer | Must | `evaluation_scores` | 4.0 |
| FR-AE-008 | The system shall allow general feedback per evaluation. | Supervisor, Reviewer | Should | `evaluations` | 4.0 |
| FR-AE-009 | The system shall support proxy marking: a Coordinator enters marks on behalf of an absent evaluator. The evaluator_id remains the original assigned person; on_behalf_of_user_id records the Coordinator. | Coordinator | Must | `evaluations`, `evaluation_scores` | 4.0 |
| FR-AE-010 | The system shall allow uploading an evidence attachment (scanned physical rubric) for proxy-marked evaluations. | Coordinator | Should | `evaluations` | 4.0 |
| FR-AE-011 | The system shall allow a Coordinator to unlock a submitted evaluation (sets unlocked_by, changes status from submitted to draft). This action must be audit-logged. | Coordinator | Must | `evaluations` | 4.0 |
| FR-AE-012 | The system shall auto-create pending evaluation records when a project transitions to "evaluating" status, based on the phase_rubric_rules of the project's phase template. | System | Must | `evaluations`, `phase_rubric_rules` | 4.0 |
| FR-AE-013 | The system shall enforce fill_order on a **per-evaluator** basis: an evaluator cannot start filling a rubric at fill_order N until they have submitted their own rubric at fill_order N-1. Each evaluator progresses independently of other evaluators. | System | Must | `evaluations`, `phase_rubric_rules` | 4.0 |

---

## 7. Module 6: Grade Consolidation (FR-GC)

| ID | Description | Actor(s) | Priority | Tables Touched | DFD Process |
|----|------------|----------|----------|---------------|-------------|
| FR-GC-001 | The system shall auto-calculate consolidated marks when all evaluations for a project reach "submitted" status. | System | Must | `consolidated_marks`, `evaluations`, `evaluation_scores`, `phase_rubric_rules` | 5.0 |
| FR-GC-002 | When multiple reviewers evaluate the same rubric, the system shall apply the configured aggregation method (AVERAGE by default). | System | Must | `phase_rubric_rules`, `evaluation_scores` | 5.0 |
| FR-GC-003 | The system shall support all four aggregation methods: AVERAGE, WEIGHTED_AVERAGE, SUM, MAX. | System | Must | `phase_rubric_rules` | 5.0 |
| FR-GC-004 | The system shall generate a per-student consolidated mark row, computing individual totals by combining group criterion scores with individual criterion scores specific to that student. | System | Must | `consolidated_marks` | 5.0 |
| FR-GC-005 | The system shall store the score breakdown via consolidated_mark_components, recording each source rubric's contribution (source_label + score). | System | Must | `consolidated_mark_components` | 5.0 |
| FR-GC-006 | The system shall allow a Coordinator to manually override a consolidated mark by providing an override_score and a mandatory override_reason. | Coordinator | Must | `consolidated_marks` | 5.0 |
| FR-GC-007 | The system shall retain the original total_calculated_score even when overridden. The override never erases the calculated value. | System | Must | `consolidated_marks` | 5.0 |
| FR-GC-008 | The system shall map the final score to a letter grade and GPA equivalent using the grading_scales table. | System | Should | `grading_scales`, `consolidated_marks` | 5.0 |

---

## 8. Module 7: Reporting & Export (FR-RE)

| ID | Description | Actor(s) | Priority | Tables Touched | DFD Process |
|----|------------|----------|----------|---------------|-------------|
| FR-RE-001 | The system shall provide a Coordinator dashboard showing all projects in their semesters with real-time status tracking (pending, in-progress, completed evaluations). | Coordinator | Must | `projects`, `semesters`, `evaluations` | 6.0 |
| FR-RE-002 | The system shall provide a Supervisor dashboard showing all supervised projects and their evaluation progress. | Supervisor | Must | `projects`, `evaluations` | 6.0 |
| FR-RE-003 | The system shall provide a dual-role Reviewer/Supervisor dashboard with clearly separated sections: "Projects I am Supervising" and "Projects I am Reviewing". | Supervisor, Reviewer | Must | `projects`, `project_reviewer`, `evaluations` | 6.0 |
| FR-RE-004 | The system shall provide a Student portal showing internal marks (supervisor component) and consolidated marks only. Students shall not see individual reviewer identities. | Student | Must | `consolidated_marks`, `evaluation_scores`, `consolidated_mark_components` | 6.0 |
| FR-RE-005 | The system shall allow generating PDF reports (consolidated grade reports for official academic records). | Coordinator | Must | `consolidated_marks`, `projects`, `users` | 6.0 |
| FR-RE-006 | The system shall allow exporting final grades as CSV/Excel for SIS upload, including university_id, student name, project title, final score, and letter grade. | Coordinator | Must | `consolidated_marks`, `users`, `projects` | 6.0 |
| FR-RE-007 | The system shall provide a per-project detail view showing all evaluations, individual scores, and the consolidated result. | Coordinator | Must | All Domain 4 tables | 6.0 |

---

## 9. Module 8: System Features / Cross-Cutting (FR-SF)

| ID | Description | Actor(s) | Priority | Tables Touched | DFD Process |
|----|------------|----------|----------|---------------|-------------|
| FR-SF-001 | The system shall audit-log all mark changes (who, when, old value, new value) using Spatie Activitylog. | System | Must | `activity_log` (spatie) | All |
| FR-SF-002 | The system shall audit-log all evaluation unlock events, recording the Coordinator identity. | System | Must | `activity_log` (spatie) | 4.0 |
| FR-SF-003 | The system shall audit-log all proxy marking events, recording who entered marks on behalf of whom. | System | Must | `activity_log` (spatie) | 4.0 |
| FR-SF-004 | The system shall send email notification to evaluators when they are assigned to a project. | System | Should | None (mail) | 3.0 |
| FR-SF-005 | The system shall send email notification to Coordinators when all evaluations for a project are submitted. | System | Should | None (mail) | 4.0 |
| FR-SF-006 | The system shall send email notification to evaluators when their submitted assessment is unlocked. | System | Should | None (mail) | 4.0 |
| FR-SF-007 | The system shall send email notification to students when their consolidated marks are finalized. | System | Should | None (mail) | 5.0 |
| FR-SF-008 | The system shall implement soft deletes (deleted_at column) on all entities to preserve audit trails. | System | Must | All tables | All |
| FR-SF-009 | The system shall allow reusing rubric and phase templates across semesters by pulling from the template pool without recreation. | Coordinator | Must | `rubric_templates`, `phase_templates` | 2.0, 3.0 |

---

## Appendix A: Requirements Traceability Matrix

This matrix maps each requirement module to the DFD Level 1 process it implements and the database domain it operates on.

| Module | FR Prefix | DFD Process | Database Domain | Requirement Count |
|--------|-----------|-------------|-----------------|-------------------|
| Authentication & User Management | FR-AU | 1.0 Manage Master Data | Domain 1: Users & Master Data | 11 |
| Master Data Management | FR-MD | 1.0 Manage Master Data | Domain 1: Users & Master Data | 6 |
| Template Pool / Workflow Engine | FR-TP | 2.0 Build Workflow Rules | Domain 2: Template Pool | 15 |
| Semester & Academic Setup | FR-SA | 3.0 Configure Semester Sandbox | Domain 3: Academic Sandbox | 13 |
| Assessment Execution | FR-AE | 4.0 Process Assessments | Domain 4: Execution & Scoring | 13 |
| Grade Consolidation | FR-GC | 5.0 Consolidate Grades | Domain 4: Execution & Scoring | 8 |
| Reporting & Export | FR-RE | 6.0 Generate Reports & Export | Cross-domain (read) | 7 |
| System Features | FR-SF | Cross-cutting | Cross-domain | 9 |

---

## Appendix B: Priority Summary

| Priority | Count | Description |
|----------|-------|-------------|
| **Must** | 64 | Required for MVP. System cannot function without these. |
| **Should** | 18 | Important for usability and security but not blocking. |
| **Could** | 0 | Nice-to-have features deferred to future iterations. |
| **Total** | 82 | |

All **Must** requirements are required before the system can be used in production for a single semester cycle. **Should** requirements enhance security (Fail2Ban, Pwned Passwords validation) and usability (browsable pool view, grading scale overlap validation, email notifications) but can be added incrementally.
