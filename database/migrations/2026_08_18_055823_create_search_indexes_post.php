<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Create indexes on ws_user table after table is created
     */
    public function up(): void
    {
        try {
            // Create index for exact email search
            DB::statement('CREATE INDEX IF NOT EXISTS idx_ws_user_email ON ws_user (user_email)');

            // Create index for exact phone search
            DB::statement('CREATE INDEX IF NOT EXISTS idx_ws_user_msisdn ON ws_user (msisdn)');

            // Enable pg_trgm extension for fuzzy search
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

            // Create GIN index for full_name fuzzy/substring search
            DB::statement('CREATE INDEX IF NOT EXISTS idx_ws_user_full_name_trgm ON ws_user USING GIN (full_name gin_trgm_ops)');

            logger()->info('Search indexes created successfully');
        } catch (\Exception $e) {
            logger()->warning('Note: Some indexes may not have been created. ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
