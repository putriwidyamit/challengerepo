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
        Schema::create('ws_user', function (Blueprint $table) {
            $table->bigInteger('user_id')->primary();
            $table->string('user_name', 128)->nullable();
            $table->string('full_name', 128)->nullable();
            $table->string('user_email', 512)->nullable();
            $table->string('msisdn', 20)->nullable();
            $table->smallInteger('status')->default(1);
            $table->timestamp('create_time')->useCurrent();
            $table->timestamp('update_time')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('last_login')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ws_user');
    }
};
