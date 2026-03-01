<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rubric_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('version')->default(1);
            $table->foreignId('parent_template_id')->nullable()
                  ->constrained('rubric_templates')->nullOnDelete();
            $table->decimal('total_marks', 8, 2)->default(0);
            $table->boolean('is_locked')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_templates');
    }
};
