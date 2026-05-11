<?php

namespace Tests\Unit;

use App\Database\BooleanSafePostgresConnection;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;

class PostgresBooleanBindingTest extends TestCase
{
    public function test_postgres_boolean_bindings_are_not_converted_to_integers(): void
    {
        $connection = new BooleanSafePostgresConnection(fn () => null, 'database', '', []);

        $bindings = $connection->prepareBindings([
            false,
            true,
            new DateTimeImmutable('2026-05-11 18:00:54'),
        ]);

        $this->assertSame(false, $bindings[0]);
        $this->assertSame(true, $bindings[1]);
        $this->assertSame('2026-05-11 18:00:54', $bindings[2]);
    }

    public function test_postgres_boolean_bindings_use_pdo_bool_parameter_type(): void
    {
        $connection = new BooleanSafePostgresConnection(fn () => null, 'database', '', []);
        $statement = new class
        {
            public array $bindings = [];

            public function bindValue($parameter, $value, $type): bool
            {
                $this->bindings[$parameter] = compact('value', 'type');

                return true;
            }
        };

        $connection->bindValues($statement, [false, true, 10, 'value']);

        $this->assertSame(PDO::PARAM_BOOL, $statement->bindings[1]['type']);
        $this->assertSame(PDO::PARAM_BOOL, $statement->bindings[2]['type']);
        $this->assertSame(PDO::PARAM_INT, $statement->bindings[3]['type']);
        $this->assertSame(PDO::PARAM_STR, $statement->bindings[4]['type']);
    }
}
