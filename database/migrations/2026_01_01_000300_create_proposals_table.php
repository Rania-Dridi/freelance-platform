<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->unsignedInteger('price');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['project_id', 'freelancer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
