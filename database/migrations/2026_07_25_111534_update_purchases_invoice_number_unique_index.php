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
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique('purchases_invoice_number_unique');
            $table->unique(['pharmacy_id', 'invoice_number'], 'purchases_pharmacy_invoice_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique('purchases_pharmacy_invoice_unique');
            $table->unique('invoice_number', 'purchases_invoice_number_unique');
        });
    }
};
