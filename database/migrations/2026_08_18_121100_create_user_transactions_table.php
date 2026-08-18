<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->bigIncrements('transaction_id');
            $table->bigInteger('user_id')->index();
            $table->bigInteger('order_id')->nullable()->index();
            $table->enum('type', ['debit', 'credit', 'refund', 'payment']);
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50)->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled']);
            $table->text('description')->nullable();
            $table->timestamp('transaction_time')->useCurrent();
            $table->foreign('user_id')->references('user_id')->on('ws_user')->onDelete('cascade');
            $table->foreign('order_id')->references('order_id')->on('user_orders')->onDelete('set null');
        });

        // Index for common queries
        Schema::table('user_transactions', function (Blueprint $table) {
            $table->index(['user_id', 'transaction_time']);
            $table->index('transaction_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_transactions');
    }
};
