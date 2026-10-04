<?php

use App\Models\AbstractSubmission;
use App\Models\Profile;
use App\Models\Registration;
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
        Schema::table('profiles', function (Blueprint $table) {
            $table->enum('participation_type', ['presenter', 'non_presenter'])
                ->default('presenter')
                ->after('user_id');
        });

        // Backfill existing user profiles intelligently
        $nonPresenterUserIds = Registration::whereHas('registrationFee', function ($query) {
            $query->where('type', 'non_presenter');
        })->pluck('user_id')->unique();

        Profile::whereIn('user_id', $nonPresenterUserIds)
            ->update(['participation_type' => 'non_presenter']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('participation_type');
        });
    }
};
