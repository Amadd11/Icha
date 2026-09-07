<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('full_papers', function (Blueprint $table) {
            $table->unique('abstract_id');
        });
    }

    public function down(): void
    {
        Schema::table('full_papers', function (Blueprint $table) {
            $table->dropUnique(['abstract_id']);
        });
    }
};
