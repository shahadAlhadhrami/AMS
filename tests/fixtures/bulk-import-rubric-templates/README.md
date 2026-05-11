# Bulk Import Rubric Templates CSV Test Cases

These files test the rubric-templates branch of the `BulkImports` page. Each CSV file is
imported as one `RubricTemplate`; rows describe deliverables → criteria → score levels.

Standard columns:

```csv
deliverable_title,deliverable_max_marks,criterion_title,criterion_description,max_score,is_individual,level_label,level_score,level_description
```

Required columns: `criterion_title`, `max_score`, `is_individual`, `level_label`, `level_score`.
Optional: `deliverable_title`, `deliverable_max_marks`, `criterion_description`, `level_description`.

The CSV is hierarchical: rows sharing a `deliverable_title` are grouped into one deliverable;
rows sharing a `criterion_title` within that deliverable are grouped into one criterion; each
row then defines one score level on that criterion. `max_score` and `is_individual` are read
from the first row of each criterion group.

## Files

- `01_valid_basic.csv`: minimal valid rubric — 1 deliverable, 1 criterion, 2 levels.
- `02_valid_complex.csv`: multi-deliverable hierarchy with individual and group criteria.
- `03_invalid_missing_required_columns.csv`: missing `max_score` and `is_individual` headers.
- `04_invalid_non_numeric_max_score.csv`: `max_score` contains a non-numeric string.
- `05_empty_data_rows.csv`: header row only, no data.
- `06_no_deliverable_column.csv`: omits the optional `deliverable_title` column — all criteria
  should be grouped under a single auto-named "General" deliverable.
