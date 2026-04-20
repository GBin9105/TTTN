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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('attribute_id')
                ->constrained('attributes')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Giá trị thuộc tính
            |--------------------------------------------------------------------------
            | Tùy theo type của attribute mà dùng cột phù hợp:
            | - text/select  -> value
            | - number       -> numeric_value
            | - boolean      -> boolean_value
            |--------------------------------------------------------------------------
            */
            $table->string('value')->nullable()->comment('Giá trị text/select, ví dụ: Xanh, 800W');
            $table->decimal('numeric_value', 15, 2)->nullable()->comment('Giá trị số nếu thuộc tính là number');
            $table->boolean('boolean_value')->nullable()->comment('Giá trị true/false nếu thuộc tính là boolean');

            $table->timestamps();

            // Mỗi sản phẩm chỉ có 1 giá trị cho 1 thuộc tính
            $table->unique(['product_id', 'attribute_id']);

            // Index hỗ trợ lọc
            $table->index(['attribute_id', 'value']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};