<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class LargeDatasetSeeder extends Seeder
{
    /**
     * Generate large dataset with customizable record count and duplicates
     * 
     * Usage: php artisan db:seed --class=LargeDatasetSeeder
     * 
     * Set environment variable to control record count:
     * SEEDER_RECORD_COUNT=50000 php artisan db:seed --class=LargeDatasetSeeder
     * 
     * Duplicate Distribution (adjustable in code):
     * - 30% exact email duplicates (family, shared accounts)
     * - 25% exact phone duplicates (family, shared accounts)
     * - 20% similar names (typos, variations)
     * - 20% unique users
     * - 5% edge cases (null values, special characters)
     */
    
    public function run(): void
    {
        // Get record count from environment or use default
        $recordCount = (int) env('SEEDER_RECORD_COUNT', 1000);
        
        // Validate
        if ($recordCount < 10) {
            $this->command->error('Minimum 10 records required');
            return;
        }
        
        if ($recordCount > 1000000) {
            $this->command->warn('Warning: Large dataset may take time. Current max: 1,000,000');
        }
        
        $this->command->info("Starting seeder with $recordCount records...");
        $startTime = microtime(true);
        
        // Clear existing data
        DB::table('ws_user')->truncate();
        
        $faker = Faker::create();
        $this->command->info('Generating records...');
        
        // Calculate distribution
        $totalRecords = $recordCount;
        $emailDuplicateRecords = (int) ($totalRecords * 0.30);    // 30% shared emails
        $phoneDuplicateRecords = (int) ($totalRecords * 0.25);    // 25% shared phones
        $nameVariationRecords = (int) ($totalRecords * 0.20);     // 20% similar names
        $uniqueRecords = (int) ($totalRecords * 0.20);            // 20% completely unique
        $edgeCaseRecords = $totalRecords - $emailDuplicateRecords - $phoneDuplicateRecords - $nameVariationRecords - $uniqueRecords; // 5% edge cases
        
        $this->command->info("Distribution:");
        $this->command->info("  - Email duplicates: $emailDuplicateRecords (30%)");
        $this->command->info("  - Phone duplicates: $phoneDuplicateRecords (25%)");
        $this->command->info("  - Name variations: $nameVariationRecords (20%)");
        $this->command->info("  - Unique records: $uniqueRecords (20%)");
        $this->command->info("  - Edge cases: $edgeCaseRecords (5%)");
        
        $records = [];
        $userId = 1;
        $batchSize = 1000;
        
        // Helper to batch insert
        $insertBatch = function() use (&$records, &$userId, $batchSize) {
            if (count($records) >= $batchSize) {
                DB::table('ws_user')->insert($records);
                $records = [];
                $this->command->info("✓ Inserted up to user ID $userId...");
            }
        };
        
        // 1. EMAIL DUPLICATES (30%)
        // Shared emails within families/couples
        $this->command->info("\n📧 Generating email duplicates...");
        $sharedEmails = [];
        for ($i = 0; $i < 100; $i++) {
            $sharedEmails[] = strtolower($faker->firstName()) . '.' . strtolower($faker->lastName()) . '@' . $faker->freeEmailDomain();
        }
        
        for ($i = 0; $i < $emailDuplicateRecords; $i++) {
            $baseEmail = $faker->randomElement($sharedEmails);
            $records[] = $this->generateUserRecord(
                $userId++,
                faker: $faker,
                email: $baseEmail,
                phone: $this->generatePhoneNumber(),
                fullName: $faker->name(),
            );
            $insertBatch();
        }
        
        // 2. PHONE DUPLICATES (25%)
        // Shared phones (family members, roommates)
        $this->command->info("📱 Generating phone duplicates...");
        $sharedPhones = [];
        for ($i = 0; $i < 150; $i++) {
            $sharedPhones[] = '08' . str_pad(rand(10, 99), 2, '0', STR_PAD_LEFT) . 
                            str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT) . 
                            str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        for ($i = 0; $i < $phoneDuplicateRecords; $i++) {
            $basePhone = $faker->randomElement($sharedPhones);
            $records[] = $this->generateUserRecord(
                $userId++,
                faker: $faker,
                email: $faker->safeEmail(),
                phone: $basePhone,
                fullName: $faker->name(),
            );
            $insertBatch();
        }
        
        // 3. NAME VARIATIONS (20%)
        // Similar names with typos, nicknames, variations
        $this->command->info("👤 Generating name variations...");
        $baseNames = [
            ['John Smith', 'Jon Smith', 'Johan Smith', 'John Smyth', 'Johnny Smith'],
            ['Mary Johnson', 'Marie Johnson', 'Mary Jonson', 'Mariah Johnson', 'Merry Johnson'],
            ['Robert Brown', 'Rob Brown', 'Robert Browne', 'Bob Brown', 'Robbert Brown'],
            ['Jennifer Davis', 'Jen Davis', 'Jennifer Davies', 'Jenny Davis', 'Jenifer Davis'],
            ['Michael Wilson', 'Mike Wilson', 'Micheal Wilson', 'Michael Willson', 'Micheal Willson'],
            ['Sarah Miller', 'Sara Miller', 'Sarah Muller', 'Saira Miller', 'Sarah Miler'],
            ['David Moore', 'Dave Moore', 'David More', 'David Moor', 'Davyd Moore'],
            ['Jessica Taylor', 'Jess Taylor', 'Jessica Tayler', 'Jessie Taylor', 'Jessica Tailor'],
            ['James Anderson', 'Jim Anderson', 'James Andersen', 'Jamie Anderson', 'James Anderso'],
            ['Amanda Thomas', 'Amy Thomas', 'Amanda Tomas', 'Annette Thomas', 'Amanda Thoms'],
        ];
        
        for ($i = 0; $i < $nameVariationRecords; $i++) {
            $nameVariations = $faker->randomElement($baseNames);
            $name = $faker->randomElement($nameVariations);
            $records[] = $this->generateUserRecord(
                $userId++,
                faker: $faker,
                email: $faker->safeEmail(),
                phone: $this->generatePhoneNumber(),
                fullName: $name,
            );
            $insertBatch();
        }
        
        // 4. COMPLETELY UNIQUE RECORDS (20%)
        $this->command->info("🆔 Generating unique records...");
        for ($i = 0; $i < $uniqueRecords; $i++) {
            $records[] = $this->generateUserRecord(
                $userId++,
                faker: $faker,
            );
            $insertBatch();
        }
        
        // 5. EDGE CASES (5%)
        $this->command->info("⚠️  Generating edge cases...");
        for ($i = 0; $i < $edgeCaseRecords; $i++) {
            $caseType = $faker->randomElement([
                'null_email',
                'null_phone',
                'both_null',
                'case_sensitive_email',
                'special_chars',
                'very_long_name',
                'empty_email',
                'whitespace_phone',
            ]);
            
            $records[] = $this->generateEdgeCaseRecord($userId++, $faker, $caseType);
            $insertBatch();
        }
        
        // Insert remaining records
        if (!empty($records)) {
            DB::table('ws_user')->insert($records);
        }
        
        // Final stats
        $totalTime = round(microtime(true) - $startTime, 2);
        $finalCount = DB::table('ws_user')->count();
        $recordsPerSecond = round($finalCount / $totalTime, 0);
        
        $this->command->info('');
        $this->command->info("✅ Seeding complete!");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("Total records inserted: " . number_format($finalCount));
        $this->command->info("Total time: {$totalTime}s");
        $this->command->info("Records/second: $recordsPerSecond");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }
    
    /**
     * Generate a standard user record
     */
    private function generateUserRecord(
        int $userId,
        Faker $faker,
        ?string $email = null,
        ?string $phone = null,
        ?string $fullName = null,
    ): array {
        $name = $fullName ?? $faker->name();
        $status = $faker->randomElement([0, 1, 1, 1, 2, -1]); // 60% active (1), 20% inactive (0), 10% suspended (2), 10% deleted (-1)
        $daysAgo = rand(1, 365);
        
        return [
            'user_id' => $userId,
            'user_name' => strtolower(str_replace(' ', '_', $name) . '_' . rand(100, 999)),
            'full_name' => $name,
            'user_email' => $email ?? ($faker->randomElement([null, $faker->safeEmail(), $faker->safeEmail()]) ?? null),
            'msisdn' => $phone ?? ($faker->randomElement([null, $faker->mobileNumber(), $faker->mobileNumber()]) ?? null),
            'status' => $status,
            'create_time' => Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23)),
            'update_time' => Carbon::now()->subDays(rand(0, $daysAgo)),
            'last_login' => rand(0, 1) ? Carbon::now()->subDays(rand(0, 30)) : null,
            'birth_date' => rand(0, 1) ? $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d') : null,
            'hobbies' => $faker->randomElement([
                null,
                'reading, coding',
                'sports, gaming',
                'music, art',
                'travel, cooking',
                'photography, hiking',
                'movies, books',
                'gaming, streaming 🎮',
                'art, design 🎨',
            ]),
        ];
    }
    
    /**
     * Generate edge case records for quality testing
     */
    private function generateEdgeCaseRecord(int $userId, Faker $faker, string $caseType): array
    {
        $baseRecord = [
            'user_id' => $userId,
            'user_name' => 'edge_case_' . $userId,
            'full_name' => match($caseType) {
                'very_long_name' => 'A' . str_repeat('very_long_name_', 10),
                default => $faker->name(),
            },
            'status' => $faker->randomElement([0, 1, 2, -1]),
            'create_time' => Carbon::now()->subDays(rand(1, 365)),
            'update_time' => Carbon::now()->subDays(rand(0, 30)),
        ];
        
        // Apply case-specific logic
        return match($caseType) {
            'null_email' => array_merge($baseRecord, [
                'user_email' => null,
                'msisdn' => $faker->mobileNumber(),
            ]),
            'null_phone' => array_merge($baseRecord, [
                'user_email' => $faker->safeEmail(),
                'msisdn' => null,
            ]),
            'both_null' => array_merge($baseRecord, [
                'user_email' => null,
                'msisdn' => null,
            ]),
            'case_sensitive_email' => array_merge($baseRecord, [
                'user_email' => 'CaseSensitive' . rand(1, 100) . '@EXAMPLE.COM',
                'msisdn' => $faker->mobileNumber(),
            ]),
            'special_chars' => array_merge($baseRecord, [
                'user_email' => 'user+' . rand(1, 1000) . '@example.com',
                'msisdn' => '+62' . rand(812, 899) . rand(1000000, 9999999),
            ]),
            'very_long_name' => array_merge($baseRecord, [
                'user_email' => $faker->safeEmail(),
                'msisdn' => $faker->mobileNumber(),
            ]),
            'empty_email' => array_merge($baseRecord, [
                'user_email' => '',
                'msisdn' => $faker->mobileNumber(),
            ]),
            'whitespace_phone' => array_merge($baseRecord, [
                'user_email' => $faker->safeEmail(),
                'msisdn' => '   ' . $faker->mobileNumber() . '   ',
            ]),
            default => array_merge($baseRecord, [
                'user_email' => $faker->safeEmail(),
                'msisdn' => $faker->mobileNumber(),
            ]),
        };
    }
}
