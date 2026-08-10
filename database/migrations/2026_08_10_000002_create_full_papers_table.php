<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('full_papers', function (Blueprint $table) {
            $table->id();
            $table->string('paper_code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->foreignId('abstract_id')->nullable()->constrained('abstracts')->nullOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->enum('status', ['pending', 'under_review', 'revision_required', 'accepted', 'rejected'])->default('pending');
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('full_papers');
    }
};
