<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activity', function (Blueprint $table) {
            $table->bigIncrements('activity_id');
            $table->bigInteger('user_id')->index();
            $table->string('activity_type', 50);
            $table->string('activity_action', 100);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('activity_timestamp')->useCurrent();
            $table->foreign('user_id')->references('user_id')->on('ws_user')->onDelete('cascade');
        });

        // Index for common queries
        Schema::table('user_activity', function (Blueprint $table) {
            $table->index(['user_id', 'activity_timestamp']);
            $table->index('activity_timestamp');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity');
    }
};
