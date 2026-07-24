<?php

$migrations = [
    'create_branches_table' => function() {
        return "
            \$table->id();
            \$table->string('name');
            \$table->string('address')->nullable();
            \$table->string('phone')->nullable();
            \$table->string('email')->nullable();
            \$table->string('city')->nullable();
            \$table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        ";
    },
    'add_branch_role_to_users_table' => function() {
        return "
            \$table->enum('role', ['admin', 'manager', 'pharmacist', 'cashier'])->default('cashier');
            \$table->foreignId('branch_id')->nullable()->constrained('branches');
            \$table->string('phone')->nullable();
            \$table->boolean('is_active')->default(true);
        ";
    },
    'create_medicine_categories_table' => function() {
        return "
            \$table->id();
            \$table->string('name');
            \$table->text('description')->nullable();
            \$table->timestamps();
        ";
    },
    'create_units_table' => function() {
        return "
            \$table->id();
            \$table->string('name');
            \$table->string('abbreviation')->nullable();
            \$table->timestamps();
        ";
    },
    'create_medicines_table' => function() {
        return "
            \$table->id();
            \$table->string('name');
            \$table->string('generic_name')->nullable();
            \$table->foreignId('category_id')->constrained('medicine_categories');
            \$table->foreignId('unit_id')->constrained('units');
            \$table->string('manufacturer')->nullable();
            \$table->text('description')->nullable();
            \$table->boolean('requires_prescription')->default(false);
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        ";
    },
    'create_suppliers_table' => function() {
        return "
            \$table->id();
            \$table->string('name');
            \$table->string('company')->nullable();
            \$table->string('phone')->nullable();
            \$table->string('email')->nullable();
            \$table->string('address')->nullable();
            \$table->string('city')->nullable();
            \$table->string('ntn_number')->nullable();
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        ";
    },
    'create_purchases_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('branch_id')->constrained('branches');
            \$table->foreignId('supplier_id')->constrained('suppliers');
            \$table->string('invoice_number')->unique();
            \$table->date('purchase_date');
            \$table->decimal('total_amount', 15, 2);
            \$table->decimal('discount', 15, 2)->default(0);
            \$table->decimal('tax', 15, 2)->default(0);
            \$table->decimal('paid_amount', 15, 2)->default(0);
            \$table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            \$table->text('notes')->nullable();
            \$table->foreignId('created_by')->constrained('users');
            \$table->timestamps();
        ";
    },
    'create_purchase_items_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            \$table->foreignId('medicine_id')->constrained('medicines');
            \$table->string('batch_number');
            \$table->date('expiry_date')->nullable();
            \$table->integer('quantity');
            \$table->decimal('purchase_price', 15, 2);
            \$table->decimal('sale_price', 15, 2);
            \$table->decimal('total', 15, 2);
            \$table->timestamps();
        ";
    },
    'create_stock_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('branch_id')->constrained('branches');
            \$table->foreignId('medicine_id')->constrained('medicines');
            \$table->string('batch_number');
            \$table->date('expiry_date')->nullable();
            \$table->integer('quantity');
            \$table->decimal('purchase_price', 15, 2);
            \$table->decimal('sale_price', 15, 2);
            \$table->timestamps();
        ";
    },
    'create_customers_table' => function() {
        return "
            \$table->id();
            \$table->string('name');
            \$table->string('phone')->nullable();
            \$table->string('email')->nullable();
            \$table->string('address')->nullable();
            \$table->string('city')->nullable();
            \$table->date('dob')->nullable();
            \$table->text('notes')->nullable();
            \$table->timestamps();
        ";
    },
    'create_sales_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('branch_id')->constrained('branches');
            \$table->foreignId('customer_id')->nullable()->constrained('customers');
            \$table->string('invoice_number')->unique();
            \$table->date('sale_date');
            \$table->decimal('subtotal', 15, 2);
            \$table->decimal('discount', 15, 2)->default(0);
            \$table->decimal('tax', 15, 2)->default(0);
            \$table->decimal('total_amount', 15, 2);
            \$table->decimal('paid_amount', 15, 2)->default(0);
            \$table->decimal('change_amount', 15, 2)->default(0);
            \$table->enum('payment_method', ['cash', 'card', 'online'])->default('cash');
            \$table->enum('status', ['completed', 'refunded', 'partial'])->default('completed');
            \$table->text('notes')->nullable();
            \$table->foreignId('created_by')->constrained('users');
            \$table->timestamps();
        ";
    },
    'create_sale_items_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            \$table->foreignId('medicine_id')->constrained('medicines');
            \$table->foreignId('stock_id')->constrained('stock');
            \$table->string('batch_number');
            \$table->integer('quantity');
            \$table->decimal('sale_price', 15, 2);
            \$table->decimal('discount', 15, 2)->default(0);
            \$table->decimal('total', 15, 2);
            \$table->timestamps();
        ";
    },
    'create_purchase_returns_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('purchase_id')->constrained('purchases');
            \$table->foreignId('branch_id')->constrained('branches');
            \$table->foreignId('supplier_id')->constrained('suppliers');
            \$table->date('return_date');
            \$table->decimal('total_amount', 15, 2);
            \$table->text('reason')->nullable();
            \$table->foreignId('created_by')->constrained('users');
            \$table->timestamps();
        ";
    },
    'create_purchase_return_items_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('return_id')->constrained('purchase_returns')->onDelete('cascade');
            \$table->foreignId('medicine_id')->constrained('medicines');
            \$table->string('batch_number');
            \$table->integer('quantity');
            \$table->decimal('price', 15, 2);
            \$table->decimal('total', 15, 2);
        ";
    },
    'create_sale_returns_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('sale_id')->constrained('sales');
            \$table->foreignId('branch_id')->constrained('branches');
            \$table->foreignId('customer_id')->nullable()->constrained('customers');
            \$table->date('return_date');
            \$table->decimal('total_amount', 15, 2);
            \$table->text('reason')->nullable();
            \$table->foreignId('created_by')->constrained('users');
            \$table->timestamps();
        ";
    },
    'create_sale_return_items_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('return_id')->constrained('sale_returns')->onDelete('cascade');
            \$table->foreignId('medicine_id')->constrained('medicines');
            \$table->integer('quantity');
            \$table->decimal('price', 15, 2);
            \$table->decimal('total', 15, 2);
        ";
    },
    'create_expenses_table' => function() {
        return "
            \$table->id();
            \$table->foreignId('branch_id')->constrained('branches');
            \$table->string('category');
            \$table->text('description')->nullable();
            \$table->decimal('amount', 15, 2);
            \$table->date('expense_date');
            \$table->foreignId('created_by')->constrained('users');
            \$table->timestamps();
        ";
    }
];

foreach (\$migrations as \$name => \$callback) {
    echo "Running php artisan make:migration \$name\n";
    shell_exec("php artisan make:migration \$name");
    
    // Find the generated file
    \$files = glob(__DIR__ . "/database/migrations/*_\$name.php");
    if (count(\$files) > 0) {
        \$file = \$files[0];
        \$content = file_get_contents(\$file);
        
        // We will replace the contents of Schema::create or Schema::table
        \$columns = \$callback();
        
        if (strpos(\$name, 'add_') === 0) {
            // It's a Schema::table
            \$pattern = '/Schema::table\([^,]+,\s*function\s*\(Blueprint\s*\$table\)\s*\{([^}]+)\}\);/s';
            \$replacement = "Schema::table('users', function (Blueprint \$table) { \$columns });";
            \$content = preg_replace(\$pattern, \$replacement, \$content);
        } else {
            // It's Schema::create
            \$pattern = '/Schema::create\([^,]+,\s*function\s*\(Blueprint\s*\$table\)\s*\{([^}]+)\}\);/s';
            preg_match('/Schema::create\(\\\'([^\\\']+)\\\'/', \$content, \$matches);
            \$tableName = \$matches[1] ?? str_replace(['create_', '_table'], '', \$name);
            \$replacement = "Schema::create('\$tableName', function (Blueprint \$table) { \$columns });";
            \$content = preg_replace(\$pattern, \$replacement, \$content);
        }
        
        file_put_contents(\$file, \$content);
        echo "Updated \$file\n";
    }
}
