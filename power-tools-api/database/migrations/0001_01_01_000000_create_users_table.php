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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Thông tin cơ bản
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('address')->nullable();

            // Phân quyền và trạng thái
            $table->enum('role', ['admin', 'customer'])->default('customer');
            $table->boolean('status')->default(true)->comment('true: active, false: inactive');

            // Xác thực
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Audit cơ bản
            $table->timestamp('last_login_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index phục vụ lọc / tìm kiếm quản trị
            $table->index(['role', 'status']);
            $table->index(['name']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};