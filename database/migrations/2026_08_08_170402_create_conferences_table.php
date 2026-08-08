<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Indonesia');
            $table->string('theme')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conferences');
    }
};
