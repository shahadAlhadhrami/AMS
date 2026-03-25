<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('max_marks', 8, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('criteria', function (Blueprint $table) {
            $table->foreignId('deliverable_id')->nullable()->after('rubric_template_id')->constrained('deliverables')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('deliverable_id');
        });

        Schema::table('score_levels', function (Blueprint $table) {
            $table->string('percentage_range')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('score_levels', function (Blueprint $table) {
            $table->dropColumn('percentage_range');
        });

        Schema::table('criteria', function (Blueprint $table) {
            $table->dropForeign(['deliverable_id']);
            $table->dropColumn(['deliverable_id', 'sort_order']);
        });

        Schema::dropIfExists('deliverables');
    }
};
