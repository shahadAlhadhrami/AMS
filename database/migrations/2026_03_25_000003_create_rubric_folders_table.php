<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rubric_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('parent_id')->nullable()->constrained('rubric_folders')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('rubric_templates', function (Blueprint $table) {
            $table->foreignId('rubric_folder_id')->nullable()->after('parent_template_id')->constrained('rubric_folders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rubric_templates', function (Blueprint $table) {
            $table->dropForeign(['rubric_folder_id']);
            $table->dropColumn('rubric_folder_id');
        });

        Schema::dropIfExists('rubric_folders');
    }
};
