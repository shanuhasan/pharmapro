<?php

$modelsPath = __DIR__ . '/app/Models/';

$models = [
    'Branch' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected \$guarded = [];

    public function users(): HasMany
    {
        return \$this->hasMany(User::class);
    }

    public function stock(): HasMany
    {
        return \$this->hasMany(Stock::class);
    }

    public function sales(): HasMany
    {
        return \$this->hasMany(Sale::class);
    }

    public function purchases(): HasMany
    {
        return \$this->hasMany(Purchase::class);
    }
}
EOT,
    'Medicine' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected \$guarded = [];

    public function medicineCategory(): BelongsTo
    {
        return \$this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return \$this->belongsTo(Unit::class);
    }

    public function stock(): HasMany
    {
        return \$this->hasMany(Stock::class);
    }

    public function saleItems(): HasMany
    {
        return \$this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return \$this->hasMany(PurchaseItem::class);
    }
}
EOT,
    'Purchase' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected \$guarded = [];

    public function branch(): BelongsTo
    {
        return \$this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return \$this->belongsTo(Supplier::class);
    }

    public function purchaseItems(): HasMany
    {
        return \$this->hasMany(PurchaseItem::class);
    }
}
EOT,
    'PurchaseItem' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected \$guarded = [];

    public function medicine(): BelongsTo
    {
        return \$this->belongsTo(Medicine::class);
    }
    
    public function purchase(): BelongsTo
    {
        return \$this->belongsTo(Purchase::class);
    }
}
EOT,
    'Sale' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected \$guarded = [];

    public function branch(): BelongsTo
    {
        return \$this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return \$this->belongsTo(Customer::class);
    }

    public function saleItems(): HasMany
    {
        return \$this->hasMany(SaleItem::class);
    }
}
EOT,
    'SaleItem' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected \$guarded = [];

    public function medicine(): BelongsTo
    {
        return \$this->belongsTo(Medicine::class);
    }

    public function stock(): BelongsTo
    {
        return \$this->belongsTo(Stock::class);
    }
    
    public function sale(): BelongsTo
    {
        return \$this->belongsTo(Sale::class);
    }
}
EOT,
    'Stock' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Stock extends Model
{
    use HasFactory;

    protected \$table = 'stock';
    protected \$guarded = [];

    public function branch(): BelongsTo
    {
        return \$this->belongsTo(Branch::class);
    }

    public function medicine(): BelongsTo
    {
        return \$this->belongsTo(Medicine::class);
    }

    public function scopeExpiringSoon(Builder \$query): void
    {
        \$query->where('expiry_date', '<=', now()->addDays(30))
              ->where('expiry_date', '>=', now());
    }

    public function scopeLowStock(Builder \$query): void
    {
        \$query->where('quantity', '<', 10);
    }
}
EOT,
];

// Simple boilerplate for others
\$others = ['MedicineCategory', 'Unit', 'Supplier', 'Customer', 'PurchaseReturn', 'PurchaseReturnItem', 'SaleReturn', 'SaleReturnItem', 'Expense'];

foreach (\$others as \$model) {
    \$models[\$model] = <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {\$model} extends Model
{
    use HasFactory;

    protected \$guarded = [];
}
EOT;
}

foreach (\$models as \$name => \$content) {
    file_put_contents(\$modelsPath . \$name . '.php', \$content);
    echo "Created {\$name}.php\n";
}
