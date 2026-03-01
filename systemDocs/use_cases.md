# AMS Use Cases

> **Version:** 1.0
> **Date:** 2026-03-01
> **Status:** Draft
> **System:** Assessment Management System (AMS)

---

## 1. Introduction

### 1.1 Purpose
This document defines the formal use cases for every actor in the AMS. Each use case describes a specific interaction between an actor and the system, including preconditions, step-by-step flow, postconditions, alternative flows, and business rules.

### 1.2 Actors

| Actor | System Role | Description |
|-------|------------|-------------|
| **Super Admin** | `super_admin` | System-level administrator seeded at installation. Creates Coordinator accounts and manages master data (departments, specializations, courses, grading scales). |
| **Coordinator** | `coordinator` | Academic manager who creates semesters, configures workflows, manages users, monitors assessments, and handles grade overrides. Manages only the semesters they are assigned to. |
| **Supervisor** | `supervisor` | Project supervisor assigned to one or more projects. Fills supervisor rubrics (internal marks). Can also hold reviewer role for different projects. |
| **Reviewer** | `reviewer` | External examiner assigned to review one or more projects. Fills reviewer rubrics. Can also hold supervisor role for different projects. |
| **Student** | `student` | Views their internal marks (supervisor) and consolidated marks. Read-only access. |
| **System** | (automated) | Automated processes triggered by state changes (auto-generate evaluations, auto-calculate marks, lock templates, send notifications). |

### 1.3 Use Case Index

| ID | Title | Actor |
|----|-------|-------|
| UC-SA-01 | Create Coordinator Account | Super Admin |
| UC-SA-02 | Manage Master Data | Super Admin |
| UC-CO-01 | Create Rubric Template | Coordinator |
| UC-CO-02 | Import Rubric from CSV | Coordinator |
| UC-CO-03 | Create Phase Template | Coordinator |
| UC-CO-04 | Create Semester | Coordinator |
| UC-CO-05 | Bulk Import Users | Coordinator |
| UC-CO-06 | Create Project and Assign Group | Coordinator |
| UC-CO-07 | Assign Reviewers to Project | Coordinator |
| UC-CO-08 | Monitor Assessment Progress | Coordinator |
| UC-CO-09 | Unlock Submitted Assessment | Coordinator |
| UC-CO-10 | Proxy Mark Entry | Coordinator |
| UC-CO-11 | Override Consolidated Mark | Coordinator |
| UC-CO-12 | Export Grades for SIS | Coordinator |
| UC-CO-13 | Generate PDF Report | Coordinator |
| UC-SU-01 | View Supervised Projects | Supervisor |
| UC-SU-02 | Fill Assessment Form | Supervisor |
| UC-SU-03 | Save Draft / Submit Assessment | Supervisor |
| UC-SU-04 | View Project Evaluation Progress | Supervisor |
| UC-RE-01 | View Review Assignments | Reviewer |
| UC-RE-02 | Fill Assessment Form | Reviewer |
| UC-RE-03 | Save Draft / Submit Assessment | Reviewer |
| UC-ST-01 | View Internal Marks | Student |
| UC-ST-02 | View Consolidated Marks | Student |
| UC-SY-01 | Auto-Generate Evaluation Records | System |
| UC-SY-02 | Auto-Calculate Consolidated Marks | System |
| UC-SY-03 | Lock Rubric Template on First Use | System |
| UC-SY-04 | Send Email Notifications | System |

---

## 2. Super Admin Use Cases

### UC-SA-01: Create Coordinator Account

| Field | Value |
|-------|-------|
| **Actor** | Super Admin |
| **Preconditions** | Super Admin is logged into the Admin Panel |
| **Trigger** | Super Admin navigates to User Management |

**Main Flow:**
1. Super Admin navigates to User Management → Users → Create.
2. System displays the user creation form.
3. Super Admin enters: university_id, name, email, password.
4. Super Admin selects the `coordinator` role from the roles checkbox list.
5. Super Admin optionally selects a specialization.
6. Super Admin clicks "Create".
7. System validates all fields (unique university_id, unique email, password meets policy).
8. System creates the user record and assigns the coordinator role via spatie/laravel-permission.
9. System displays success notification.

**Postconditions:**
- New user exists in the `users` table with the `coordinator` role.
- The coordinator can now log into the Admin Panel.

**Alternative Flows:**
- **AF1 — Validation Error:** If university_id or email already exists, system displays inline error. Super Admin corrects and retries.
- **AF2 — Bulk Import:** Super Admin navigates to Bulk Import → Users tab and uploads a CSV containing multiple coordinator records.

**Business Rules:**
- Only Super Admin can assign the `coordinator` role.
- Coordinators cannot self-register; they must be created by Super Admin.
- Password must pass the Pwned Passwords check.

---

### UC-SA-02: Manage Master Data

| Field | Value |
|-------|-------|
| **Actor** | Super Admin |
| **Preconditions** | Super Admin is logged into the Admin Panel |
| **Trigger** | Super Admin navigates to Master Data section |

**Main Flow:**
1. Super Admin navigates to Master Data in the sidebar.
2. Super Admin selects the entity type: Departments, Specializations, Courses, or Grading Scales.
3. System displays the list page for the selected entity.
4. Super Admin performs one of: Create, Edit, or Delete (soft).
5. For Create/Edit: system displays form, Super Admin fills fields and saves.
6. System validates and persists the record.
7. System displays success notification.

**Postconditions:**
- Master data record is created, updated, or soft-deleted.
- Changes are immediately available for downstream use (e.g., specializations appear in user forms, courses appear in project forms).

**Alternative Flows:**
- **AF1 — Grading Scale Overlap:** If creating/editing a grading scale entry causes score range overlap with another entry, system displays validation error.
- **AF2 — Delete with Dependencies:** If attempting to soft-delete a department that has linked specializations, system warns but allows deletion (specializations retain their department_id reference; soft delete preserves data).

**Business Rules:**
- All master data uses soft deletes (preserving audit trail).
- Grading scale score ranges must not overlap.

---

## 3. Coordinator Use Cases

### UC-CO-01: Create Rubric Template

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Coordinator is logged into the Admin Panel |
| **Trigger** | Coordinator navigates to Template Pool → Rubric Templates |

**Main Flow:**
1. Coordinator navigates to Template Pool → Rubric Templates → Create.
2. System displays the rubric template form.
3. Coordinator enters the rubric name (e.g., "Phase 1 Final Reviewer").
4. Coordinator saves the rubric template. System creates the record with version=1, is_locked=false.
5. System redirects to the rubric template view/edit page with the Criteria RelationManager.
6. Coordinator clicks "Create" in the Criteria section.
7. Coordinator enters criterion details:
   - Title (e.g., "Literature Review")
   - Description (optional instructions)
   - Max Score (e.g., 5.0)
   - Is Individual toggle (false = group, true = per-student)
8. Coordinator adds score levels using the Repeater within the criterion form:
   - For each level: label (e.g., "Excellent"), score_value (e.g., 5.0), description (e.g., "Very clearly studied related literature...")
9. Coordinator saves the criterion. System creates `criteria` and `score_levels` records.
10. Coordinator repeats steps 6-9 for additional criteria.
11. System auto-calculates `total_marks` on the rubric template from the sum of all criteria max_score values.

**Postconditions:**
- Rubric template exists in the pool with all criteria and score levels.
- total_marks is auto-calculated.
- Template is unlocked and available for use in phase templates.

**Alternative Flows:**
- **AF1 — Inline Criterion Creation:** Coordinator creates criteria directly within the rubric form without navigating away (same steps 6-9 executed inline).
- **AF2 — Save Criterion to Pool:** After creating a criterion inline, Coordinator clicks "Save to Pool" to make it available for reuse in other rubric templates.
- **AF3 — Clone Existing:** Instead of creating from scratch, Coordinator clones an existing rubric template (see clone action). System duplicates the template with incremented version and all criteria/score levels.

**Business Rules:**
- Inline-created criteria are NOT automatically saved to a standalone pool unless Coordinator explicitly saves them.
- total_marks is always the sum of criteria max_scores (auto-calculated, not manually editable).
- A rubric template with version > 1 has a parent_template_id linking to its predecessor.

---

### UC-CO-02: Import Rubric from CSV

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Coordinator has a properly formatted CSV file |
| **Trigger** | Coordinator navigates to Bulk Import → Rubric Import tab |

**Main Flow:**
1. Coordinator navigates to Tools → Bulk Import → Import Rubric tab.
2. Coordinator downloads the CSV template link (to see required format).
3. Coordinator uploads the CSV file containing one rubric definition.
4. System parses the CSV and displays a preview of the rubric structure (template name, criteria list with max_scores, score levels).
5. Coordinator reviews the preview for accuracy.
6. Coordinator clicks "Import".
7. System creates the `rubric_template`, all `criteria`, and all `score_levels` records.
8. System auto-calculates `total_marks`.
9. System displays success notification with summary (rubric name, criteria count, score level count).

**Postconditions:**
- Complete rubric template with all criteria and score levels exists in the pool.

**Alternative Flows:**
- **AF1 — Parse Error:** If CSV format is invalid, system displays validation errors with specific row/column references. Import is aborted.
- **AF2 — Partial Failure:** If some rows fail validation but others succeed, system reports which rows failed and which succeeded. (Decision: import all-or-nothing vs. partial is TBD.)

**Business Rules:**
- Only one rubric per CSV file.
- CSV format/columns are TBD (to be defined during implementation).

---

### UC-CO-03: Create Phase Template

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | At least one rubric template exists in the pool |
| **Trigger** | Coordinator navigates to Template Pool → Phase Templates |

**Main Flow:**
1. Coordinator navigates to Template Pool → Phase Templates → Create.
2. Coordinator enters the phase template name (e.g., "B.Tech Phase I") and total_phase_marks (e.g., 100).
3. Coordinator saves. System creates the `phase_templates` record.
4. System redirects to the edit page with the Phase Rubric Rules RelationManager.
5. Coordinator clicks "Create" in the Rules section.
6. Coordinator configures each rule:
   - Select rubric template from pool dropdown
   - Enter evaluator_role (e.g., "Reviewer" or "Supervisor")
   - Enter fill_order (e.g., 1, 2, 3)
   - Enter max_marks (e.g., 10, 20, 30)
   - Select aggregation_method (AVERAGE, WEIGHTED_AVERAGE, SUM, MAX)
7. Coordinator saves the rule.
8. Coordinator repeats steps 5-7 for all rubrics in the phase.

**Postconditions:**
- Phase template blueprint exists with all rubric-phase rules defined.
- Ready to be assigned to projects during semester setup.

**Alternative Flows:**
- **AF1 — Edit Existing:** Coordinator edits a phase template that is not yet used by any project in "evaluating" status.

**Business Rules:**
- fill_order must be sequential integers (1, 2, 3...).
- The sum of max_marks across all rules should equal total_phase_marks (validated or warned).
- Rubric templates referenced in rules become locked once an evaluation is created against them.

---

### UC-CO-04: Create Semester

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Coordinator is logged into the Admin Panel |
| **Trigger** | Coordinator navigates to Academic Setup → Semesters |

**Main Flow:**
1. Coordinator navigates to Academic Setup → Semesters → Create.
2. Coordinator enters: name (e.g., "Fall 2026"), academic_year (e.g., "2025-2026"), start_date, end_date.
3. Coordinator clicks "Create".
4. System creates the `semesters` record with is_active=true, is_closed=false.
5. System automatically creates a `coordinator_semester` pivot record linking the current coordinator to the new semester.

**Postconditions:**
- Active semester exists.
- Current coordinator is assigned to it.
- Semester is ready for project creation.

**Alternative Flows:**
- **AF1 — Close Semester:** Coordinator clicks "Close Semester" action, confirming the dialog. System sets is_closed=true, making all data read-only.
- **AF2 — Deactivate:** Coordinator toggles is_active to false (semester is hidden from active views but data remains).

**Business Rules:**
- Multiple semesters can be active simultaneously.
- A coordinator can only manage semesters they are assigned to.
- Closing a semester prevents any further evaluation submissions or mark changes.

---

### UC-CO-05: Bulk Import Users

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Coordinator has a CSV file with user data |
| **Trigger** | Coordinator navigates to Bulk Import → Users tab |

**Main Flow:**
1. Coordinator navigates to Tools → Bulk Import → Import Users tab.
2. Coordinator downloads the CSV template.
3. Coordinator uploads the CSV file (columns: university_id, name, email, role).
4. System parses and validates the CSV, displaying a preview table.
5. Coordinator reviews the preview.
6. Coordinator clicks "Import".
7. System creates user records, assigns specified roles, and generates temporary passwords (or sets default password).
8. System displays summary: X users created, Y errors.

**Postconditions:**
- User accounts exist with assigned roles.
- Users can log in with their credentials.

**Alternative Flows:**
- **AF1 — Duplicate Detection:** If university_id or email already exists, system skips the duplicate row and reports it in the error summary.
- **AF2 — Invalid Role:** If a specified role is invalid, system reports the error for that row.

**Business Rules:**
- Imported users receive the role specified in the CSV.
- Coordinator cannot import users with the `super_admin` or `coordinator` role.
- Passwords must meet the system's password policy.

---

### UC-CO-06: Create Project and Assign Group

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Semester exists; courses, phase templates, and students exist in the system |
| **Trigger** | Coordinator navigates to Academic Setup → Projects |

**Main Flow:**
1. Coordinator navigates to Academic Setup → Projects → Create.
2. Coordinator fills the project form:
   - Title (e.g., "AI-Based Assessment System")
   - Semester (select)
   - Course (select)
   - Phase Template (select from pool)
   - Specialization (select)
   - Supervisor (select from users with supervisor role)
3. Coordinator saves. System creates the `projects` record with status="setup".
4. System redirects to the project view/edit page with RelationManagers.
5. Coordinator navigates to the Students tab.
6. Coordinator clicks "Attach" and selects 1-4 students. System creates `project_student` pivot records.
7. Coordinator navigates to the Reviewers tab.
8. Coordinator clicks "Attach" and selects one or more reviewers. System creates `project_reviewer` pivot records.

**Postconditions:**
- Project exists with status "setup".
- Students (1-4), one supervisor, and one or more reviewers are assigned.

**Alternative Flows:**
- **AF1 — Bulk CSV Import:** Coordinator uploads a CSV that creates projects and assigns students, supervisor, and reviewers in one operation (see Bulk Import page).
- **AF2 — Link to Phase 1:** For Phase 2 projects, Coordinator selects a Phase 1 project via previous_phase_project_id.

**Business Rules:**
- Maximum 4 students per project.
- Each student can only belong to one project per semester.
- Exactly one supervisor per project.
- Supervisor cannot be assigned as reviewer for the same project.
- One or more reviewers per project (flexible count).

---

### UC-CO-07: Assign Reviewers to Project

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Project exists with a supervisor assigned |
| **Trigger** | Coordinator navigates to project's Reviewers RelationManager |

**Main Flow:**
1. Coordinator opens the project view/edit page.
2. Coordinator navigates to the Reviewers tab.
3. Coordinator clicks "Attach".
4. System displays a searchable dropdown of users with the reviewer role, excluding the project's supervisor.
5. Coordinator selects one or more reviewers.
6. System creates `project_reviewer` pivot records.
7. System displays success notification.

**Postconditions:**
- Selected reviewer(s) are linked to the project.
- Reviewers can see this project in their Staff Panel.

**Alternative Flows:**
- **AF1 — Remove Reviewer:** Coordinator clicks "Detach" on an existing reviewer. System removes the `project_reviewer` record. (Only allowed if the reviewer has not yet submitted any evaluations for this project.)
- **AF2 — Change Reviewer:** Coordinator detaches current reviewer and attaches a new one.

**Business Rules:**
- Reviewer cannot be the same person as the project's supervisor.
- If a reviewer has already submitted evaluations, they cannot be detached (warn coordinator).

---

### UC-CO-08: Monitor Assessment Progress

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Active semester with projects in "evaluating" status |
| **Trigger** | Coordinator navigates to Dashboard or Evaluation list |

**Main Flow:**
1. Coordinator views the Admin Dashboard widgets showing pending evaluations, project status distribution, and submission progress.
2. Coordinator navigates to Assessment Monitoring → Evaluations for detailed view.
3. System displays a filterable table of all evaluations with: project title, rubric name, evaluator name, role, status (pending/draft/submitted).
4. Coordinator filters by semester, status, or evaluator to identify outstanding evaluations.
5. Coordinator identifies which evaluators have not yet submitted.

**Postconditions:**
- Coordinator has visibility into assessment progress across all projects.

**Alternative Flows:**
- **AF1 — No Pending:** All evaluations are submitted. Dashboard shows 100% completion.

**Business Rules:**
- Consolidated marks auto-calculation only fires when ALL evaluations for a project reach "submitted" status.
- Project status progresses: setup → evaluating → completed.

---

### UC-CO-09: Unlock Submitted Assessment

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | An evaluation exists with status="submitted" |
| **Trigger** | Coordinator clicks "Unlock" action on an evaluation |

**Main Flow:**
1. Coordinator navigates to Assessment Monitoring → Evaluations.
2. Coordinator finds the submitted evaluation that needs unlocking.
3. Coordinator clicks the "Unlock" action button.
4. System displays a confirmation dialog: "Are you sure you want to unlock this evaluation? The evaluator will be able to edit their marks."
5. Coordinator confirms.
6. System changes the evaluation status from "submitted" to "draft".
7. System records `unlocked_by` with the current coordinator's user ID.
8. System logs the event via Spatie Activitylog (who unlocked, when, which evaluation).
9. System displays success notification.

**Postconditions:**
- Evaluation is editable again by the original evaluator.
- Audit trail records who unlocked and when.
- If consolidated marks had already been calculated, they may need recalculation after the evaluator re-submits.

**Alternative Flows:**
- **AF1 — Cancel:** Coordinator clicks "Cancel" on the confirmation dialog. No changes are made.

**Business Rules:**
- Only Coordinators can unlock evaluations.
- Every unlock must be audit-logged (Spatie Activitylog).
- If consolidated marks already existed, they should be flagged as potentially stale.

---

### UC-CO-10: Proxy Mark Entry

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | An evaluator is absent; a pending/draft evaluation exists for a project |
| **Trigger** | Coordinator initiates proxy marking from the Evaluation list |

**Main Flow:**
1. Coordinator navigates to Assessment Monitoring → Evaluations.
2. Coordinator selects the evaluation for the absent evaluator.
3. Coordinator clicks "Proxy Mark Entry" action.
4. System opens the evaluation form (same as S-05 in Staff Panel) with:
   - `evaluator_id` remaining as the original assigned evaluator.
   - `on_behalf_of_user_id` set to the current coordinator.
5. Coordinator fills the rubric form with group and individual scores.
6. Coordinator optionally uploads an evidence attachment (scanned physical rubric).
7. Coordinator saves as draft or submits.
8. System logs the proxy marking event via Spatie Activitylog.

**Postconditions:**
- Evaluation has marks entered.
- `on_behalf_of_user_id` records the coordinator who entered the marks.
- `evidence_attachment_path` stores the scanned evidence (if uploaded).
- Audit trail records the proxy event.

**Alternative Flows:**
- **AF1 — Save Draft:** Coordinator saves as draft to return later and complete the entry.

**Business Rules:**
- Only Coordinators can perform proxy marking.
- The original `evaluator_id` is preserved (marks are attributed to the assigned evaluator).
- Proxy events must be audit-logged.

---

### UC-CO-11: Override Consolidated Mark

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Consolidated mark exists (auto-calculated) |
| **Trigger** | Coordinator clicks "Override" action on a consolidated mark |

**Main Flow:**
1. Coordinator navigates to Grade Consolidation → Consolidated Marks.
2. Coordinator finds the student's consolidated mark record.
3. Coordinator clicks the "Override Mark" action.
4. System displays a modal form with:
   - Current calculated score (display-only)
   - `override_score` — numeric input (required)
   - `override_reason` — textarea (required)
5. Coordinator enters the new score and reason.
6. Coordinator clicks "Save Override".
7. System saves the override_score and override_reason. The original total_calculated_score is preserved.
8. System logs the override via Spatie Activitylog.

**Postconditions:**
- `override_score` and `override_reason` are populated.
- `total_calculated_score` remains unchanged.
- The student's displayed final score becomes the override_score.
- Audit trail records the override.

**Alternative Flows:**
- **AF1 — Remove Override:** Coordinator clears the override, reverting to the calculated score. (Set override_score and override_reason to null.)

**Business Rules:**
- override_reason is mandatory when override_score is provided.
- The original calculated score is never deleted or modified.
- Override events must be audit-logged.

---

### UC-CO-12: Export Grades for SIS

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Consolidated marks are finalized for a semester |
| **Trigger** | Coordinator navigates to Reports & Export → Grade Export |

**Main Flow:**
1. Coordinator navigates to Reports & Export → Grade Export.
2. Coordinator selects a semester from the dropdown.
3. Coordinator optionally filters by course.
4. System displays a preview table with: university_id, student name, project title, calculated score, override score (if any), final score, letter grade, GPA.
5. Coordinator clicks "Export CSV" or "Export PDF".
6. System generates and downloads the file.

**Postconditions:**
- Downloaded file containing final grades ready for SIS upload.

**Alternative Flows:**
- **AF1 — No Data:** If no consolidated marks exist for the selected semester, system shows "No data available" message.

**Business Rules:**
- Export includes both calculated and override scores (if any).
- Letter grade and GPA are derived from the grading_scales table.

---

### UC-CO-13: Generate PDF Report

| Field | Value |
|-------|-------|
| **Actor** | Coordinator |
| **Preconditions** | Consolidated marks exist for target project(s) |
| **Trigger** | Coordinator requests PDF generation from Grade Export or Consolidated Marks |

**Main Flow:**
1. Coordinator navigates to a consolidated mark record or Grade Export page.
2. Coordinator clicks "Generate PDF" action.
3. System generates a formatted PDF containing:
   - Institution header (UTAS, Nizwa; Department; Course)
   - Student info (university_id, name)
   - Project title
   - Score breakdown by rubric source (supervisor total, reviewer total)
   - Final score, letter grade, GPA
4. System provides the PDF for download.

**Postconditions:**
- PDF file is generated and downloadable.
- PDF can be used as official academic record.

**Business Rules:**
- PDF format should align with institutional standards.

---

## 4. Supervisor Use Cases

### UC-SU-01: View Supervised Projects

| Field | Value |
|-------|-------|
| **Actor** | Supervisor |
| **Preconditions** | Supervisor is logged into the Staff Panel; has projects assigned as supervisor |
| **Trigger** | Supervisor navigates to Dashboard or Supervised Projects list |

**Main Flow:**
1. Supervisor logs into the Staff Panel (`/staff`).
2. System displays the Dashboard with the "Projects I am Supervising" widget.
3. Supervisor sees a table of their supervised projects: title, semester, student names, evaluation progress.
4. Supervisor clicks on a project to view full detail (see UC-SU-04).

**Postconditions:**
- Supervisor has visibility into all projects where they are the assigned supervisor.

**Business Rules:**
- Supervisor can only see projects where `projects.supervisor_id` matches their user ID.
- If the supervisor also has a reviewer role, the "Projects I am Reviewing" widget appears separately.

---

### UC-SU-02: Fill Assessment Form

| Field | Value |
|-------|-------|
| **Actor** | Supervisor |
| **Preconditions** | Project is in "evaluating" status; evaluation record exists for this supervisor; prerequisite fill_order rubrics are submitted (if applicable) |
| **Trigger** | Supervisor clicks "Fill Assessment" from their project list |

**Main Flow:**
1. Supervisor navigates to their supervised project.
2. Supervisor clicks "Fill Assessment" for the current rubric.
3. System renders the dynamic evaluation form based on the rubric template:
   - Header: Project title, rubric name, "Supervisor" role context
   - **Group Criteria section:** For each criterion where is_individual=false:
     - Criterion title and description
     - Score input: dropdown of score levels OR manual numeric entry (0 to max_score)
     - Feedback textarea (optional)
   - **Individual Criteria section:** For each criterion where is_individual=true:
     - Criterion title and description
     - For each student in the project:
       - Student name label
       - Score input: dropdown or manual entry
       - Feedback textarea (optional)
   - General feedback textarea
4. Supervisor fills scores and feedback for all criteria.
5. Supervisor clicks "Save Draft" or "Submit" (see UC-SU-03).

**Postconditions:**
- Evaluation scores are stored in `evaluation_scores` table.

**Alternative Flows:**
- **AF1 — Fill Order Not Met:** If prerequisite rubrics at earlier fill_order are not yet submitted, system shows a message: "Previous assessment must be completed first." Form is not accessible.
- **AF2 — Resume Draft:** If supervisor previously saved a draft, the form is pre-populated with saved scores.

**Business Rules:**
- Score must be between 0 and the criterion's max_score.
- For group criteria: one `evaluation_scores` row per criterion (student_id=NULL).
- For individual criteria: one `evaluation_scores` row per criterion per student.
- Score can be selected from predefined score_levels OR entered manually.

---

### UC-SU-03: Save Draft / Submit Assessment

| Field | Value |
|-------|-------|
| **Actor** | Supervisor |
| **Preconditions** | Evaluation form has been partially or fully filled |
| **Trigger** | Supervisor clicks "Save Draft" or "Submit" |

**Main Flow (Save Draft):**
1. Supervisor clicks "Save Draft".
2. System persists all entered scores to `evaluation_scores`.
3. System sets evaluation status to "draft".
4. System displays success notification: "Draft saved."
5. Supervisor can return later to continue editing.

**Main Flow (Submit):**
1. Supervisor clicks "Submit".
2. System validates that all required criteria have scores.
3. System displays confirmation dialog: "Once submitted, this assessment will be locked. Continue?"
4. Supervisor confirms.
5. System sets evaluation status to "submitted".
6. System locks the evaluation (all form fields become read-only).
7. System displays success notification: "Assessment submitted successfully."

**Postconditions:**
- Draft: Evaluation is editable, status="draft".
- Submit: Evaluation is locked, status="submitted". Only a Coordinator can unlock it.

**Alternative Flows:**
- **AF1 — Validation Failure on Submit:** If any required criterion is missing a score, system highlights the missing fields and prevents submission.
- **AF2 — Cancel Submit:** Supervisor clicks "Cancel" on the confirmation dialog. No status change occurs.

**Business Rules:**
- Submission requires all criteria to have a score.
- Submission is irreversible without Coordinator unlock (UC-CO-09).
- Scores must be within valid range (0 to max_score).

---

### UC-SU-04: View Project Evaluation Progress

| Field | Value |
|-------|-------|
| **Actor** | Supervisor |
| **Preconditions** | Project has evaluations in various states |
| **Trigger** | Supervisor clicks on a project from their list |

**Main Flow:**
1. Supervisor clicks on a project from the "Projects I am Supervising" list.
2. System displays the Project Detail View:
   - Project info (title, course, semester, phase template)
   - Team members table (student names, university IDs)
   - Evaluation status table showing:
     - All rubrics in the phase (from phase_rubric_rules)
     - Which evaluators are assigned (supervisor, reviewers)
     - Each evaluator's submission status (pending/draft/submitted)
3. Supervisor can see which assessments are still outstanding.

**Postconditions:**
- Supervisor has visibility into the overall evaluation progress for their project.

**Business Rules:**
- Supervisor sees evaluation status but NOT the actual scores entered by reviewers.

---

## 5. Reviewer Use Cases

### UC-RE-01: View Review Assignments

| Field | Value |
|-------|-------|
| **Actor** | Reviewer |
| **Preconditions** | Reviewer is logged into the Staff Panel; has projects assigned as reviewer |
| **Trigger** | Reviewer navigates to Dashboard or Review Assignments list |

**Main Flow:**
1. Reviewer logs into the Staff Panel (`/staff`).
2. System displays the Dashboard with the "Projects I am Reviewing" widget.
3. Reviewer sees a table of assigned projects: title, semester, rubric to fill, fill_order, their evaluation status (pending/draft/submitted).
4. Reviewer clicks "Fill Assessment" to begin grading (see UC-RE-02).

**Postconditions:**
- Reviewer has visibility into all projects assigned to them for review.

**Business Rules:**
- Reviewer can only see projects where they appear in `project_reviewer`.
- If the reviewer also has a supervisor role, the "Projects I am Supervising" widget appears separately, clearly distinguished.

---

### UC-RE-02: Fill Assessment Form

| Field | Value |
|-------|-------|
| **Actor** | Reviewer |
| **Preconditions** | Project is in "evaluating" status; evaluation record exists for this reviewer; prerequisite fill_order rubrics are submitted |
| **Trigger** | Reviewer clicks "Fill Assessment" from their assignment list |

**Main Flow:**
1. Reviewer clicks "Fill Assessment" for the assigned rubric.
2. System renders the dynamic evaluation form (same as UC-SU-02 but in the reviewer context).
3. Header shows: Project title, rubric name, "Reviewer" role context.
4. Reviewer fills group criteria scores (one per project) and individual criteria scores (one per student).
5. Reviewer adds feedback as needed.
6. Reviewer saves draft or submits.

**Postconditions:**
- Same as UC-SU-02.

**Alternative Flows:**
- Same as UC-SU-02 (fill order not met, resume draft).

**Business Rules:**
- Same scoring rules as UC-SU-02.
- Multiple reviewers fill the same rubric independently for the same project. Their scores are later aggregated during consolidation (UC-SY-02).

---

### UC-RE-03: Save Draft / Submit Assessment

| Field | Value |
|-------|-------|
| **Actor** | Reviewer |
| **Preconditions** | Evaluation form has been partially or fully filled |
| **Trigger** | Reviewer clicks "Save Draft" or "Submit" |

**Main Flow:** Same as UC-SU-03.

**Postconditions:** Same as UC-SU-03.

**Alternative Flows:** Same as UC-SU-03.

**Business Rules:** Same as UC-SU-03.

---

## 6. Student Use Cases

### UC-ST-01: View Internal Marks

| Field | Value |
|-------|-------|
| **Actor** | Student |
| **Preconditions** | Student is logged into the Student Panel; at least one supervisor evaluation is submitted |
| **Trigger** | Student navigates to My Marks |

**Main Flow:**
1. Student logs into the Student Panel (`/student`).
2. Student navigates to "My Marks" page.
3. If the student has been in multiple semesters, they select a semester from the dropdown.
4. System displays the "Internal Marks" section showing supervisor evaluation scores:
   - Rubric name (e.g., "Review I - Supervisor - 10 marks")
   - Criterion-by-criterion breakdown with score and feedback
   - For group criteria: the group score is shown
   - For individual criteria: the student's personal score is shown
   - Subtotal per rubric
5. Student reviews their internal marks and feedback.

**Postconditions:**
- Student sees their supervisor-assessed marks with detailed feedback.

**Alternative Flows:**
- **AF1 — No Marks Yet:** If no supervisor evaluations are submitted, system shows: "Internal marks not yet available."

**Business Rules:**
- Students see marks from supervisor evaluations only (evaluator_role = "Supervisor").
- Students see group criterion scores plus their own individual scores.
- Students see per-criterion feedback if provided.
- All data is read-only.

---

### UC-ST-02: View Consolidated Marks

| Field | Value |
|-------|-------|
| **Actor** | Student |
| **Preconditions** | Consolidated marks have been calculated for the student's project |
| **Trigger** | Student navigates to My Marks → Consolidated Marks section |

**Main Flow:**
1. Student navigates to "My Marks" page.
2. System displays the "Consolidated Marks" section:
   - Component breakdown table from `consolidated_mark_components`:
     - Source labels (e.g., "Supervisor Total", "Reviewer Total") with scores
   - Total calculated score
   - Final score (override if exists, otherwise calculated)
   - Letter grade and GPA equivalent (from grading_scales)
3. Student reviews their final grade.

**Postconditions:**
- Student sees their final consolidated marks.

**Alternative Flows:**
- **AF1 — Not Yet Available:** If consolidated marks have not been calculated, system shows: "Final marks not yet available."
- **AF2 — Override Applied:** If coordinator has overridden the mark, the final score shows the override value. The student sees the final score only (not the original calculated score or override reason).

**Business Rules:**
- Students see consolidated marks but NOT individual reviewer identities or individual reviewer score breakdowns.
- Students see source labels (e.g., "Supervisor Total", "Reviewer Total") but not who the reviewers are.
- If override exists, student sees the override score as their final score.
- All data is read-only.

---

## 7. System (Automated) Use Cases

### UC-SY-01: Auto-Generate Evaluation Records

| Field | Value |
|-------|-------|
| **Actor** | System |
| **Trigger** | Project status changes from "setup" to "evaluating" |

**Main Flow:**
1. Coordinator changes a project's status to "evaluating" (either manually or via a bulk action).
2. System reads the project's `phase_template_id` to get the associated `phase_rubric_rules`.
3. For each rule in the phase:
   - System identifies the evaluator(s) based on `evaluator_role`:
     - If "Supervisor": creates one evaluation for `projects.supervisor_id`
     - If "Reviewer": creates one evaluation for each user in `project_reviewer`
   - System creates evaluation records with:
     - `project_id` = current project
     - `rubric_template_id` = from the rule
     - `evaluator_id` = identified evaluator
     - `evaluator_role` = from the rule
     - `status` = "pending"
4. All evaluation records are created.

**Postconditions:**
- All required evaluation records exist for the project with status="pending".
- Evaluators can see their assigned evaluations in the Staff Panel.

**Business Rules:**
- One evaluation record per (project, rubric_template, evaluator) combination (enforced by UNIQUE constraint).
- Evaluations are created for all rubric-phase rules in the phase template.

---

### UC-SY-02: Auto-Calculate Consolidated Marks

| Field | Value |
|-------|-------|
| **Actor** | System |
| **Trigger** | All evaluations for a project reach "submitted" status |

**Main Flow:**
1. System detects that the last evaluation for a project is submitted.
2. System reads the project's phase_rubric_rules.
3. For each rule:
   - System collects all evaluation_scores for the rubric from all evaluators.
   - If multiple evaluators filled the same rubric:
     - System applies the configured aggregation_method (AVERAGE by default).
   - If only one evaluator: no aggregation needed.
4. For each student in the project:
   - System computes the student's total score:
     - Group criteria scores are shared (same score for all students).
     - Individual criteria scores are student-specific.
   - System sums across all rubric rules, applying max_marks weighting.
5. System creates `consolidated_marks` record per student:
   - `total_calculated_score` = computed total
   - `override_score` = NULL
6. System creates `consolidated_mark_components` records:
   - One entry per rubric source (e.g., "Supervisor Review I: 9.0", "Reviewer Final: 25.5").
7. System transitions project status to "completed".

**Postconditions:**
- Consolidated mark records exist for each student in the project.
- Component breakdown is stored for transparency.
- Project status is "completed".

**Business Rules:**
- Calculation only triggers when ALL evaluations reach "submitted".
- If a submitted evaluation is later unlocked (UC-CO-09) and re-submitted, consolidated marks should be recalculated.
- Group criterion scores contribute equally to all students.
- Individual criterion scores are specific to each student.

---

### UC-SY-03: Lock Rubric Template on First Use

| Field | Value |
|-------|-------|
| **Actor** | System |
| **Trigger** | An evaluation record is created referencing a rubric_template |

**Main Flow:**
1. System creates an evaluation record (via UC-SY-01).
2. System checks the referenced `rubric_template.is_locked`.
3. If `is_locked` is false:
   - System sets `is_locked = true`.
   - The rubric template can no longer be edited.

**Postconditions:**
- Rubric template is locked, preserving grading integrity.
- Coordinator must clone the template to make changes (creating a new version).

**Business Rules:**
- Once locked, a rubric template's criteria and score levels cannot be modified.
- Cloning creates a new unlocked version with parent_template_id linkage.

---

### UC-SY-04: Send Email Notifications

| Field | Value |
|-------|-------|
| **Actor** | System |
| **Trigger** | Various system events |

**Notification Events:**

| Event | Recipients | Content |
|-------|-----------|---------|
| Evaluator assigned to project | Supervisor / Reviewer | "You have been assigned to evaluate [Project Title]" |
| Evaluation submitted | Coordinator | "[Evaluator Name] has submitted their assessment for [Project Title]" |
| All evaluations submitted (project complete) | Coordinator | "All assessments for [Project Title] are complete. Consolidated marks are ready." |
| Assessment unlocked | Original Evaluator | "Your assessment for [Project Title] has been unlocked by [Coordinator Name]. You can now edit your marks." |
| Consolidated marks finalized | Student | "Your final marks for [Project Title] are now available." |

**Main Flow:**
1. System event occurs (e.g., evaluation submitted).
2. System dispatches a Laravel Notification to the appropriate user(s).
3. Email is sent via the configured mail driver.

**Postconditions:**
- Email delivered to recipient(s).

**Business Rules:**
- Email is a "should-have" feature (not blocking for MVP).
- Notification delivery uses Laravel's built-in Notification system.
- Failed deliveries should be logged but not block the triggering action.
