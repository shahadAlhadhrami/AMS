# AMS Development Status

> **Last Updated:** 2026-05-19
> **Status:** Updated to match the current Laravel/Filament codebase

---

## Screen Implementation Status

### Admin Panel

| ID | Screen | Status | File(s) |
|----|--------|--------|---------|
| A-01 | Admin Dashboard | Done | `Pages/Dashboard.php` |
| A-02 | Department Resource | Done | `Resources/DepartmentResource.php` |
| A-03 | Specialization Resource | Done | `Resources/SpecializationResource.php` |
| A-04 | Course Resource | Done | `Resources/CourseResource.php` |
| A-05 | Grading Scale Resource | Done | `Resources/GradingScaleResource.php` |
| A-06 | User Resource | Done | `Resources/UserResource.php` |
| A-07 | Rubric Template Resource | Done | `Resources/RubricTemplateResource.php` |
| A-08 | Phase Template Resource | Done | `Resources/PhaseTemplateResource.php` |
| A-09 | Semester Resource | Done | `Resources/SemesterResource.php` |
| A-10 | Project Resource | Done | `Resources/ProjectResource.php` |
| A-11 | Evaluation Resource | Done | `Resources/EvaluationResource.php` |
| A-12 | Consolidated Mark Resource | Done | `Resources/ConsolidatedMarkResource.php` |
| A-13 | Bulk Imports | Done | `Pages/BulkImports.php`; resource list buttons route into this shared page with `type=users`, `type=projects`, or `type=rubric-templates` |
| A-14 | Grade Export | Done | `Pages/GradeExport.php` |
| A-15 | Master Data Setup Wizard | Done | `Pages/MasterDataSetupWizard.php` |
| A-16 | Proxy Evaluation Form | Done | `Pages/ProxyEvaluationForm.php` |
| A-17 | Coordinator Registration | Done | `Pages/CoordinatorRegistration.php` |

### Staff Panel

| ID | Screen | Status | File(s) |
|----|--------|--------|---------|
| S-01 | Staff Dashboard | Done | `Pages/Dashboard.php` |
| S-02 | Project Detail View | Done | `Pages/ProjectDetail.php` |
| S-03 | Evaluation Form | Done | `Pages/EvaluationForm.php` |

### Student Panel

| ID | Screen | Status | File(s) |
|----|--------|--------|---------|
| ST-01 | Student Dashboard | Done | `Pages/Dashboard.php` |
| ST-02 | Marks view | Done | Included in `Pages/Dashboard.php` |

---

## Widget Implementation Status

| Widget | Panel | Status |
|--------|-------|--------|
| Pending Evaluations Count | Admin | Done |
| Project Status Distribution (Pie Chart) | Admin | Done |
| Submission Progress (Bar Chart) | Admin | Done |
| Recent Activity Feed | Admin | Done |
| System Overview | Admin | Done |
| Coordinator Projects | Admin | Done |
| My Pending Evaluations | Staff | Done |
| Projects I am Supervising | Staff | Done |
| Projects I am Reviewing | Staff | Done |
| My Project Info | Student | Done |
| Marks Available | Student | Done |

---

## Functional Requirements Status

### Module 1: Authentication & User Management (FR-AU) — 12 requirements
- Implemented via Laravel Fortify + Filament Auth + Spatie Permission, including pending Coordinator approval.

### Module 2: Master Data Management (FR-MD) — 7 requirements
- Implemented via Admin panel resources (A-02 through A-05) plus the first-login Master Data Setup Wizard.

### Module 3: Template Pool / Workflow Engine (FR-TP) — 15 requirements
- Implemented: rubric folders, rubric templates with deliverables/criteria/score levels, clone/lock, spreadsheet import, phase templates with rubric rules and reviewer/external assignment.

### Module 4: Semester & Academic Setup (FR-SA) — 13 requirements
- Implemented: Super Admin semester management, Coordinator-owned projects, student/reviewer assignment, spreadsheet import, warning-and-overwrite student reassignment, and project transfer to another Coordinator.

### Module 5: Assessment Execution (FR-AE) — 13 requirements
- All implemented: dynamic evaluation form, group/individual scoring, draft/submit, proxy marking, unlock, fill order enforcement

### Module 6: Grade Consolidation (FR-GC) — 8 requirements
- All implemented: auto-calculation, aggregation methods, consolidated marks, override mechanism, letter grade/GPA mapping

### Module 7: Reporting & Export (FR-RE) — 7 requirements
- All implemented: dashboards (admin/staff/student), grade export (CSV/PDF), project detail views, student marks portal

### Module 8: System Features (FR-SF) — 9 requirements
- All implemented: audit logging (Spatie Activitylog), email notifications, soft deletes, template reuse

---

## Architecture Decisions Log

| Decision | Rationale |
|----------|-----------|
| **Shared import page with contextual entry points (A-13)** | The current implementation uses one `BulkImports` page. Users, Projects, and Rubric Templates list pages link into the same page with an importer type, keeping one import workflow while preserving contextual entry points. |
| **Merged staff role** | The stored staff role is `Reviewer/Supervisor`; supervisor and reviewer are assignment responsibilities, not separate stored roles. |
| **Master-data setup gate** | Super Admin is redirected to `MasterDataSetupWizard` until departments, specializations, courses, and grading scales exist. |
| **Coordinator approval flow** | Admin registration creates unapproved Coordinator accounts; a Super Admin approves them before access is granted. |
| **Proxy evaluation as admin page** | Proxy marking is implemented as a dedicated admin page (`ProxyEvaluationForm.php`) rather than inline on the Evaluation resource, for better UX with the dynamic rubric form. |
| **Shared evaluation form builder** | `BuildsEvaluationForm` concern is shared between Staff `EvaluationForm` and Admin `ProxyEvaluationForm` to avoid code duplication. |
| **Three separate panels** | Admin, Staff, and Student panels are fully isolated for security and UX separation. Users with multiple roles access different panels at different URLs. |

---

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend Framework | Laravel 12 |
| Admin Panel | FilamentPHP v5 |
| Authentication | Laravel Fortify + Filament Auth |
| Authorization | Spatie Laravel Permission v7 |
| Audit Logging | Spatie Laravel Activitylog v4 |
| PDF Generation | Laravel DomPDF |
| Styling | Tailwind CSS v4 |
| Build Tool | Vite |
| Database | MySQL for the current local runtime (`.env`); `.env.example` still ships with Laravel's SQLite default |
