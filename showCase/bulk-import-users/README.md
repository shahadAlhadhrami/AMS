# Bulk Import Users CSV Test Cases

These files test the `BulkImportUsers` CSV flow. Standard columns are:

```csv
university_id,name,email,role
```

Student IDs follow the requested UTAS-style format:

- `26s2020`: `26` department, `s` September admission, non-sequential number
- `26j2174`: `26` department, `j` January admission, non-sequential number

Staff IDs follow `e` plus four digits, such as `e4382`.

Student emails use `university_id@utas.edu.om`.
Staff emails use `first.last@utas.edu.om`.

## Files

- `01_valid_students_only.csv`: valid student-only import.
- `02_valid_staff_only_reviewer_supervisor.csv`: valid staff import using the app's merged `Reviewer/Supervisor` role.
- `03_valid_mixed_users.csv`: valid mixed students and staff.
- `04_valid_case_insensitive_roles.csv`: valid data with role casing variations.
- `05_invalid_duplicate_university_id_and_email.csv`: duplicate IDs and duplicate emails inside one CSV.
- `06_invalid_missing_required_fields.csv`: missing required fields.
- `07_invalid_email_formats.csv`: syntactically invalid emails.
- `08_invalid_restricted_roles.csv`: `Coordinator` and `Super Admin` rows that should be rejected for bulk import.
- `09_invalid_role_values.csv`: role values outside the allowed set.
- `10_column_mapping_nonstandard_headers.csv`: valid rows with non-standard headers for the column-mapping step.
- `11_empty_headers_only.csv`: headers only, no data rows.
- `12_valid_staff_role_aliases.csv`: valid staff aliases accepted by import and normalised to `Reviewer/Supervisor`.
