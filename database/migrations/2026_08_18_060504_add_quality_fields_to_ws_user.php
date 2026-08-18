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
        Schema::table('ws_user', function (Blueprint $table) {
            $table->date('birth_date')->nullable();
            $table->text('hobbies')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ws_user', function (Blueprint $table) {
            $table->dropColumn(['birth_date', 'hobbies']);
        });
    }
};
