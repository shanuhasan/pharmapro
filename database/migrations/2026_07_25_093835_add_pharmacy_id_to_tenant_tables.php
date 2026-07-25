<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'users',
            'branches',
            'medicines',
            'medicine_categories',
            'units',
            'suppliers',
            'customers',
            'expenses',
            'purchases',
            'sales',
            'stock'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'pharmacy_id')) {
                    $table->foreignId('pharmacy_id')->nullable()->constrained('pharmacies')->onDelete('cascade');
                }
            });
        }
        
        // Update user roles to include super_admin
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'pharmacist', 'cashier') NOT NULL DEFAULT 'cashier'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'branches',
            'medicines',
            'medicine_categories',
            'units',
            'suppliers',
            'customers',
            'expenses',
            'purchases',
            'sales',
            'stock'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'pharmacy_id')) {
                    $table->dropForeign(['pharmacy_id']);
                    $table->dropColumn('pharmacy_id');
                }
            });
        }
        
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'pharmacist', 'cashier') NOT NULL DEFAULT 'cashier'");
    }
};
