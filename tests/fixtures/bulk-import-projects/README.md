# Bulk Import Projects CSV Test Cases

These files test the projects branch of the `BulkImports` page. Standard columns are:

```csv
title,semester_name,course_code,phase_template_name,specialization_name,supervisor_university_id,student_university_ids,reviewer_university_ids
```

`student_university_ids` and `reviewer_university_ids` are pipe-separated (`|`).
Resolution lookups use:
- `semester_name` → `semesters.name`
- `course_code` → `courses.code`
- `phase_template_name` → `phase_templates.name`
- `specialization_name` → `specializations.name`
- `*_university_id(s)` → `users.university_id`

Supervisors must have the `Reviewer/Supervisor` role, students the `Student` role,
and reviewers the `Reviewer/Supervisor` role.

## Files

- `01_valid_projects.csv`: two valid projects with multiple students and reviewers each.
- `02_valid_minimal_students.csv`: valid projects with one student and one reviewer.
- `03_invalid_missing_required_fields.csv`: rows missing title / semester / course / supervisor.
- `04_invalid_unknown_lookups.csv`: rows with unknown semester, course, phase template, specialization.
- `05_invalid_supervisor_wrong_role.csv`: supervisor university_id exists but lacks the Reviewer/Supervisor role.
- `06_invalid_student_in_two_projects_same_semester.csv`: same student appears in two CSV rows for the same semester.
- `07_invalid_too_many_students.csv`: project with more than 4 students.
- `08_invalid_supervisor_also_reviewer.csv`: supervisor university_id also appears in `reviewer_university_ids`.
- `09_column_mapping_nonstandard_headers.csv`: valid rows with non-standard headers for the column-mapping step.
