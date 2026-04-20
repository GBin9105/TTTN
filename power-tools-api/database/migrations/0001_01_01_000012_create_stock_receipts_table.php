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
        Schema::create('stock_receipts', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();

            $table->string('supplier_name')->nullable();
            $table->string('supplier_phone', 20)->nullable();
            $table->string('supplier_email')->nullable();
            $table->string('supplier_address')->nullable();

            $table->unsignedInteger('total_quantity')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->text('note')->nullable();

            $table->enum('status', ['draft', 'completed', 'cancelled'])->default('completed');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('imported_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'imported_at']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_receipts');
    }
};