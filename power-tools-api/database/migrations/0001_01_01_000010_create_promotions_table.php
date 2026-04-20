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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();

            $table->enum('type', ['fixed_amount', 'percent', 'fixed_price']);
            $table->decimal('value', 15, 2);

            $table->unsignedInteger('priority')->default(0);
            $table->boolean('status')->default(true);

            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status', 'priority']);
            $table->index(['start_at', 'end_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};