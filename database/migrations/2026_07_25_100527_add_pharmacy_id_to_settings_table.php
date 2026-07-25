<?php

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
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'pharmacy_id')) {
                $table->foreignId('pharmacy_id')->nullable()->constrained('pharmacies')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'pharmacy_id')) {
                $table->dropForeign(['pharmacy_id']);
                $table->dropColumn('pharmacy_id');
            }
        });
    }
};
