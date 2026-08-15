<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conference_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_fee_id')->constrained('registration_fees')->cascadeOnDelete();
            $table->boolean('is_early_bird')->default(false);
            $table->enum('currency', ['IDR', 'USD'])->default('IDR');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'waiting_verification', 'paid', 'rejected', 'expired'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
