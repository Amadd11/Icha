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
            $table->string('slug')->nullable()->unique()->after('title');
            $table->integer('year')->nullable()->after('slug');
            $table->string('logo')->nullable()->after('website');
            $table->string('hero_image')->nullable()->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropColumn(['slug', 'year', 'logo', 'hero_image']);
        });
    }
};
