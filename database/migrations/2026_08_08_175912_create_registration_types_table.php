<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('name');                             // e.g. "International Presenter (Student)"
            $table->enum('category', ['student', 'non_student'])->default('non_student');
            $table->enum('role_type', ['presenter', 'attendee'])->default('presenter');
            $table->boolean('is_international')->default(false);
            $table->decimal('early_bird_price_idr', 12, 2)->default(0);
            $table->decimal('regular_price_idr', 12, 2)->default(0);
            $table->decimal('early_bird_price_usd', 8, 2)->default(0);
            $table->decimal('regular_price_usd', 8, 2)->default(0);
            $table->date('early_bird_deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_types');
    }
};
