# Bulk Import Projects CSV Test Cases

These files test the projects branch of the `BulkImports` page. The CSV uses a
**long format**: one row per student. `project_title` and `supervisor_id` repeat
across all rows that belong to the same project — rows are grouped by `project_title`
inside the importer.

Standard columns:

```csv
project_title,supervisor_id,student_id
```

- `supervisor_id` / `student_id` resolve to `users.university_id`.
- Supervisors must have the `Reviewer/Supervisor` role; students must have the `Student` role.
- Every row in the same `project_title` group must repeat the same `supervisor_id`.
- A project may have **at most 4 students**.
- A student may appear in **only one project per CSV**.

Semester, Course, Phase Template, and Specialization are NOT in the CSV — they
are selected in the Filament UI's context step after the preview passes. Reviewers
come from the chosen Phase Template's `reviewers` relation, not the CSV.

## Files

- `01_valid_projects.csv`: three valid projects with three students each (canonical demo, uses the 3 supervisors + 9 students from `bulk-import-users/13`).
- `02_valid_solo_project.csv`: one valid project with a single student.
- `03_invalid_missing_required_fields.csv`: rows missing `project_title`, `supervisor_id`, and `student_id` respectively.
- `04_invalid_unknown_lookups.csv`: rows referencing a non-existent supervisor and a non-existent student.
- `05_invalid_supervisor_wrong_role.csv`: a student university_id used in the `supervisor_id` column.
- `06_invalid_duplicate_student_in_csv.csv`: the same student appears in two different projects within one CSV.
- `07_invalid_too_many_students.csv`: a project with 5 students (max is 4).
- `08_invalid_inconsistent_supervisor.csv`: two rows for the same `project_title` with different `supervisor_id`s.
- `09_column_mapping_nonstandard_headers.csv`: valid long-format data with renamed headers for the column-mapping step.
