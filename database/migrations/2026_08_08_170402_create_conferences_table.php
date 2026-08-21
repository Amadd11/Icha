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
            $table->string('slug')->unique();
            $table->year('year');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('theme')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->json('hero_images')->nullable();
            $table->string('poster')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->text('bank_instructions')->nullable();
            $table->string('abstract_template')->nullable();
            $table->string('paper_template')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('active');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conferences');
    }
};
