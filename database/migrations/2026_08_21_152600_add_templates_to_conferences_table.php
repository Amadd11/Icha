<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            if (!Schema::hasColumn('conferences', 'abstract_template')) {
                $table->string('abstract_template')->nullable()->after('bank_instructions');
            }
            if (!Schema::hasColumn('conferences', 'paper_template')) {
                $table->string('paper_template')->nullable()->after('abstract_template');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropColumn([
                'abstract_template',
                'paper_template',
            ]);
        });
    }
};
