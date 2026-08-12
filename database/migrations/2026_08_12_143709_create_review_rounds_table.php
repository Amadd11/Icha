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
        Schema::create('review_rounds', function (Blueprint $table) {
            $table->id();
            $table->string('submission_type', 50); // abstract or full_paper
            $table->unsignedBigInteger('submission_id');
            $table->string('status', 50)->default('pending'); // pending, locked, completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_rounds');
    }
};
