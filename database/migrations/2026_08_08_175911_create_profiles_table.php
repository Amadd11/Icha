<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->string('institution')->nullable();
            $table->string('country')->default('Indonesia');
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->enum('participant_category', ['student', 'non_student'])->default('non_student');
            $table->string('identity_number')->nullable(); // KTP / NIM / Passport
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
