<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            // true while the current membership period is a free trial
            $table->boolean('on_trial')->default(false)->after('expires_at');
        });

        Schema::table('pharmacy_renewals', function (Blueprint $table) {
            $table->unsignedSmallInteger('trial_days')->nullable()->after('months');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_renewals', function (Blueprint $table) {
            $table->dropColumn('trial_days');
        });

        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropColumn('on_trial');
        });
    }
};
