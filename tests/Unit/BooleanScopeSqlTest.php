<?php

namespace Tests\Unit;

use App\Models\RubricTemplate;
use App\Models\Semester;
use Tests\TestCase;

class BooleanScopeSqlTest extends TestCase
{
    public function test_semester_active_scope_uses_sql_boolean_literal(): void
    {
        $query = Semester::active();

        $this->assertStringContainsString('is_active', $query->toSql());
        $this->assertStringContainsString('true', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    public function test_semester_inactive_scope_uses_sql_boolean_literal(): void
    {
        $query = Semester::inactive();

        $this->assertStringContainsString('is_active', $query->toSql());
        $this->assertStringContainsString('false', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    public function test_semester_closed_scope_uses_sql_boolean_literal(): void
    {
        $query = Semester::closed();

        $this->assertStringContainsString('is_closed', $query->toSql());
        $this->assertStringContainsString('true', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    public function test_semester_open_scope_uses_sql_boolean_literal(): void
    {
        $query = Semester::open();

        $this->assertStringContainsString('is_closed', $query->toSql());
        $this->assertStringContainsString('false', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    public function test_rubric_template_locked_scope_uses_sql_boolean_literal(): void
    {
        $query = RubricTemplate::locked();

        $this->assertStringContainsString('is_locked', $query->toSql());
        $this->assertStringContainsString('true', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }

    public function test_rubric_template_unlocked_scope_uses_sql_boolean_literal(): void
    {
        $query = RubricTemplate::unlocked();

        $this->assertStringContainsString('is_locked', $query->toSql());
        $this->assertStringContainsString('false', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }
}
