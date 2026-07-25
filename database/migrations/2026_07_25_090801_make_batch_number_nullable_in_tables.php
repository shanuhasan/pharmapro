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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->change();
        });
        
        Schema::table('stock', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->change();
        });
        
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->change();
        });
        
        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('batch_number')->nullable(false)->change();
        });
        
        Schema::table('stock', function (Blueprint $table) {
            $table->string('batch_number')->nullable(false)->change();
        });
        
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('batch_number')->nullable(false)->change();
        });
        
        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->string('batch_number')->nullable(false)->change();
        });
    }
};
