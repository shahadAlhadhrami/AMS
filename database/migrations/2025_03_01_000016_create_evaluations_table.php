<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('rubric_template_id')->constrained('rubric_templates')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->string('evaluator_role');
            $table->foreignId('on_behalf_of_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('evidence_attachment_path')->nullable();
            $table->enum('status', ['pending', 'draft', 'submitted'])->default('pending');
            $table->text('general_feedback')->nullable();
            $table->foreignId('unlocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'rubric_template_id', 'evaluator_id'], 'unique_evaluation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
