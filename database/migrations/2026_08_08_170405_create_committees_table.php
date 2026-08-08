<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role');                        // e.g. "Chairman", "Secretary"
            $table->string('institution')->nullable();
            $table->enum('group', ['steering', 'organizing', 'scientific'])->default('organizing');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
};
