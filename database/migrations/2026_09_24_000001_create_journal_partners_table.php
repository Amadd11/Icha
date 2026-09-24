<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_partners', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conference_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('journal_name');
            $table->string('university');

            $table->string('website')->nullable();

            // Isi panjang seperti pada Excel
            $table->longText('submission_requirements')->nullable();

            // "Ya, jurnal dapat menerbitkan LoA..."
            $table->text('loa_information')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_partners');
    }
};
