<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phase_templates', function (Blueprint $table) {
            $table->foreignId('external_reviewer_id')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('phase_templates', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'external_reviewer_id');
            $table->dropColumn('external_reviewer_id');
        });
    }
};
