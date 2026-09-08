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
        Schema::table('conferences', function (Blueprint $table) {
            $table->date('abstract_open_date')->nullable()->after('end_date');
            $table->date('abstract_deadline')->nullable()->after('abstract_open_date');
            $table->date('paper_deadline')->nullable()->after('abstract_deadline');
            $table->text('address')->nullable()->after('venue');
            $table->dropColumn('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('email');
            $table->dropColumn([
                'abstract_open_date',
                'abstract_deadline',
                'paper_deadline',
                'address',
            ]);
        });
    }
};
