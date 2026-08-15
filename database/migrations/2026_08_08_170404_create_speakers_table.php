<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('title')->nullable();       // e.g. "Prof. Dr."
            $table->string('institution')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code', 5)->nullable();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();       // storage path
            $table->string('email')->nullable();
            $table->enum('type', ['keynote', 'invited', 'speaker', 'plenary'])->default('invited');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('speakers');
    }
};
