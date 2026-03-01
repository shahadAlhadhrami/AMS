<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phase_rubric_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phase_template_id')->constrained('phase_templates')->cascadeOnDelete();
            $table->foreignId('rubric_template_id')->constrained('rubric_templates')->cascadeOnDelete();
            $table->string('evaluator_role');
            $table->integer('fill_order');
            $table->decimal('max_marks', 8, 2);
            $table->enum('aggregation_method', ['AVERAGE', 'WEIGHTED_AVERAGE', 'SUM', 'MAX'])->default('AVERAGE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phase_rubric_rules');
    }
};
