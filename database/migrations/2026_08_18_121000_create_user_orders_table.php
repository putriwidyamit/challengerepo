<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->bigInteger('user_id')->index();
            $table->string('order_number', 50)->unique();
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled']);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('user_id')->references('user_id')->on('ws_user')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_orders');
    }
};
