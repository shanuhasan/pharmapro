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
        Schema::table('sales', function (Blueprint $table) {
            $table->text('customer_address')->nullable()->after('customer_phone');
            $table->string('doctor_name')->nullable()->after('customer_address');
            $table->text('doctor_address')->nullable()->after('doctor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['customer_address', 'doctor_name', 'doctor_address']);
        });
    }
};
