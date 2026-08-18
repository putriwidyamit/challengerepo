<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

class QualityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create test data for quality analysis
        $this->seedTestData();
    }

    /**
     * Seed test data with quality issues for testing
     */
    private function seedTestData(): void
    {
        // Regular good records
        for ($i = 1; $i <= 50; $i++) {
            DB::table('ws_user')->insert([
                'user_id' => $i,
                'user_name' => "user_$i",
                'full_name' => "Test User $i",
                'user_email' => "user$i@example.com",
                'msisdn' => "0812000000" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => Carbon::now()->subYears(25)->addDays($i)->format('Y-m-d'),
                'hobbies' => 'reading, coding, gaming',
                'status' => 1,
                'create_time' => Carbon::now(),
                'update_time' => Carbon::now(),
            ]);
        }

        // Records with missing email
        for ($i = 51; $i <= 55; $i++) {
            DB::table('ws_user')->insert([
                'user_id' => $i,
                'user_name' => "user_$i",
                'full_name' => "Test User $i",
                'user_email' => null,
                'msisdn' => "0812000000" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => Carbon::now()->subYears(30)->format('Y-m-d'),
                'hobbies' => 'sports',
                'status' => 1,
                'create_time' => Carbon::now(),
                'update_time' => Carbon::now(),
            ]);
        }

        // Records with missing phone
        for ($i = 56; $i <= 60; $i++) {
            DB::table('ws_user')->insert([
                'user_id' => $i,
                'user_name' => "user_$i",
                'full_name' => "Test User $i",
                'user_email' => "user$i@example.com",
                'msisdn' => null,
                'birth_date' => Carbon::now()->subYears(20)->format('Y-m-d'),
                'hobbies' => 'music',
                'status' => 0,
                'create_time' => Carbon::now(),
                'update_time' => Carbon::now(),
            ]);
        }

        // Records with missing birth date
        for ($i = 61; $i <= 65; $i++) {
            DB::table('ws_user')->insert([
                'user_id' => $i,
                'user_name' => "user_$i",
                'full_name' => "Test User $i",
                'user_email' => "user$i@example.com",
                'msisdn' => "0812000000" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => null,
                'hobbies' => 'art',
                'status' => 1,
                'create_time' => Carbon::now(),
                'update_time' => Carbon::now(),
            ]);
        }

        // Records with missing hobbies
        for ($i = 66; $i <= 70; $i++) {
            DB::table('ws_user')->insert([
                'user_id' => $i,
                'user_name' => "user_$i",
                'full_name' => "Test User $i",
                'user_email' => "user$i@example.com",
                'msisdn' => "0812000000" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => Carbon::now()->subYears(22)->format('Y-m-d'),
                'hobbies' => null,
                'status' => 1,
                'create_time' => Carbon::now(),
                'update_time' => Carbon::now(),
            ]);
        }

        // Duplicate emails (case insensitive)
        DB::table('ws_user')->insert([
            'user_id' => 71,
            'user_name' => 'user_71',
            'full_name' => 'Test User 71',
            'user_email' => 'duplicate@example.com',
            'msisdn' => '08120000070',
            'birth_date' => Carbon::now()->subYears(25)->format('Y-m-d'),
            'hobbies' => 'reading',
            'status' => 1,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now(),
        ]);

        DB::table('ws_user')->insert([
            'user_id' => 72,
            'user_name' => 'user_72',
            'full_name' => 'Test User 72',
            'user_email' => 'DUPLICATE@EXAMPLE.COM',
            'msisdn' => '08120000071',
            'birth_date' => Carbon::now()->subYears(26)->format('Y-m-d'),
            'hobbies' => 'coding',
            'status' => 1,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now(),
        ]);

        // Duplicate phones
        DB::table('ws_user')->insert([
            'user_id' => 73,
            'user_name' => 'user_73',
            'full_name' => 'Test User 73',
            'user_email' => 'user73@example.com',
            'msisdn' => '08129999999',
            'birth_date' => Carbon::now()->subYears(28)->format('Y-m-d'),
            'hobbies' => 'travel',
            'status' => 1,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now(),
        ]);

        DB::table('ws_user')->insert([
            'user_id' => 74,
            'user_name' => 'user_74',
            'full_name' => 'Test User 74',
            'user_email' => 'user74@example.com',
            'msisdn' => '08129999999',
            'birth_date' => Carbon::now()->subYears(29)->format('Y-m-d'),
            'hobbies' => 'sports',
            'status' => 1,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now(),
        ]);

        // Invalid email format
        for ($i = 75; $i <= 77; $i++) {
            DB::table('ws_user')->insert([
                'user_id' => $i,
                'user_name' => "user_$i",
                'full_name' => "Test User $i",
                'user_email' => 'invalid-email-' . $i,
                'msisdn' => "0812000000" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => Carbon::now()->subYears(24)->format('Y-m-d'),
                'hobbies' => 'gaming',
                'status' => 1,
                'create_time' => Carbon::now(),
                'update_time' => Carbon::now(),
            ]);
        }

        // Malformed phone
        for ($i = 78; $i <= 80; $i++) {
            DB::table('ws_user')->insert([
                'user_id' => $i,
                'user_name' => "user_$i",
                'full_name' => "Test User $i",
                'user_email' => "user$i@example.com",
                'msisdn' => '123abc456def',
                'birth_date' => Carbon::now()->subYears(23)->format('Y-m-d'),
                'hobbies' => 'music',
                'status' => 0,
                'create_time' => Carbon::now(),
                'update_time' => Carbon::now(),
            ]);
        }

        // Impossible birth dates (before 1900)
        for ($i = 81; $i <= 83; $i++) {
            DB::table('ws_user')->insert([
                'user_id' => $i,
                'user_name' => "user_$i",
                'full_name' => "Test User $i",
                'user_email' => "user$i@example.com",
                'msisdn' => "0812000000" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => '1850-01-01',
                'hobbies' => 'art',
                'status' => 1,
                'create_time' => Carbon::now(),
                'update_time' => Carbon::now(),
            ]);
        }

        // Future birth dates
        for ($i = 84; $i <= 86; $i++) {
            DB::table('ws_user')->insert([
                'user_id' => $i,
                'user_name' => "user_$i",
                'full_name' => "Test User $i",
                'user_email' => "user$i@example.com",
                'msisdn' => "0812000000" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'birth_date' => Carbon::now()->addYears(5)->format('Y-m-d'),
                'hobbies' => 'dance',
                'status' => 1,
                'create_time' => Carbon::now(),
                'update_time' => Carbon::now(),
            ]);
        }

        // Hobbies with special characters
        DB::table('ws_user')->insert([
            'user_id' => 87,
            'user_name' => 'user_87',
            'full_name' => 'Test User 87',
            'user_email' => 'user87@example.com',
            'msisdn' => '08120000087',
            'birth_date' => Carbon::now()->subYears(21)->format('Y-m-d'),
            'hobbies' => 'reading, coding!@#, gaming',
            'status' => 1,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now(),
        ]);

        // Hobbies with emoji
        DB::table('ws_user')->insert([
            'user_id' => 88,
            'user_name' => 'user_88',
            'full_name' => 'Test User 88',
            'user_email' => 'user88@example.com',
            'msisdn' => '08120000088',
            'birth_date' => Carbon::now()->subYears(27)->format('Y-m-d'),
            'hobbies' => 'coding 🎮, gaming 🕹️',
            'status' => 1,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now(),
        ]);

        // Various status values
        DB::table('ws_user')->insert([
            'user_id' => 89,
            'user_name' => 'user_89',
            'full_name' => 'Test User 89',
            'user_email' => 'user89@example.com',
            'msisdn' => '08120000089',
            'birth_date' => Carbon::now()->subYears(19)->format('Y-m-d'),
            'hobbies' => 'sports',
            'status' => -1,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now(),
        ]);

        DB::table('ws_user')->insert([
            'user_id' => 90,
            'user_name' => 'user_90',
            'full_name' => 'Test User 90',
            'user_email' => 'user90@example.com',
            'msisdn' => '08120000090',
            'birth_date' => Carbon::now()->subYears(31)->format('Y-m-d'),
            'hobbies' => 'travel',
            'status' => 2,
            'create_time' => Carbon::now(),
            'update_time' => Carbon::now(),
        ]);
    }

    /**
     * Test quality endpoint exists and returns 200
     */
    public function test_quality_endpoint_exists(): void
    {
        $response = $this->getJson('/api/quality');
        $this->assertEquals(200, $response->status());
    }

    /**
     * Test quality response has required structure
     */
    public function test_quality_response_structure(): void
    {
        $response = $this->getJson('/api/quality');

        $response->assertJsonStructure([
            'total_records',
            'analyzed_at',
            'quality_metrics' => [
                'email' => [
                    'total',
                    'present',
                    'missing_count',
                    'missing_percent',
                    'unique',
                    'duplicate_count',
                    'invalid_format',
                ],
                'phone' => [
                    'total',
                    'present',
                    'missing_count',
                    'missing_percent',
                    'unique',
                    'duplicate_count',
                    'malformed',
                ],
                'birth_date' => [
                    'total',
                    'present',
                    'missing_count',
                    'missing_percent',
                    'invalid_dates',
                    'impossible_dates',
                    'future_dates',
                ],
                'hobbies' => [
                    'total',
                    'null_count',
                    'null_percent',
                    'with_special_chars',
                    'with_emoji',
                ],
                'status' => [
                    'total',
                    'distribution',
                ],
            ],
            'data_issues',
            'took_ms',
        ]);
    }

    /**
     * Test total records count
     */
    public function test_total_records_count(): void
    {
        $response = $this->getJson('/api/quality');

        $response->assertJsonPath('total_records', 90);
    }

    /**
     * Test email metrics
     */
    public function test_email_metrics(): void
    {
        $response = $this->getJson('/api/quality');

        $response->assertJsonPath('quality_metrics.email.total', 90);
        $response->assertJsonPath('quality_metrics.email.present', 85); // 5 missing
        $response->assertJsonPath('quality_metrics.email.missing_count', 5);
    }

    /**
     * Test phone metrics
     */
    public function test_phone_metrics(): void
    {
        $response = $this->getJson('/api/quality');

        $response->assertJsonPath('quality_metrics.phone.total', 90);
        $response->assertJsonPath('quality_metrics.phone.present', 85); // 5 missing
        $response->assertJsonPath('quality_metrics.phone.missing_count', 5);
    }

    /**
     * Test birth_date metrics
     */
    public function test_birth_date_metrics(): void
    {
        $response = $this->getJson('/api/quality');

        $response->assertJsonPath('quality_metrics.birth_date.total', 90);
        $response->assertJsonPath('quality_metrics.birth_date.present', 85); // 5 missing
        $response->assertJsonPath('quality_metrics.birth_date.missing_count', 5);
    }

    /**
     * Test hobbies metrics
     */
    public function test_hobbies_metrics(): void
    {
        $response = $this->getJson('/api/quality');

        $response->assertJsonPath('quality_metrics.hobbies.total', 90);
        $response->assertJsonPath('quality_metrics.hobbies.null_count', 5);
    }

    /**
     * Test status distribution
     */
    public function test_status_distribution(): void
    {
        $response = $this->getJson('/api/quality');

        $distribution = $response->json('quality_metrics.status.distribution');
        $this->assertIsArray($distribution);
        $this->assertTrue(count($distribution) > 0);
    }

    /**
     * Test email duplicate detection
     */
    public function test_email_duplicate_detection(): void
    {
        $response = $this->getJson('/api/quality');

        $duplicateCount = $response->json('quality_metrics.email.duplicate_count');
        $this->assertGreaterThanOrEqual(2, $duplicateCount); // At least the 2 duplicate emails we created
    }

    /**
     * Test phone duplicate detection
     */
    public function test_phone_duplicate_detection(): void
    {
        $response = $this->getJson('/api/quality');

        $duplicateCount = $response->json('quality_metrics.phone.duplicate_count');
        $this->assertGreaterThanOrEqual(2, $duplicateCount); // At least the 2 duplicate phones we created
    }

    /**
     * Test invalid email format detection
     */
    public function test_invalid_email_format_detection(): void
    {
        $response = $this->getJson('/api/quality');

        $invalidCount = $response->json('quality_metrics.email.invalid_format');
        $this->assertGreaterThanOrEqual(3, $invalidCount); // We created 3 invalid emails
    }

    /**
     * Test malformed phone detection
     */
    public function test_malformed_phone_detection(): void
    {
        $response = $this->getJson('/api/quality');

        $malformedCount = $response->json('quality_metrics.phone.malformed');
        $this->assertGreaterThanOrEqual(3, $malformedCount); // We created 3 malformed phones
    }

    /**
     * Test impossible birth date detection
     */
    public function test_impossible_birth_date_detection(): void
    {
        $response = $this->getJson('/api/quality');

        $impossibleCount = $response->json('quality_metrics.birth_date.impossible_dates');
        $this->assertGreaterThanOrEqual(3, $impossibleCount); // We created 3 impossible dates
    }

    /**
     * Test future birth date detection
     */
    public function test_future_birth_date_detection(): void
    {
        $response = $this->getJson('/api/quality');

        $futureCount = $response->json('quality_metrics.birth_date.future_dates');
        $this->assertGreaterThanOrEqual(3, $futureCount); // We created 3 future dates
    }

    /**
     * Test data issues are reported
     */
    public function test_data_issues_reported(): void
    {
        $response = $this->getJson('/api/quality');

        $issues = $response->json('data_issues');
        $this->assertIsArray($issues);
        $this->assertGreaterThan(0, count($issues)); // Should have issues detected
    }

    /**
     * Test data issue structure
     */
    public function test_data_issue_structure(): void
    {
        $response = $this->getJson('/api/quality');

        $issues = $response->json('data_issues');
        if (count($issues) > 0) {
            $issue = $issues[0];
            $this->assertArrayHasKey('field', $issue);
            $this->assertArrayHasKey('issue_type', $issue);
            $this->assertArrayHasKey('count', $issue);
            $this->assertArrayHasKey('examples', $issue);
            $this->assertArrayHasKey('severity', $issue);
        }
    }

    /**
     * Test response includes timing
     */
    public function test_response_includes_timing(): void
    {
        $response = $this->getJson('/api/quality');

        $tookMs = $response->json('took_ms');
        $this->assertIsInt($tookMs);
        $this->assertGreaterThanOrEqual(0, $tookMs);
    }

    /**
     * Test analyzed_at timestamp
     */
    public function test_analyzed_at_timestamp(): void
    {
        $response = $this->getJson('/api/quality');

        $analyzedAt = $response->json('analyzed_at');
        $this->assertNotNull($analyzedAt);
        // Should be ISO8601 format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $analyzedAt);
    }

    /**
     * Test unique email count accuracy
     */
    public function test_unique_email_count(): void
    {
        $response = $this->getJson('/api/quality');

        $uniqueCount = $response->json('quality_metrics.email.unique');
        $presentCount = $response->json('quality_metrics.email.present');

        // Unique count should be <= present count
        $this->assertLessThanOrEqual($presentCount, $uniqueCount);
    }

    /**
     * Test unique phone count accuracy
     */
    public function test_unique_phone_count(): void
    {
        $response = $this->getJson('/api/quality');

        $uniqueCount = $response->json('quality_metrics.phone.unique');
        $presentCount = $response->json('quality_metrics.phone.present');

        // Unique count should be <= present count
        $this->assertLessThanOrEqual($presentCount, $uniqueCount);
    }

    /**
     * Test missing percentage calculation
     */
    public function test_missing_percentage_calculation(): void
    {
        $response = $this->getJson('/api/quality');

        $total = $response->json('quality_metrics.email.total');
        $missing = $response->json('quality_metrics.email.missing_count');
        $percentage = $response->json('quality_metrics.email.missing_percent');

        $expectedPercentage = round(($missing * 100.0 / max($total, 1)), 1);
        $this->assertEquals($expectedPercentage, $percentage);
    }
}
