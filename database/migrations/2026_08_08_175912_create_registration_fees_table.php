<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('mode', ['offline', 'online'])->default('offline');
            $table->enum('type', ['presenter', 'non_presenter'])->default('presenter');
            $table->string('category')->nullable(); // national, student, international
            $table->decimal('price', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_fees');
    }
};
