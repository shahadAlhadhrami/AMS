<?php

namespace Tests\Unit;

use App\Models\RubricTemplate;
use App\Models\Semester;
use App\Models\User;
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

    public function test_user_approved_scope_uses_bound_boolean(): void
    {
        $query = User::approved();

        $this->assertStringContainsString('is_approved', $query->toSql());
        $this->assertStringNotContainsString('"is_approved" = true', $query->toSql());
        $this->assertSame([true], $query->getBindings());
    }

    public function test_user_unapproved_scope_uses_bound_boolean(): void
    {
        $query = User::unapproved();

        $this->assertStringContainsString('is_approved', $query->toSql());
        $this->assertStringNotContainsString('"is_approved" = false', $query->toSql());
        $this->assertSame([false], $query->getBindings());
    }
}
