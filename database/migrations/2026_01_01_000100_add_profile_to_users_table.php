<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('client')->after('email');
            $table->string('title')->nullable()->after('role');
            $table->text('bio')->nullable()->after('title');
            $table->string('skills')->nullable()->after('bio');
            $table->unsignedInteger('hourly_rate')->nullable()->after('skills');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'title', 'bio', 'skills', 'hourly_rate']);
        });
    }
};
