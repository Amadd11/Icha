<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            if (!Schema::hasColumn('conferences', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('poster');
            }
            if (!Schema::hasColumn('conferences', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('conferences', 'bank_account_holder')) {
                $table->string('bank_account_holder')->nullable()->after('bank_account_number');
            }
            if (!Schema::hasColumn('conferences', 'bank_instructions')) {
                $table->text('bank_instructions')->nullable()->after('bank_account_holder');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
                'bank_instructions',
            ]);
        });
    }
};
