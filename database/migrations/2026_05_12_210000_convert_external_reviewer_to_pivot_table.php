<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phase_template_external', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phase_template_id')->constrained('phase_templates')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['phase_template_id', 'user_id']);
        });

        Schema::table('phase_templates', function (Blueprint $table) {
            $table->dropForeign('phase_templates_external_reviewer_id_foreign');
            $table->dropColumn('external_reviewer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phase_template_external');

        Schema::table('phase_templates', function (Blueprint $table) {
            $table->foreignId('external_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
