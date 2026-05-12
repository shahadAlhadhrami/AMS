<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        match (DB::connection()->getDriverName()) {
            'pgsql' => $this->upPostgres(),
            'sqlite' => $this->upSqlite(),
            'mysql' => $this->upMysql(),
            'mariadb' => $this->upMysql(),
            default => null,
        };
    }

    public function down(): void
    {
        match (DB::connection()->getDriverName()) {
            'pgsql' => $this->downPostgres(),
            'sqlite' => $this->downSqlite(),
            'mysql' => $this->downMysql(),
            'mariadb' => $this->downMysql(),
            default => null,
        };
    }

    private function upPostgres(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_unique');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_university_id_unique');
        DB::statement('DROP INDEX IF EXISTS users_email_unique');
        DB::statement('DROP INDEX IF EXISTS users_university_id_unique');

        DB::statement('CREATE UNIQUE INDEX users_email_active_unique ON users (email) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX users_university_id_active_unique ON users (university_id) WHERE deleted_at IS NULL');
    }

    private function downPostgres(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_email_active_unique');
        DB::statement('DROP INDEX IF EXISTS users_university_id_active_unique');

        DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email)');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_university_id_unique UNIQUE (university_id)');
    }

    private function upSqlite(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_email_unique');
        DB::statement('DROP INDEX IF EXISTS users_university_id_unique');

        DB::statement('CREATE UNIQUE INDEX users_email_active_unique ON users (email) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX users_university_id_active_unique ON users (university_id) WHERE deleted_at IS NULL');
    }

    private function downSqlite(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_email_active_unique');
        DB::statement('DROP INDEX IF EXISTS users_university_id_active_unique');

        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        DB::statement('CREATE UNIQUE INDEX users_university_id_unique ON users (university_id)');
    }

    private function upMysql(): void
    {
        DB::statement('ALTER TABLE users DROP INDEX users_email_unique');
        DB::statement('ALTER TABLE users DROP INDEX users_university_id_unique');

        DB::statement('CREATE UNIQUE INDEX users_email_active_unique ON users ((CASE WHEN deleted_at IS NULL THEN email ELSE NULL END))');
        DB::statement('CREATE UNIQUE INDEX users_university_id_active_unique ON users ((CASE WHEN deleted_at IS NULL THEN university_id ELSE NULL END))');
    }

    private function downMysql(): void
    {
        DB::statement('ALTER TABLE users DROP INDEX users_email_active_unique');
        DB::statement('ALTER TABLE users DROP INDEX users_university_id_active_unique');

        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        DB::statement('CREATE UNIQUE INDEX users_university_id_unique ON users (university_id)');
    }
};
