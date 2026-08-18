<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create optimized indexes for search feature on ws_user table
     */
    public function up(): void
    {
        // Only create indexes if the ws_user table exists
        if (!Schema::hasTable('ws_user')) {
            return;
        }

        try {
            // Create index for exact email search
            DB::statement('CREATE INDEX IF NOT EXISTS idx_ws_user_email ON ws_user (user_email)');

            // Create index for exact phone search
            DB::statement('CREATE INDEX IF NOT EXISTS idx_ws_user_msisdn ON ws_user (msisdn)');

            // Enable pg_trgm extension for fuzzy search if not already enabled
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

            // Create GIN index for full_name fuzzy/substring search
            DB::statement('CREATE INDEX IF NOT EXISTS idx_ws_user_full_name_trgm ON ws_user USING GIN (full_name gin_trgm_ops)');
        } catch (\Exception $e) {
            // Log but don't fail if indexes can't be created
            logger()->warning('Failed to create search indexes: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('ws_user')) {
            return;
        }

        try {
            // Drop indexes in reverse order
            DB::statement('DROP INDEX IF EXISTS idx_ws_user_full_name_trgm');
            DB::statement('DROP INDEX IF EXISTS idx_ws_user_msisdn');
            DB::statement('DROP INDEX IF EXISTS idx_ws_user_email');
        } catch (\Exception $e) {
            logger()->warning('Failed to drop search indexes: ' . $e->getMessage());
        }
    }
};


