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
        // 1. Drop the full_papers table if it exists
        Schema::dropIfExists('full_papers');

        // 2. Drop paper-related columns from conferences table
        Schema::table('conferences', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('conferences', 'paper_deadline')) {
                $columnsToDrop[] = 'paper_deadline';
            }
            if (Schema::hasColumn('conferences', 'paper_template')) {
                $columnsToDrop[] = 'paper_template';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            if (!Schema::hasColumn('conferences', 'paper_deadline')) {
                $table->date('paper_deadline')->nullable()->after('abstract_deadline');
            }
            if (!Schema::hasColumn('conferences', 'paper_template')) {
                $table->string('paper_template')->nullable()->after('abstract_template');
            }
        });

        if (!Schema::hasTable('full_papers')) {
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
                $table->softDeletes();
            });
        }
    }
};
