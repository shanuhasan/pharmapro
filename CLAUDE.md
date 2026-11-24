# CLAUDE.md

This file guides Claude Code (claude.ai/code) when it works in this repository.

## What this is

PharmaPro is a multi-tenant (SaaS-style) pharmacy management system. It covers POS billing, purchases, batch-wise stock, returns, expenses, reports and invoices. It runs on **Laravel 12 / PHP 8.2** with Blade views, Breeze auth, Tailwind and Alpine. It runs locally under XAMPP with **MySQL** (`DB_DATABASE=pharma_pro`). The currency defaults to ₹, and the purchase importers target Indian distributor formats.

## Commands

```bash
composer dev            # serve + queue:listen + pail logs + vite, all concurrently
composer test           # config:clear then php artisan test
php artisan test --filter=AuthenticationTest   # single test
npm run dev / npm run build
php artisan migrate --seed   # seeders: Admin (admin@example.com / password), Branch, MedicineCategory, Unit
vendor/bin/pint         # code style
```

Tests run against in-memory SQLite (`phpunit.xml`). The migration `2026_07_25_093835_add_pharmacy_id_to_tenant_tables` uses a MySQL-only `ALTER TABLE ... MODIFY COLUMN role ENUM(...)`, so the migrations, and therefore the DB-backed tests, may fail on SQLite. The only tests are the default Breeze ones; nothing covers the domain logic.

## Architecture

### Multi-tenancy (the most important thing to understand)
- A `Pharmacy` is the tenant. Tenant tables carry a `pharmacy_id`: users, branches, medicines, medicine_categories, units, suppliers, customers, expenses, purchases, sales, stock, settings.
- The [app/Traits/HasPharmacy.php](app/Traits/HasPharmacy.php) trait adds the global [PharmacyScope](app/Models/Scopes/PharmacyScope.php). The scope filters every query by `auth()->user()->pharmacy_id`, except for `super_admin`. The trait also fills in `pharmacy_id` automatically when a record is created.
- **Any new tenant model must `use \App\Traits\HasPharmacy;`**, and its table needs a `pharmacy_id` column. Child/line-item models (SaleItem, PurchaseItem, StockLog, *ReturnItem) do not carry it; they are scoped only through their parent.
- Raw `DB::table()` queries and `Rule::unique()`/`exists` validation bypass the scope. Add the `pharmacy_id` condition by hand, as `PurchaseController` does for `invoice_number`.
- [CheckPharmacyStatus](app/Http/Middleware/CheckPharmacyStatus.php) is appended to the `web` group. It logs out users whose pharmacy is inactive, or whose pharmacy was created more than one year ago (membership expiry).

### Roles and branches
- `users.role` enum: `super_admin`, `admin`, `manager`, `pharmacist`, `cashier`. Roles are plain string checks. `spatie/laravel-permission` is installed but not used.
- `super_admin` has no pharmacy. They use `/super-admin/*` (`SuperAdminController`) to create pharmacies, each with its first admin, and to toggle pharmacies active or inactive.
- `admin` sees every branch of their pharmacy (`branch_id` is null) and can pass `branch_id` to filter. Other roles are locked to `auth()->user()->branch_id`. Controllers repeat this pattern inline: `role === 'admin' ? $request->branch_id : auth()->user()->branch_id`.
- Middleware aliases are declared in [bootstrap/app.php](bootstrap/app.php): `role:admin,...` (CheckRole) and `super_admin`. `AdminMiddleware` guards `users`. `SettingController` checks the role inline.

### Domain flow
- **Stock** (table `stock`, model `Stock`) is batch-level: one row per (branch, medicine, batch_number) with expiry, purchase price and sale price. `StockService::addStock` merges stock into an existing batch or creates a new one.
- **Purchases** add stock through `StockService`. `PurchaseController::importFile` parses distributor CSV/Excel files: it detects the RetailShakti, Neelkanth Pharma and "DR" formats and has a `parse*Row` method for each.
- **POS** (`PosController` + `pos/index.blade.php`) uses JSON endpoints under `/api/...`, defined in `web.php` with session auth. `SaleController::store` returns JSON. It runs in a DB transaction, deducts stock per `stock_id`, and writes a `StockLog`. The invoice number format is `{invoice_prefix}{branchId}-{Ymd}-{NNN}`.
- **Every stock mutation must write a `StockLog`** (`type`: adjustment / transfer_in / transfer_out), recording previous, changed and new quantities.
- **Returns** (`ReturnController`) cover purchase returns and sale returns, and adjust stock and logs.
- **Invoices**: `InvoiceService` renders `invoices/template.blade.php` with DomPDF. There are also print and email routes (`SaleInvoiceMail`).
- **Reports**: `ReportController` plus the `app/Exports/*` classes (maatwebsite/excel). Medicines can be bulk-imported with `MedicinesImport`.

### Settings
- The global `setting($key, $default)` helper lives in [app/Helpers/SettingsHelper.php](app/Helpers/SettingsHelper.php) and is loaded by `AppServiceProvider::register`. It reads a per-pharmacy key/value cache, `app_settings_{pharmacy_id}`, kept with `rememberForever`. Clear that cache key whenever settings change (`SettingController` does). Common keys are `currency_symbol` (default `'₹'`), `invoice_prefix` and `pharmacy_name`.

### Frontend conventions
- The authenticated pages use `<x-admin-layout>` ([layouts/admin.blade.php](resources/views/layouts/admin.blade.php), with the role-aware [layouts/sidebar.blade.php](resources/views/layouts/sidebar.blade.php)).
- jQuery, Select2, DataTables, Chart.js and FontAwesome are vendored under `public/vendor/` and loaded with `asset()`, not npm. Select2 attaches automatically to every `<select>` except those with the `.hidden` or `.no-select2` class.
- List pages use **Yajra DataTables** server-side. On `$request->ajax()` the `index()` method returns `DataTables::of(...)`, with action-button HTML built in PHP and passed through `rawColumns`. Otherwise it returns the view.
- Tailwind theme colors: `primary` (teal), `secondary` (emerald), and `medical.*`.

## Repo quirks
- The root-level `*.php` scripts (`generate_migrations.php`, `generate_models.php`, `refactor.php`, `replace_currency.php`, `replace_raw_currency.php`, `clean_flash.php`, `read_excel.php`, `test.php`, `test_dt.php`) are one-off scaffolding and debug scripts, not part of the app. Some contain hard-coded absolute paths, so don't run them casually.
- `README.md` is the stock Laravel README. `PharmaPro_Synopsis.doc` is the project synopsis.
- Commit messages in the history are terse ("updates").
