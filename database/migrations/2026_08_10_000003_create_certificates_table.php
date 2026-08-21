<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['participant', 'presenter', 'speaker'])->default('participant');
            $table->string('role_title')->nullable(); // e.g. "Presenter / Author", "Keynote Speaker", "Participant"
            $table->string('file_path')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
