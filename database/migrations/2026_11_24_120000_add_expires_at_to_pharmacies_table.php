<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->date('expires_at')->nullable()->after('is_active');
        });

        // Keep existing memberships exactly as they were: 1 year from registration.
        DB::table('pharmacies')->whereNull('expires_at')->orderBy('id')->each(function ($pharmacy) {
            $start = $pharmacy->created_at ? Carbon::parse($pharmacy->created_at) : now();
            DB::table('pharmacies')->where('id', $pharmacy->id)->update([
                'expires_at' => $start->copy()->addYear()->toDateString(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
