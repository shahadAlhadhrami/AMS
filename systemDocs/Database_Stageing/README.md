# Database Staging Notes

Updated: 2026-05-19

Use `database_schema.sql` as the current AMS domain schema reference. It reflects the current implementation-level model: merged `Reviewer/Supervisor` staff role, rubric folders, deliverables, phase-template reviewer/external pivots, coordinator-owned projects, master-data setup progress, and warning-based student reassignment support.

Use `dummy_data.sql` only as lightweight sample data aligned to that schema.

`activity_log.sql` and `activity_log_import.sql` are historical database exports. They may contain old timestamps, old demo records, and responsibility labels such as `Supervisor` and `Reviewer`; those labels are evaluation responsibilities, not separate stored staff roles. Do not treat those export files as the current schema source of truth.
