<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('university_id')->unique()->after('id');
            $table->foreignId('specialization_id')->nullable()->after('email')
                  ->constrained('specializations')->nullOnDelete();
            $table->text('app_authentication_secret')->nullable()->after('password');
            $table->text('app_authentication_recovery_codes')->nullable()->after('app_authentication_secret');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['specialization_id']);
            $table->dropColumn([
                'university_id',
                'specialization_id',
                'app_authentication_secret',
                'app_authentication_recovery_codes',
                'deleted_at',
            ]);
        });
    }
};
