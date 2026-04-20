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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');

            $table->enum('status', ['new', 'replied', 'closed'])->default('new');
            $table->text('reply_message')->nullable();

            $table->foreignId('replied_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('replied_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['replied_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};