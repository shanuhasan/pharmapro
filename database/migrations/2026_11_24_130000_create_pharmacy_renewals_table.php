<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();
            $table->foreignId('renewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('previous_expires_at')->nullable();
            $table->date('new_expires_at');
            $table->string('renew_type', 10);              // months | date
            $table->unsignedSmallInteger('months')->nullable();
            $table->decimal('amount', 10, 2)->nullable();   // optional, for billing records
            $table->string('note', 500)->nullable();
            $table->boolean('reactivated')->default(false);
            $table->timestamps();

            $table->index(['pharmacy_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_renewals');
    }
};
