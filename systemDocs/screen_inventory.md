# AMS Screen Inventory (FilamentPHP v5)

> **Version:** 1.0
> **Date:** 2026-03-01
> **Status:** Draft
> **UI Framework:** FilamentPHP v5.3 (Livewire-based admin panel builder)

---

## 1. Panel Architecture

### 1.1 Decision: Three Separate Filament Panels

The AMS uses three isolated Filament panels, each with its own URL path, PanelProvider, and navigation structure.

| Panel | URL Path | Roles | Purpose |
|-------|----------|-------|---------|
| **Admin Panel** | `/admin` | Super Admin, Coordinator | System configuration, template management, semester setup, monitoring, exports |
| **Staff Panel** | `/staff` | Supervisor, Reviewer | Assessment filling, project viewing, limited dashboards |
| **Student Panel** | `/student` | Student | Read-only mark viewing |

### 1.2 Rationale
1. **Security by separation** — Students cannot accidentally access admin routes or staff grading forms.
2. **Tailored UX** — Each panel shows only relevant screens for that role category.
3. **Dual-role handling** — A user who is both Supervisor and Reviewer accesses the Staff Panel where the dashboard separates those responsibilities. If they are also a Coordinator, they can access the Admin Panel with the same credentials at a different URL.
4. **Native FilamentPHP architecture** — v5 is built around multi-panel support via separate `PanelProvider` classes in `app/Providers/Filament/`.

### 1.3 Implementation Files
```
app/Providers/Filament/
├── AdminPanelProvider.php
├── StaffPanelProvider.php
└── StudentPanelProvider.php
```

### 1.4 Shared Authentication
All panels share the same `users` table and Laravel authentication. Panel access is controlled via Filament's `->authMiddleware()` combined with custom middleware that checks the user's spatie roles against the panel's allowed roles.

---

## 2. Admin Panel Screens

### Navigation Sidebar Structure
```
Admin Panel (/admin)
├── Dashboard
├── Master Data
│   ├── Departments
│   ├── Specializations
│   ├── Courses
│   └── Grading Scales
├── User Management
│   └── Users
├── Template Pool
│   ├── Rubric Templates
│   └── Phase Templates
├── Academic Setup
│   ├── Semesters
│   └── Projects
├── Assessment Monitoring
│   └── Evaluations
├── Grade Consolidation
│   └── Consolidated Marks
├── Tools
│   └── Bulk Import
└── Reports & Export
    └── Grade Export
```

---

### A-01: Admin Dashboard

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page (`Pages/Dashboard.php`) |
| **Purpose** | High-level system overview for coordinators and super admins |
| **Navigation** | Top-level (default landing page) |
| **Access** | Super Admin, Coordinator |

**Widgets:**

| Widget | Type | Content |
|--------|------|---------|
| Pending Evaluations | `StatsOverviewWidget` | Count of evaluations in pending/draft state for active semesters |
| Project Status | `ChartWidget` (Pie) | Distribution of projects by status (setup / evaluating / completed) |
| Submission Progress | `ChartWidget` (Bar) | Per-semester evaluation completion percentage |
| Recent Activity | `TableWidget` | Last N entries from Spatie Activitylog (mark changes, unlocks, overrides) |

---

### A-02: DepartmentResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource |
| **Purpose** | CRUD for academic departments |
| **Navigation** | Sidebar → Master Data → Departments |
| **Access** | Super Admin |
| **Tables** | `departments`, `specializations` |
| **Related Screens** | A-03 (Specializations) |

**List Page:**
- Columns: `name`, specialization count (computed), `created_at`
- Actions: Edit, Delete (soft)
- Bulk Actions: Bulk delete

**Form (Create/Edit):**
- `name` — TextInput (required)

**RelationManagers:**
- `SpecializationsRelationManager` — inline CRUD for specializations under this department
  - Columns: `name`, user count
  - Form: `name` TextInput

---

### A-03: SpecializationResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource (standalone + appears as RelationManager under Department) |
| **Purpose** | CRUD for specializations |
| **Navigation** | Sidebar → Master Data → Specializations |
| **Access** | Super Admin |
| **Tables** | `specializations`, `departments` |

**List Page:**
- Columns: `name`, department name (relationship), user count
- Filters: Department

**Form (Create/Edit):**
- `name` — TextInput (required)
- `department_id` — Select (relationship to departments)

---

### A-04: CourseResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource |
| **Purpose** | CRUD for course codes and titles |
| **Navigation** | Sidebar → Master Data → Courses |
| **Access** | Super Admin |
| **Tables** | `courses` |

**List Page:**
- Columns: `code`, `title`, project count
- Searchable: `code`, `title`

**Form (Create/Edit):**
- `code` — TextInput (required)
- `title` — TextInput (required)

---

### A-05: GradingScaleResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource |
| **Purpose** | CRUD for grade-to-score mappings |
| **Navigation** | Sidebar → Master Data → Grading Scales |
| **Access** | Super Admin |
| **Tables** | `grading_scales` |

**List Page:**
- Columns: `letter_grade`, `min_score`, `max_score`, `gpa_equivalent`
- Sorted by: `min_score` descending (A+ at top)

**Form (Create/Edit):**
- `letter_grade` — TextInput (e.g., "A", "B+")
- `min_score` — TextInput (numeric)
- `max_score` — TextInput (numeric)
- `gpa_equivalent` — TextInput (numeric)
- Validation: ranges must not overlap with existing entries

---

### A-06: UserResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource |
| **Purpose** | Manage all system users and their roles |
| **Navigation** | Sidebar → User Management → Users |
| **Access** | Super Admin (full CRUD), Coordinator (CRUD for non-admin users via policy) |
| **Tables** | `users`, spatie permission tables |

**List Page:**
- Columns: `university_id`, `name`, `email`, roles (badge column), specialization (relationship)
- Filters: Role, Specialization
- Searchable: `university_id`, `name`, `email`
- Header Action: **"Import CSV"** button (opens modal or redirects to A-13)

**Form (Create/Edit):**
- `university_id` — TextInput (required, unique)
- `name` — TextInput (required)
- `email` — TextInput (required, unique, email validation)
- `password` — TextInput (required on create, optional on edit, hashed)
- `specialization_id` — Select (nullable, relationship to specializations)
- `roles` — CheckboxList (spatie roles: coordinator, supervisor, reviewer, student)

**Policy Notes:**
- Super Admin can assign all roles including `coordinator`
- Coordinator cannot assign `super_admin` or `coordinator` roles

---

### A-07: RubricTemplateResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource (most complex resource in the system) |
| **Purpose** | Manage the rubric template pool |
| **Navigation** | Sidebar → Template Pool → Rubric Templates |
| **Access** | Coordinator |
| **Tables** | `rubric_templates`, `criteria`, `score_levels` |
| **Related** | A-08 (Phase Templates) |

**List Page:**
- Columns: `name`, `version`, `total_marks` (computed), `is_locked` (IconColumn/Badge), `created_by` (relationship), parent template name (version lineage)
- Filters: Locked status, created_by
- Searchable: `name`
- Header Actions:
  - **"Import from CSV"** — opens CSV upload modal

**Record Actions:**
- **"Clone"** — creates new version (increments version, sets parent_template_id, duplicates all criteria + score levels)
- **"Lock"** — manually locks template (confirmation dialog)
- Edit (disabled when is_locked=true)
- Delete (soft, disabled when is_locked=true)

**Form (Create/Edit):**
- `name` — TextInput (required)
- `total_marks` — Placeholder/Disabled (auto-calculated from criteria sum, display-only)

**RelationManager: CriteriaRelationManager**
- Table columns: `title`, `max_score`, `is_individual` (ToggleColumn or IconColumn), score levels count
- Record actions: Edit, Delete
- Create/Edit form fields:
  - `title` — TextInput (required)
  - `description` — Textarea (nullable)
  - `max_score` — TextInput (numeric, required)
  - `is_individual` — Toggle (default: false; label: "Individual scoring per student")
  - **Score Levels** — `Repeater` field within the criterion form:
    - `label` — TextInput (e.g., "Excellent", "Very Good")
    - `score_value` — TextInput (numeric)
    - `description` — Textarea (nullable, descriptive text)
    - `sort_order` — Hidden/auto-set from repeater order
  - Optional Action: **"Save Criterion to Pool"** — saves this criterion as reusable (separate from rubric template)

**Architecture Note:** FilamentPHP v5 does not support nested RelationManagers. Score levels are managed via a `Repeater` component inside the criterion create/edit form, not as a separate RelationManager.

---

### A-08: PhaseTemplateResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource |
| **Purpose** | Manage phase template blueprints |
| **Navigation** | Sidebar → Template Pool → Phase Templates |
| **Access** | Coordinator |
| **Tables** | `phase_templates`, `phase_rubric_rules` |
| **Related** | A-07 (Rubric Templates) |

**List Page:**
- Columns: `name`, `total_phase_marks`, `created_by` (relationship), rubric rule count
- Searchable: `name`

**Form (Create/Edit):**
- `name` — TextInput (required)
- `total_phase_marks` — TextInput (numeric, required)

**RelationManager: PhaseRubricRulesRelationManager**
- Table columns: rubric template name (relationship), `evaluator_role`, `fill_order`, `max_marks`, `aggregation_method`
- Sorted by: `fill_order` ascending
- Create/Edit form fields:
  - `rubric_template_id` — Select (from rubric template pool, searchable)
  - `evaluator_role` — TextInput (flexible string, e.g., "Supervisor", "Reviewer")
  - `fill_order` — TextInput (integer, required)
  - `max_marks` — TextInput (numeric, required)
  - `aggregation_method` — Select (enum: AVERAGE, WEIGHTED_AVERAGE, SUM, MAX; default: AVERAGE)

---

### A-09: SemesterResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource |
| **Purpose** | Manage academic semesters |
| **Navigation** | Sidebar → Academic Setup → Semesters |
| **Access** | Super Admin (create + assign coordinators), Coordinator (manage their own semesters) |
| **Tables** | `semesters`, `coordinator_semester` |
| **Related** | A-10 (Projects) |

**List Page:**
- Columns: `name`, `academic_year`, `start_date`, `end_date`, `is_active` (ToggleColumn), `is_closed` (IconColumn/Badge), project count
- Filters: is_active, is_closed, academic_year

**Record Actions:**
- **"Close Semester"** — sets is_closed=true (confirmation dialog with warning)
- Edit, Delete (soft)

**Form (Create/Edit):**
- `name` — TextInput (e.g., "Fall 2026")
- `academic_year` — TextInput (e.g., "2025-2026")
- `start_date` — DatePicker (nullable)
- `end_date` — DatePicker (nullable)

**RelationManagers:**
- `CoordinatorsRelationManager` — manage coordinator_semester pivot
  - Attach/Detach coordinators (Select filtered to users with coordinator role)
- `ProjectsRelationManager` — list projects in this semester
  - Table: title, course, supervisor, status, student count
  - Link: Opens project in A-10

---

### A-10: ProjectResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource |
| **Purpose** | Manage projects within semesters |
| **Navigation** | Sidebar → Academic Setup → Projects |
| **Access** | Coordinator |
| **Tables** | `projects`, `project_student`, `project_reviewer` |
| **Related** | A-09 (Semesters), A-11 (Evaluations) |

**List Page:**
- Columns: `title`, semester (relationship), course (relationship), specialization, supervisor (relationship), `status` (BadgeColumn), student count, reviewer count
- Filters: Semester, Status, Course, Supervisor
- Searchable: `title`
- Header Action: **"Import Projects CSV"**

**Form (Create/Edit):**
- `title` — TextInput (required)
- `semester_id` — Select (required)
- `course_id` — Select (required)
- `phase_template_id` — Select (from phase template pool, required)
- `specialization_id` — Select (required)
- `supervisor_id` — Select (filtered to users with supervisor role; validated: cannot match any reviewer)
- `previous_phase_project_id` — Select (nullable, for Phase 2 linking)
- `status` — Select (setup/evaluating/completed; default: setup)

**RelationManagers:**
- `StudentsRelationManager` — project_student pivot
  - Attach action: Select filtered to student role users
  - Validation: max 4 students; student not already in another project this semester
  - Detach action
- `ReviewersRelationManager` — project_reviewer pivot
  - Attach action: Select filtered to reviewer role users
  - Validation: cannot be the same as supervisor_id
  - Detach action

---

### A-11: EvaluationResource (Admin Monitoring View)

| Field | Value |
|-------|-------|
| **Filament Type** | Resource (read-mostly; write interface is in Staff Panel) |
| **Purpose** | Monitor and manage all evaluations across semesters |
| **Navigation** | Sidebar → Assessment Monitoring → Evaluations |
| **Access** | Coordinator |
| **Tables** | `evaluations`, `evaluation_scores` |

**List Page:**
- Columns: project title (relationship), rubric name (relationship), evaluator name (relationship), `evaluator_role`, `status` (BadgeColumn), `on_behalf_of_user` (if proxy), `unlocked_by` (if unlocked)
- Filters: Semester (via project), Status, Evaluator
- Searchable: project title, evaluator name

**View Page:**
- Evaluation header info (project, rubric, evaluator, status)
- Table of all evaluation_scores: criterion title, student name (if individual), score_awarded, score_level label, feedback

**Record Actions:**
- **"Unlock"** — changes status from submitted to draft, records unlocked_by (confirmation dialog; audit-logged)
- **"Proxy Mark Entry"** — opens evaluation form pre-filled with on_behalf_of_user_id set to current coordinator

---

### A-12: ConsolidatedMarkResource

| Field | Value |
|-------|-------|
| **Filament Type** | Resource (primarily view + override) |
| **Purpose** | View and override consolidated marks |
| **Navigation** | Sidebar → Grade Consolidation → Consolidated Marks |
| **Access** | Coordinator |
| **Tables** | `consolidated_marks`, `consolidated_mark_components` |

**List Page:**
- Columns: project title (relationship), student name (relationship), `total_calculated_score`, `override_score` (nullable), final mark (computed: override or calculated), letter grade (computed from grading_scales)
- Filters: Semester (via project), Project

**View Page:**
- Student info header
- Consolidated mark components breakdown table: `source_label`, `score`
- Override info (if any): override_score, override_reason

**Record Actions:**
- **"Override Mark"** — modal form:
  - `override_score` — TextInput (numeric, required)
  - `override_reason` — Textarea (required)
  - Saves override, preserves original `total_calculated_score`

---

### A-13: Bulk Import Page

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page (`Pages/BulkImport.php`) |
| **Purpose** | Centralized CSV upload interface |
| **Navigation** | Sidebar → Tools → Bulk Import |
| **Access** | Coordinator |

**UI Structure — Tabs:**

| Tab | Fields | Result |
|-----|--------|--------|
| **Import Users** | FileUpload (CSV), column mapping preview, "Import" button, download template link | Creates user records with roles |
| **Import Projects & Groups** | FileUpload (CSV), column mapping preview, "Import" button, download template link | Creates projects, assigns students, supervisor, reviewers |
| **Import Rubric** | FileUpload (CSV), rubric structure preview, "Import" button, download template link | Creates one rubric template with criteria and score levels |

Each tab shows:
- Success/error count after import
- Detailed error log with row numbers for failed records

---

### A-14: Grade Export Page

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page (`Pages/GradeExport.php`) |
| **Purpose** | Export final grades for SIS upload |
| **Navigation** | Sidebar → Reports & Export → Grade Export |
| **Access** | Coordinator |

**UI Elements:**
- Semester selector (dropdown)
- Course filter (optional)
- Preview table: university_id, student name, project title, calculated score, override score (if any), final score, letter grade
- Action buttons: **"Export CSV"**, **"Export PDF"**

---

### A-15: Semester Setup Wizard

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page with Wizard component (`Pages/SemesterSetupWizard.php`) |
| **Purpose** | Guided flow for setting up a new semester end-to-end |
| **Navigation** | Sidebar → Academic Setup (or action button from Semester list) |
| **Access** | Coordinator |
| **Priority** | Could (convenience feature; all steps can be done individually via other screens) |

**Wizard Steps:**
1. **Create Semester** — name, academic_year, dates
2. **Select Phase Template(s)** — choose from pool
3. **Import/Create Projects** — manual form or CSV upload
4. **Review Summary** — overview of created semester data

---

## 3. Staff Panel Screens

### Navigation Sidebar Structure
```
Staff Panel (/staff)
├── Dashboard
├── Supervision
│   └── My Supervised Projects
├── Reviews
│   └── My Review Assignments
└── (Evaluation Form — accessed via action buttons, not sidebar)
```

---

### S-01: Staff Dashboard

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page (`Pages/Dashboard.php`) |
| **Purpose** | Entry point for Supervisors and Reviewers with dual-role separation |
| **Navigation** | Top-level (default landing page) |
| **Access** | Supervisor, Reviewer |

**Widgets:**

| Widget | Type | Content | Shown To |
|--------|------|---------|----------|
| My Pending Evaluations | `StatsOverviewWidget` | Count of evaluations in pending/draft state assigned to current user | All staff |
| Projects I am Supervising | `TableWidget` | Table of supervised projects (title, semester, student count, evaluation progress) | Users with supervisor role |
| Projects I am Reviewing | `TableWidget` | Table of review assignments (title, semester, rubric, my status) | Users with reviewer role |

**Design Note:** These two table widgets are the cornerstone of the "dual role" UI. They are visually separated with distinct headings. A user who is both supervisor and reviewer sees both widgets. A user with only one role sees only the relevant widget.

---

### S-02: My Supervised Projects

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page (read-only list) |
| **Purpose** | List all projects where current user is the supervisor |
| **Navigation** | Sidebar → Supervision → My Supervised Projects |
| **Access** | Supervisor (scoped to own projects) |

**Table Columns:**
- Project title, student names, semester, course, phase template, status, evaluation progress (X/Y submitted)
- Row action: Click to view project detail (S-04)
- Row action: **"Fill Assessment"** button (leads to S-05) — shown only if pending/draft evaluations exist for this supervisor

---

### S-03: My Review Assignments

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page (read-only list) |
| **Purpose** | List all projects where current user is assigned as reviewer |
| **Navigation** | Sidebar → Reviews → My Review Assignments |
| **Access** | Reviewer (scoped to assigned projects) |

**Table Columns:**
- Project title, semester, rubric template name, fill_order, my evaluation status (pending/draft/submitted)
- Row action: **"Fill Assessment"** button (leads to S-05) — shown only if evaluation is pending or draft and fill_order is met
- Row action: Click to view project detail (S-04)

---

### S-04: Project Detail View

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page (read-only, ViewRecord-style) |
| **Purpose** | Read-only detail of a project's team, evaluation status, and marks |
| **Navigation** | Reached from S-02 or S-03 row click |
| **Access** | Supervisor (for supervised projects), Reviewer (for assigned projects) |

**Sections:**
1. **Project Info** — title, course, semester, phase template, specialization
2. **Team Members** — table of students (name, university_id)
3. **Evaluation Status** — table showing all rubrics in the phase, which evaluators are assigned, and their submission status (pending/draft/submitted)

---

### S-05: Evaluation Form (THE CORE SCREEN)

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page (`Pages/EvaluationForm.php`) — NOT a standard Resource form |
| **Purpose** | Dynamic grading interface where evaluators fill rubrics |
| **Navigation** | Reached from S-02 or S-03 "Fill Assessment" action |
| **Access** | Supervisor (for supervisor rubrics), Reviewer (for reviewer rubrics) |
| **Tables** | `evaluations`, `evaluation_scores`, `criteria`, `score_levels` |

**Why Custom Page:** The form structure is entirely dynamic — driven by the rubric template's criteria and score levels. Standard Filament Resource forms have a fixed schema. This screen must programmatically compose form components based on database data.

**UI Structure:**

```
┌─────────────────────────────────────────────────┐
│ Header: Project Title | Rubric Name | Role      │
├─────────────────────────────────────────────────┤
│                                                  │
│ ── GROUP CRITERIA ──────────────────────────────│
│                                                  │
│ ┌─ Criterion: "Literature Review" (5 marks) ──┐ │
│ │  Score: [Dropdown: Excellent/VeryGood/...] or │ │
│ │         [Manual: ___/5]                       │ │
│ │  Feedback: [___________________________]     │ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│ ┌─ Criterion: "Report" (5 marks) ─────────────┐ │
│ │  Score: [Dropdown] or [Manual: ___/5]         │ │
│ │  Feedback: [___________________________]     │ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│ ── INDIVIDUAL CRITERIA ─────────────────────────│
│                                                  │
│ ┌─ Criterion: "Presentation" (5 marks) ───────┐ │
│ │  ┌─ Student: Ali Al-Busaidi ────────────┐    │ │
│ │  │  Score: [___/5]  Feedback: [______]  │    │ │
│ │  └──────────────────────────────────────┘    │ │
│ │  ┌─ Student: Sara Al-Habsi ─────────────┐    │ │
│ │  │  Score: [___/5]  Feedback: [______]  │    │ │
│ │  └──────────────────────────────────────┘    │ │
│ └──────────────────────────────────────────────┘ │
│                                                  │
│ General Feedback: [____________________________] │
│                                                  │
│ [Save Draft]                      [Submit]       │
│                                                  │
│ (If submitted: form is read-only, all disabled)  │
└─────────────────────────────────────────────────┘
```

**Key Behaviors:**
- Form built dynamically from `criteria` (via `rubric_template_id`) and `score_levels`
- Group criteria (is_individual=false): one score field per criterion
- Individual criteria (is_individual=true): one score field PER student PER criterion
- Score input: Select from score_levels dropdown OR manual numeric input (0 to max_score)
- Feedback: optional Textarea per criterion (per student for individual criteria)
- **Save Draft**: persists all scores, status stays "draft", form remains editable
- **Submit**: validates all required criteria have scores, shows confirmation dialog, sets status to "submitted", locks form
- If status is "submitted": all form fields are disabled (read-only view)
- If evaluation was unlocked by coordinator: form becomes editable again

**Filament Components Used:**
- `Forms\Components\Section` — for grouping criteria by type
- `Forms\Components\Select` — for score level dropdowns
- `Forms\Components\TextInput` — for manual score entry
- `Forms\Components\Textarea` — for feedback
- `Forms\Components\Tabs` or `Forms\Components\Repeater` — for per-student individual scoring

---

## 4. Student Panel Screens

### Navigation Sidebar Structure
```
Student Panel (/student)
├── Dashboard
└── My Marks
```

---

### ST-01: Student Dashboard

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page |
| **Purpose** | Simple landing page for students |
| **Navigation** | Top-level (default landing page) |
| **Access** | Student |

**Widgets:**

| Widget | Type | Content |
|--------|------|---------|
| My Project | `StatsOverviewWidget` | Project title, supervisor name, teammate names |
| Marks Available | `StatsOverviewWidget` | Indicator showing if consolidated marks are finalized |

---

### ST-02: My Marks Page

| Field | Value |
|-------|-------|
| **Filament Type** | Custom Page (`Pages/MyMarks.php`) |
| **Purpose** | Student views their internal and consolidated marks |
| **Navigation** | Sidebar → My Marks |
| **Access** | Student (scoped to own data via policy) |

**UI Structure:**

```
┌──────────────────────────────────────────────┐
│ Semester: [Select: Fall 2026 ▼]              │
├──────────────────────────────────────────────┤
│                                               │
│ Project: "AI-Based Assessment System"         │
│ Course: IT4001 - B.Tech Project Phase I       │
│ Supervisor: Dr. Ahmed Al-Rawahi               │
│                                               │
│ ── INTERNAL MARKS (Supervisor) ──────────────│
│                                               │
│ ┌─ Review I (Supervisor) — 10 marks ────────┐│
│ │  Project Plan:     2.0 / 2.0              ││
│ │  Attendance:       1.75 / 2.0             ││
│ │  Tracing Sheet:    2.0 / 2.0              ││
│ │  Literature Review: 1.75 / 2.0            ││
│ │  Report:           1.5 / 2.0              ││
│ │  Subtotal:         9.0 / 10.0             ││
│ │  Feedback: "Good progress, improve..."    ││
│ └────────────────────────────────────────────┘│
│                                               │
│ ── CONSOLIDATED MARKS ───────────────────────│
│                                               │
│ ┌────────────────────────────────────────────┐│
│ │  Source               Score                ││
│ │  ─────────────────────────────             ││
│ │  Supervisor Total     35.0 / 40            ││
│ │  Reviewer Total       52.5 / 60            ││
│ │  ─────────────────────────────             ││
│ │  Calculated Total:    87.5 / 100           ││
│ │  Final Score:         87.5                 ││
│ │  Letter Grade:        A-                   ││
│ │  GPA:                 3.7                  ││
│ └────────────────────────────────────────────┘│
│                                               │
│ All data is read-only.                        │
└──────────────────────────────────────────────┘
```

**Key Behaviors:**
- Semester selector if student has participated in multiple semesters
- Internal marks section: shows scores from supervisor evaluations, broken down by rubric and criterion, with feedback
- Consolidated marks section: shows final score, component breakdown, letter grade
- If coordinator has overridden the mark: final score shows the override value
- Student does NOT see individual reviewer names or reviewer mark breakdowns
- All data is strictly read-only

---

## 5. Widgets Summary

| Widget | Panel | Filament Type | Data Source |
|--------|-------|---------------|-------------|
| Pending Evaluations Count | Admin, Staff | `StatsOverviewWidget` | `evaluations` where status != submitted |
| Project Status Distribution | Admin | `ChartWidget` (Pie) | `projects` grouped by status |
| Submission Progress | Admin | `ChartWidget` (Bar) | `evaluations` completion % per semester |
| Recent Activity Feed | Admin | `TableWidget` | `activity_log` (Spatie) |
| My Supervised Projects | Staff | `TableWidget` | `projects` where supervisor_id = auth user |
| My Review Assignments | Staff | `TableWidget` | `project_reviewer` where user_id = auth user |
| My Project Info | Student | `StatsOverviewWidget` | `project_student` where user_id = auth user |
| Marks Available | Student | `StatsOverviewWidget` | `consolidated_marks` existence check |

---

## 6. Screen Count Summary

| Panel | Resources | Custom Pages | RelationManagers | Widgets | Total Screens |
|-------|-----------|-------------|------------------|---------|---------------|
| Admin | 9 | 4 | 8 | 4 | 13+ |
| Staff | 0 | 5 | 0 | 3 | 5 |
| Student | 0 | 2 | 0 | 2 | 2 |
| **Total** | **9** | **11** | **8** | **9** | **20+** |

---

## 7. Development Build Order

The build order follows a strict dependency chain dictated by database foreign keys and business logic.

### Phase A: Foundation
> Must complete before any screens can be built.

| Step | Task | Produces |
|------|------|---------|
| A1 | Create Laravel migrations for all 16 tables across 4 domains (convert `database_schema.sql`) | Database schema |
| A2 | Create Eloquent models with relationships, casts, and SoftDeletes trait (16 models) | Models |
| A3 | Install and configure `spatie/laravel-permission` — seed roles: super_admin, coordinator, supervisor, reviewer, student | RBAC |
| A4 | Install and configure `laravel/fortify` — login, MFA, password rules | Auth |
| A5 | Create three Filament PanelProviders (Admin, Staff, Student) with auth middleware | Panels |
| A6 | Seed Super Admin account | Bootstrap user |

### Phase B: Master Data & Users
> Foundation for all downstream screens.

| Step | Task | Screens Built |
|------|------|---------------|
| B1 | DepartmentResource + SpecializationsRelationManager | A-02, A-03 |
| B2 | CourseResource | A-04 |
| B3 | GradingScaleResource | A-05 |
| B4 | UserResource with role management | A-06 |
| B5 | CSV import for users (BulkImport page, users tab) | A-13 (partial) |

### Phase C: Template Pool
> Depends on Phase B (rubric_templates.created_by references users).

| Step | Task | Screens Built |
|------|------|---------------|
| C1 | RubricTemplateResource with CriteriaRelationManager | A-07 |
| C2 | Score Levels management via Repeater inside criterion form | A-07 (nested) |
| C3 | Clone / Version / Lock actions on RubricTemplateResource | A-07 (actions) |
| C4 | CSV import for rubrics | A-13 (partial) |
| C5 | PhaseTemplateResource with PhaseRubricRulesRelationManager | A-08 |

### Phase D: Academic Sandbox
> Depends on Phase C (projects.phase_template_id references phase_templates).

| Step | Task | Screens Built |
|------|------|---------------|
| D1 | SemesterResource with CoordinatorsRelationManager | A-09 |
| D2 | ProjectResource with Students + Reviewers RelationManagers | A-10 |
| D3 | Supervisor/reviewer validation rules (no self-review, max 4 students) | A-10 (validation) |
| D4 | CSV import for projects/groups | A-13 (complete) |
| D5 | Semester Setup Wizard (Could-have) | A-15 |

### Phase E: Assessment Engine
> Depends on Phase D (evaluations reference projects, rubric_templates, users).

| Step | Task | Screens Built |
|------|------|---------------|
| E1 | Evaluation model logic (auto-create pending evaluations on project status change) | Backend logic |
| E2 | Staff Dashboard with dual-role widgets | S-01 |
| E3 | Supervised Projects List + Review Assignments List | S-02, S-03 |
| E4 | Project Detail View | S-04 |
| E5 | **Evaluation Form** (dynamic rubric, group vs individual, draft/submit) | S-05 |
| E6 | EvaluationResource (admin monitoring + unlock action) | A-11 |
| E7 | Proxy marking (coordinator on behalf of evaluator) | A-11 (action) |

### Phase F: Consolidation, Output & Polish
> Depends on Phase E (consolidated_marks computed from evaluation_scores).

| Step | Task | Screens Built |
|------|------|---------------|
| F1 | Auto-calculation engine (consolidated marks from phase_rubric_rules aggregation) | Backend logic |
| F2 | ConsolidatedMarkResource with override mechanism | A-12 |
| F3 | Admin Dashboard widgets | A-01 |
| F4 | Grade Export page (CSV/Excel) | A-14 |
| F5 | PDF report generation | A-14 (PDF action) |
| F6 | Student Dashboard + My Marks page | ST-01, ST-02 |
| F7 | Email notifications (assignment, submission, unlock, finalization) | Backend |
| F8 | Spatie Activitylog integration (audit logging) | Backend |
| F9 | Policy refinement (Filament Policies for all resources) | Security |

### Dependency Graph
```
Phase A (Foundation)
    └── Phase B (Master Data & Users)
            └── Phase C (Template Pool)
                    └── Phase D (Academic Sandbox)
                            └── Phase E (Assessment Engine)
                                    └── Phase F (Consolidation & Output)
```

Each phase requires the previous phase to be complete. Within each phase, steps can be partially parallelized (e.g., B1-B3 can run in parallel since they are independent Resources).
