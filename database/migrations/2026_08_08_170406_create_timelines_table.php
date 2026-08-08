<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->string('period')->nullable();      // e.g. "July - August 2026"
            $table->boolean('is_completed')->default(false);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timelines');
    }
};
