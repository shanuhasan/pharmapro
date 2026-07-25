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
            // Drop the existing unique constraint
            $table->dropUnique('settings_key_unique');
            
            // Add a new unique constraint that includes pharmacy_id
            $table->unique(['key', 'pharmacy_id'], 'settings_key_pharmacy_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_pharmacy_id_unique');
            $table->unique('key', 'settings_key_unique');
        });
    }
};
