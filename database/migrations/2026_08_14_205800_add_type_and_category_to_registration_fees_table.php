<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('registration_fees', 'type')) {
                $table->enum('type', ['presenter', 'non_presenter'])->default('presenter')->after('mode');
            }
            if (!Schema::hasColumn('registration_fees', 'category')) {
                $table->string('category')->nullable()->after('type');
            }
            if (!Schema::hasColumn('registration_fees', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registration_fees', function (Blueprint $table) {
            if (Schema::hasColumn('registration_fees', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('registration_fees', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('registration_fees', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
