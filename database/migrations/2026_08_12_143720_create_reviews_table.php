<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_assignment_id')->constrained()->cascadeOnDelete();
            $table->integer('score_criteria_1')->nullable();
            $table->integer('score_criteria_2')->nullable();
            $table->integer('total_score')->nullable();
            $table->string('recommendation', 50)->nullable(); // ORAL, POSTER, REJECT
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
