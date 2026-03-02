# AMS Development Status

> **Last Updated:** 2026-03-02
> **Status:** All 20 documented screens implemented

---

## Screen Implementation Status

### Admin Panel (15/15)

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
| A-13 | Bulk Import (Distributed) | Done | `Pages/BulkImportUsers.php`, `ListProjects.php`, `ListRubricTemplates.php` |
| A-14 | Grade Export | Done | `Pages/GradeExport.php` |
| A-15 | Semester Setup Wizard | Done | `Pages/SemesterSetupWizard.php` |

### Staff Panel (5/5)

| ID | Screen | Status | File(s) |
|----|--------|--------|---------|
| S-01 | Staff Dashboard | Done | `Pages/Dashboard.php` |
| S-02 | My Supervised Projects | Done | `Pages/MySupervisedProjects.php` |
| S-03 | My Review Assignments | Done | `Pages/MyReviewAssignments.php` |
| S-04 | Project Detail View | Done | `Pages/ProjectDetail.php` |
| S-05 | Evaluation Form | Done | `Pages/EvaluationForm.php` |

### Student Panel (2/2)

| ID | Screen | Status | File(s) |
|----|--------|--------|---------|
| ST-01 | Student Dashboard | Done | `Pages/Dashboard.php` |
| ST-02 | My Marks Page | Done | `Pages/MyMarks.php` |

---

## Widget Implementation Status (9/9)

| Widget | Panel | Status |
|--------|-------|--------|
| Pending Evaluations Count | Admin | Done |
| Project Status Distribution (Pie Chart) | Admin | Done |
| Submission Progress (Bar Chart) | Admin | Done |
| Recent Activity Feed | Admin | Done |
| My Pending Evaluations | Staff | Done |
| Projects I am Supervising | Staff | Done |
| Projects I am Reviewing | Staff | Done |
| My Project Info | Student | Done |
| Marks Available | Student | Done |

---

## Functional Requirements Status

### Module 1: Authentication & User Management (FR-AU) — 11 requirements
- All implemented via Laravel Fortify + Filament Auth + Spatie Permission

### Module 2: Master Data Management (FR-MD) — 6 requirements
- All implemented via Admin panel resources (A-02 through A-05)

### Module 3: Template Pool / Workflow Engine (FR-TP) — 15 requirements
- All implemented: rubric templates with versioning, criteria, score levels, clone/lock, CSV import, phase templates with rubric rules

### Module 4: Semester & Academic Setup (FR-SA) — 13 requirements
- All implemented: semesters, projects, student/reviewer assignment, CSV import, validation rules, semester setup wizard

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
| **Distributed imports (A-13)** | Instead of a single 3-tab import page, imports are placed on their respective resource list pages (Users, Projects, Rubrics). Better UX since users can import directly from context. |
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
| Database | SQLite (dev) / MySQL (prod) |
