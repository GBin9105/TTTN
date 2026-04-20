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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();

            // Thông tin cơ bản
            $table->string('name')->comment('Tên thuộc tính: Màu sắc, Kích thước, Công suất...');
            $table->string('slug')->unique()->comment('Slug thuộc tính');
            $table->string('code')->nullable()->unique()->comment('Mã kỹ thuật, ví dụ: mau-sac, cong-suat');

            // Kiểu dữ liệu thuộc tính
            $table->enum('type', ['text', 'number', 'boolean', 'select'])
                ->default('text')
                ->comment('Kiểu dữ liệu của thuộc tính');

            // Nghiệp vụ hiển thị/lọc
            $table->boolean('is_filterable')->default(false)
                ->comment('Có dùng để lọc sản phẩm ở client không');
            $table->boolean('is_variant')->default(false)
                ->comment('Có dùng làm biến thể sản phẩm không');
            $table->boolean('status')->default(true)
                ->comment('true: hoạt động, false: khóa');

            // Sắp xếp
            $table->unsignedInteger('sort_order')->default(0)
                ->comment('Thứ tự hiển thị');

            // Mô tả
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index phục vụ quản trị
            $table->index(['status', 'sort_order']);
            $table->index('name');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};