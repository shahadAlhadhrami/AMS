# Bulk Import Rubric Templates — CSV Test Cases

Each CSV file is imported as one `RubricTemplate`. Rows describe deliverables → criteria → score levels.

## Column reference

```csv
deliverable_title,deliverable_max_marks,criterion_title,criterion_description,max_score,is_individual,level_label,level_score,level_description
```

**Required:** `criterion_title`, `max_score`, `is_individual`, `level_label`, `level_score`  
**Optional:** `deliverable_title`, `deliverable_max_marks`, `criterion_description`, `level_description`

The file is hierarchical:
- Rows sharing a `deliverable_title` → grouped into one deliverable
- Rows sharing a `criterion_title` within a deliverable → grouped into one criterion
- Each row defines one score level on that criterion

`max_score` and `is_individual` are read from the **first row** of each criterion group.  
If `deliverable_max_marks` is 0 or omitted, it is auto-calculated as the sum of its criteria's `max_score` values.  
If `deliverable_title` is absent, all criteria are grouped under a single **"General"** deliverable.

Real rubrics use **6 score levels** per criterion: Excellent, Very Good, Good, Satisfactory, Poor, Very Poor.

## Files

- `01_valid_basic.csv` — 1 deliverable, 1 criterion, 6 levels.
- `02_valid_complex.csv` — 2 deliverables × 2 criteria × 6 levels; includes an `is_individual = true` criterion. Mirrors the downloadable xlsx template.
- `03_invalid_missing_required_columns.csv` — missing `max_score` and `is_individual` headers → import errors.
- `04_valid_no_descriptions.csv` — all optional description fields left blank; uses fractional scores (2.5, 2, 1.5, 1, 0.5, 0.25) typical of supervisor rubrics.
- `05_empty_data_rows.csv` — header row only, no data → import error.
- `06_no_deliverable_column.csv` — omits the optional `deliverable_title` column; all criteria fall under the auto-named "General" deliverable.
