<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_rounds', function (Blueprint $table) {
            if (!Schema::hasColumn('review_rounds', 'round_number')) {
                $table->integer('round_number')->default(1)->after('submission_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('review_rounds', function (Blueprint $table) {
            if (Schema::hasColumn('review_rounds', 'round_number')) {
                $table->dropColumn('round_number');
            }
        });
    }
};
