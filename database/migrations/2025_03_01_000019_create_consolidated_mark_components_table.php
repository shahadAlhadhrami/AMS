<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consolidated_mark_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consolidated_mark_id')->constrained('consolidated_marks')->cascadeOnDelete();
            $table->string('source_label');
            $table->decimal('score', 8, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consolidated_mark_components');
    }
};
