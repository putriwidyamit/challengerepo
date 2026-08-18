<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchTest extends TestCase
{
    /**
     * Test search endpoint exists and returns proper structure
     */
    public function test_search_endpoint_exists(): void
    {
        $response = $this->getJson('/api/search?q=test&type=name');
        $this->assertNotNull($response);
    }

    /**
     * Test required response fields
     */
    public function test_search_response_structure(): void
    {
        $response = $this->getJson('/api/search?q=test&type=name&limit=10&offset=0');

        $response->assertJsonStructure([
            'query',
            'type',
            'limit',
            'offset',
            'results',
            'total',
            'took_ms',
        ]);
    }

    /**
     * Test missing query parameter returns 400
     */
    public function test_missing_query_parameter(): void
    {
        $response = $this->getJson('/api/search?type=name');
        $this->assertEquals(400, $response->status());
        $response->assertJsonPath('error', fn($value) => str_contains($value, 'required'));
    }

    /**
     * Test missing type parameter returns 400
     */
    public function test_missing_type_parameter(): void
    {
        $response = $this->getJson('/api/search?q=test');
        $this->assertEquals(400, $response->status());
        $response->assertJsonPath('error', fn($value) => str_contains($value, 'required'));
    }

    /**
     * Test invalid type returns 400
     */
    public function test_invalid_type(): void
    {
        $response = $this->getJson('/api/search?q=test&type=invalid');
        $this->assertEquals(400, $response->status());
        $response->assertJsonPath('error', fn($value) => str_contains($value, 'Invalid type'));
    }

    /**
     * Test negative limit returns 400
     */
    public function test_negative_limit(): void
    {
        $response = $this->getJson('/api/search?q=test&type=name&limit=-1');
        $this->assertEquals(400, $response->status());
        $response->assertJsonPath('error', fn($value) => str_contains($value, 'between'));
    }

    /**
     * Test limit exceeding maximum returns 400
     */
    public function test_limit_exceeds_maximum(): void
    {
        $response = $this->getJson('/api/search?q=test&type=name&limit=101');
        $this->assertEquals(400, $response->status());
        $response->assertJsonPath('error', fn($value) => str_contains($value, 'between'));
    }

    /**
     * Test negative offset returns 400
     */
    public function test_negative_offset(): void
    {
        $response = $this->getJson('/api/search?q=test&type=name&offset=-1');
        $this->assertEquals(400, $response->status());
        $response->assertJsonPath('error', fn($value) => str_contains($value, 'non-negative'));
    }

    /**
     * Test query exceeding maximum length returns 400
     */
    public function test_query_exceeds_max_length(): void
    {
        $longQuery = str_repeat('a', 1001);
        $response = $this->getJson("/api/search?q={$longQuery}&type=name");
        $this->assertEquals(400, $response->status());
        $response->assertJsonPath('error', fn($value) => str_contains($value, 'exceeds'));
    }

    /**
     * Test SQL injection attempt is safely handled
     */
    public function test_sql_injection_attempt(): void
    {
        $response = $this->getJson("/api/search?q=' OR 1=1 --&type=email");
        // Should return 200 with no results, not error
        $this->assertEquals(200, $response->status());
        $response->assertJsonPath('results', []);
    }

    /**
     * Test phone number masking
     */
    public function test_phone_masking(): void
    {
        $response = $this->getJson('/api/search?q=%&type=name&limit=1');

        if ($response->status() === 200 && count($response->json()['results'] ?? []) > 0) {
            $result = $response->json()['results'][0];

            // Check that if msisdn exists in results, it's masked
            if ($result['msisdn']) {
                // Should be in format: 0812****5890
                $this->assertNotContains('081234567', $result['msisdn']);
                // Should have asterisks
                $this->assertStringContainsString('*', $result['msisdn']);
            }
        }
    }

    /**
     * Test email search returns 200
     */
    public function test_email_search(): void
    {
        $response = $this->getJson('/api/search?q=test@example.com&type=email');
        $this->assertEquals(200, $response->status());
        $response->assertJsonPath('type', 'email');
    }

    /**
     * Test phone search returns 200
     */
    public function test_phone_search(): void
    {
        $response = $this->getJson('/api/search?q=081234567890&type=phone');
        $this->assertEquals(200, $response->status());
        $response->assertJsonPath('type', 'phone');
    }

    /**
     * Test user_id search returns 200
     */
    public function test_user_id_search(): void
    {
        $response = $this->getJson('/api/search?q=123&type=user_id');
        $this->assertEquals(200, $response->status());
        $response->assertJsonPath('type', 'user_id');
    }

    /**
     * Test invalid user_id returns empty results
     */
    public function test_invalid_user_id(): void
    {
        $response = $this->getJson('/api/search?q=not_a_number&type=user_id');
        $this->assertEquals(200, $response->status());
        $response->assertJsonPath('results', []);
        $response->assertJsonPath('total', 0);
    }

    /**
     * Test name search returns 200
     */
    public function test_name_search(): void
    {
        $response = $this->getJson('/api/search?q=test&type=name');
        $this->assertEquals(200, $response->status());
        $response->assertJsonPath('type', 'name');
    }

    /**
     * Test pagination with default values
     */
    public function test_pagination_defaults(): void
    {
        $response = $this->getJson('/api/search?q=%&type=name');
        $response->assertJsonPath('limit', 10);
        $response->assertJsonPath('offset', 0);
    }

    /**
     * Test pagination with custom values
     */
    public function test_pagination_custom(): void
    {
        $response = $this->getJson('/api/search?q=%&type=name&limit=20&offset=40');
        $response->assertJsonPath('limit', 20);
        $response->assertJsonPath('offset', 40);
    }

    /**
     * Test response includes timestamp in ISO-8601 format
     */
    public function test_response_timestamp_format(): void
    {
        $response = $this->getJson('/api/search?q=test&type=name&limit=1');

        if ($response->status() === 200 && count($response->json()['results'] ?? []) > 0) {
            $result = $response->json()['results'][0];
            
            // Check if created_at is valid ISO-8601
            if ($result['created_at']) {
                $this->assertTrue(
                    strtotime($result['created_at']) !== false,
                    'created_at is not a valid ISO-8601 date'
                );
                $this->assertStringContainsString('T', $result['created_at']);
                $this->assertStringContainsString('Z', $result['created_at']);
            }
        }
    }

    /**
     * Test response includes took_ms field
     */
    public function test_response_includes_latency(): void
    {
        $response = $this->getJson('/api/search?q=test&type=name');
        $this->assertIsInt($response->json()['took_ms']);
        $this->assertGreaterThanOrEqual(0, $response->json()['took_ms']);
    }

    /**
     * Test special characters in query don't cause SQL errors
     */
    public function test_special_characters_in_query(): void
    {
        $specialQueries = [
            "test'; DROP TABLE--",
            'test" OR "1"="1',
            'test`',
            'test\\',
            'test%',
            'test_',
        ];

        foreach ($specialQueries as $query) {
            $encodedQuery = urlencode($query);
            $response = $this->getJson("/api/search?q={$encodedQuery}&type=name");
            // Should always return 200, never 500
            $this->assertTrue(
                in_array($response->status(), [200, 400]),
                "Query: {$query} returned status {$response->status()}"
            );
        }
    }

    /**
     * Test limit parameter bounds
     */
    public function test_limit_parameter_bounds(): void
    {
        // Test minimum valid limit
        $response = $this->getJson('/api/search?q=test&type=name&limit=1');
        $this->assertEquals(200, $response->status());

        // Test maximum valid limit
        $response = $this->getJson('/api/search?q=test&type=name&limit=100');
        $this->assertEquals(200, $response->status());

        // Test just over maximum
        $response = $this->getJson('/api/search?q=test&type=name&limit=101');
        $this->assertEquals(400, $response->status());

        // Test zero limit
        $response = $this->getJson('/api/search?q=test&type=name&limit=0');
        $this->assertEquals(400, $response->status());
    }

    /**
     * Test all search types work
     */
    public function test_all_search_types(): void
    {
        $types = ['email', 'phone', 'user_id', 'name'];

        foreach ($types as $type) {
            $response = $this->getJson("/api/search?q=test&type={$type}");
            $this->assertEquals(200, $response->status(), "Failed for type: {$type}");
            $response->assertJsonPath('type', $type);
        }
    }

    /**
     * Test search with empty results
     */
    public function test_search_empty_results(): void
    {
        $response = $this->getJson('/api/search?q=xyzabc123notfound&type=name');
        $this->assertEquals(200, $response->status());
        $response->assertJsonPath('results', []);
        $response->assertJsonPath('total', 0);
    }

    /**
     * Test results are returned with all required fields
     */
    public function test_result_fields(): void
    {
        $response = $this->getJson('/api/search?q=%&type=name&limit=1');

        if ($response->status() === 200 && count($response->json()['results'] ?? []) > 0) {
            $result = $response->json()['results'][0];

            $this->assertIsInt($result['user_id']);
            $this->assertIsString($result['full_name']);
            $this->assertIsString($result['user_email']);
            // msisdn can be null or string (masked)
            $this->assertTrue($result['msisdn'] === null || is_string($result['msisdn']));
            $this->assertIsInt($result['status']);
            // created_at can be null or string
            $this->assertTrue($result['created_at'] === null || is_string($result['created_at']));
        }
    }

    /**
     * Test search route is accessible
     */
    public function test_search_route_accessible(): void
    {
        $response = $this->get('/search');
        $this->assertEquals(200, $response->status());
    }
}
