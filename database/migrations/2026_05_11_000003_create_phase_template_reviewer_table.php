<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phase_template_reviewer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phase_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['phase_template_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phase_template_reviewer');
    }
};
