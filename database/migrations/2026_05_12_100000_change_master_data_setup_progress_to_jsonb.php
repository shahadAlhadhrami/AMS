<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE users ALTER COLUMN master_data_setup_progress TYPE jsonb USING master_data_setup_progress::jsonb');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE users ALTER COLUMN master_data_setup_progress TYPE json USING master_data_setup_progress::json');
    }
};
