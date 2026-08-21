<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            if (!Schema::hasColumn('conferences', 'hero_images')) {
                $table->json('hero_images')->nullable()->after('logo');
            }
            if (Schema::hasColumn('conferences', 'hero_image')) {
                $table->dropColumn('hero_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            if (Schema::hasColumn('conferences', 'hero_images')) {
                $table->dropColumn('hero_images');
            }
            if (!Schema::hasColumn('conferences', 'hero_image')) {
                $table->string('hero_image')->nullable()->after('logo');
            }
        });
    }
};
