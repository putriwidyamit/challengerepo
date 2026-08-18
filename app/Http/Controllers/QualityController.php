<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QualityController extends Controller
{
    private static $inFlightCache = [];
    private static $cacheTimestamp = 0;
    private const CACHE_TTL_SECONDS = 10;

    /**
     * GET /api/quality
     * Analyze data quality across ws_user table
     */
    public function analyze()
    {
        // Check in-flight cache to reduce concurrent queries
        $now = microtime(true);
        if (self::$cacheTimestamp && ($now - self::$cacheTimestamp) < self::CACHE_TTL_SECONDS) {
            return response()->json(self::$inFlightCache);
        }

        $startTime = microtime(true);

        try {
            // Run comprehensive quality analysis
            $analysis = $this->runQualityAnalysis();
            $tookMs = (int) round((microtime(true) - $startTime) * 1000);

            // Cache result in memory for TTL period
            self::$inFlightCache = $analysis;
            self::$cacheTimestamp = $now;

            return response()->json($analysis);
        } catch (\Throwable $e) {
            logger()->error('Quality analysis error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Data quality analysis unavailable',
            ], 503);
        }
    }

    /**
     * Run comprehensive quality analysis using set-based PostgreSQL queries
     */
    private function runQualityAnalysis(): array
    {
        $startAnalysis = microtime(true);

        // 1. Get comprehensive metrics in single aggregate query
        $mainMetrics = $this->getMainMetrics();
        $totalRecords = (int) $mainMetrics['total'];

        // 2. Get status distribution
        $statusDistribution = $this->getStatusDistribution($totalRecords);

        // 3. Get data issues
        $dataIssues = $this->getDataIssues();

        $tookMs = (int) round((microtime(true) - $startAnalysis) * 1000);

        return [
            'total_records' => $totalRecords,
            'analyzed_at' => Carbon::now()->toIso8601String(),
            'quality_metrics' => [
                'email' => $this->buildEmailMetrics($mainMetrics, $totalRecords),
                'phone' => $this->buildPhoneMetrics($mainMetrics, $totalRecords),
                'birth_date' => $this->buildBirthDateMetrics($mainMetrics, $totalRecords),
                'hobbies' => $this->buildHobbiesMetrics($mainMetrics, $totalRecords),
                'status' => [
                    'total' => $totalRecords,
                    'distribution' => $statusDistribution,
                ],
            ],
            'data_issues' => $dataIssues,
            'took_ms' => $tookMs,
        ];
    }

    /**
     * Main aggregation query for all metrics
     * Executes once for email, phone, birth_date, hobbies metrics
     */
    private function getMainMetrics(): array
    {
        $driver = DB::getDriverName();
        
        // PostgreSQL uses ~ operator for regex
        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                "SELECT
                    COUNT(*) as total,
                    
                    -- EMAIL METRICS
                    COUNT(CASE WHEN user_email IS NOT NULL AND TRIM(user_email) != '' THEN 1 END) as email_present,
                    COUNT(CASE WHEN user_email IS NULL OR TRIM(user_email) = '' THEN 1 END) as email_missing,
                    COUNT(DISTINCT CASE WHEN user_email IS NOT NULL THEN LOWER(TRIM(user_email)) END) as email_unique,
                    COUNT(CASE WHEN user_email ~ '^[A-Za-z0-9._+%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN 1 END) as email_valid,
                    
                    -- PHONE METRICS
                    COUNT(CASE WHEN msisdn IS NOT NULL AND TRIM(msisdn) != '' THEN 1 END) as phone_present,
                    COUNT(CASE WHEN msisdn IS NULL OR TRIM(msisdn) = '' THEN 1 END) as phone_missing,
                    COUNT(DISTINCT CASE WHEN msisdn IS NOT NULL THEN TRIM(msisdn) END) as phone_unique,
                    COUNT(CASE WHEN msisdn ~ '^[0-9+]{7,15}$' AND msisdn !~ '[^0-9+]' THEN 1 END) as phone_valid,
                    
                    -- BIRTH_DATE METRICS
                    COUNT(CASE WHEN birth_date IS NOT NULL THEN 1 END) as birth_date_present,
                    COUNT(CASE WHEN birth_date IS NULL THEN 1 END) as birth_date_missing,
                    COUNT(CASE WHEN birth_date < '1900-01-01' OR birth_date > CURRENT_DATE - INTERVAL '18 years' THEN 1 END) as birth_date_impossible,
                    COUNT(CASE WHEN birth_date > CURRENT_DATE THEN 1 END) as birth_date_future,
                    
                    -- HOBBIES METRICS
                    COUNT(CASE WHEN hobbies IS NULL THEN 1 END) as hobbies_null,
                    COUNT(CASE WHEN hobbies ~ '[^A-Za-z0-9\s,\-&.]' THEN 1 END) as hobbies_special_chars,
                    COUNT(CASE WHEN hobbies ~ '[\U0001F300-\U0001F9FF]|[\U0001F600-\U0001F64F]' THEN 1 END) as hobbies_emoji
                    
                FROM ws_user"
            );
        } else {
            // SQLite uses GLOB and other operators
            $result = DB::selectOne(
                "SELECT
                    COUNT(*) as total,
                    
                    -- EMAIL METRICS
                    COUNT(CASE WHEN user_email IS NOT NULL AND TRIM(user_email) != '' THEN 1 END) as email_present,
                    COUNT(CASE WHEN user_email IS NULL OR TRIM(user_email) = '' THEN 1 END) as email_missing,
                    COUNT(DISTINCT CASE WHEN user_email IS NOT NULL THEN LOWER(TRIM(user_email)) END) as email_unique,
                    COUNT(CASE WHEN user_email IS NOT NULL 
                        AND user_email GLOB '[A-Za-z0-9._+%-]*@[A-Za-z0-9.-]*.[A-Za-z][A-Za-z]' THEN 1 END) as email_valid,
                    
                    -- PHONE METRICS
                    COUNT(CASE WHEN msisdn IS NOT NULL AND TRIM(msisdn) != '' THEN 1 END) as phone_present,
                    COUNT(CASE WHEN msisdn IS NULL OR TRIM(msisdn) = '' THEN 1 END) as phone_missing,
                    COUNT(DISTINCT CASE WHEN msisdn IS NOT NULL THEN TRIM(msisdn) END) as phone_unique,
                    COUNT(CASE WHEN msisdn IS NOT NULL 
                        AND msisdn GLOB '[0-9+]*' AND LENGTH(msisdn) >= 7 AND LENGTH(msisdn) <= 15 THEN 1 END) as phone_valid,
                    
                    -- BIRTH_DATE METRICS
                    COUNT(CASE WHEN birth_date IS NOT NULL THEN 1 END) as birth_date_present,
                    COUNT(CASE WHEN birth_date IS NULL THEN 1 END) as birth_date_missing,
                    COUNT(CASE WHEN birth_date < '1900-01-01' OR birth_date > DATE('now', '-18 years') THEN 1 END) as birth_date_impossible,
                    COUNT(CASE WHEN birth_date > DATE('now') THEN 1 END) as birth_date_future,
                    
                    -- HOBBIES METRICS
                    COUNT(CASE WHEN hobbies IS NULL THEN 1 END) as hobbies_null,
                    COUNT(CASE WHEN hobbies IS NOT NULL AND (
                        hobbies LIKE '%!%' OR hobbies LIKE '%@%' OR hobbies LIKE '%#%'
                    ) THEN 1 END) as hobbies_special_chars,
                    0 as hobbies_emoji
                    
                FROM ws_user"
            );
        }

        return (array) $result;
    }

    /**
     * Calculate email duplicate count
     * Records whose normalized email appears more than once
     */
    private function getEmailDuplicateCount(): int
    {
        $dupRecords = DB::selectOne(
            "SELECT COUNT(*) as count FROM ws_user
            WHERE user_email IS NOT NULL AND TRIM(user_email) != ''
            AND LOWER(TRIM(user_email)) IN (
                SELECT LOWER(TRIM(user_email))
                FROM ws_user
                WHERE user_email IS NOT NULL AND TRIM(user_email) != ''
                GROUP BY LOWER(TRIM(user_email))
                HAVING COUNT(*) > 1
            )"
        );

        return (int) ($dupRecords->count ?? 0);
    }

    /**
     * Calculate phone duplicate count
     */
    private function getPhoneDuplicateCount(): int
    {
        $result = DB::selectOne(
            "SELECT COUNT(*) as count FROM ws_user
            WHERE msisdn IS NOT NULL AND TRIM(msisdn) != ''
            AND TRIM(msisdn) IN (
                SELECT TRIM(msisdn)
                FROM ws_user
                WHERE msisdn IS NOT NULL AND TRIM(msisdn) != ''
                GROUP BY TRIM(msisdn)
                HAVING COUNT(*) > 1
            )"
        );

        return (int) ($result->count ?? 0);
    }

    /**
     * Get status distribution
     */
    private function getStatusDistribution(int $totalRecords): array
    {
        $distribution = DB::select(
            "SELECT status, COUNT(*) as count
            FROM ws_user
            GROUP BY status
            ORDER BY status"
        );

        $result = [];
        $sumCount = 0;
        foreach ($distribution as $row) {
            $status = $row->status === null ? 'null' : (string) $row->status;
            $result[$status] = (int) $row->count;
            $sumCount += $row->count;
        }

        return $result;
    }

    /**
     * Get data issues
     */
    private function getDataIssues(): array
    {
        $issues = [];
        $driver = DB::getDriverName();

        // Email invalid format
        if ($driver === 'pgsql') {
            $emailInvalid = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE user_email IS NOT NULL AND TRIM(user_email) != ''
                AND user_email !~ '^[A-Za-z0-9._+%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'"
            );
        } else {
            $emailInvalid = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE user_email IS NOT NULL AND TRIM(user_email) != ''
                AND user_email NOT GLOB '[A-Za-z0-9._+%-]*@[A-Za-z0-9.-]*.[A-Za-z][A-Za-z]'"
            );
        }
        
        if ($emailInvalid->count > 0) {
            $examples = DB::select(
                "SELECT DISTINCT user_email FROM ws_user
                WHERE user_email IS NOT NULL AND TRIM(user_email) != ''
                LIMIT 5"
            );
            $issues[] = [
                'field' => 'email',
                'issue_type' => 'invalid_format',
                'count' => (int) $emailInvalid->count,
                'examples' => array_column($examples, 'user_email'),
                'severity' => 'medium',
            ];
        }

        // Email missing
        $emailMissing = DB::selectOne(
            "SELECT COUNT(*) as count FROM ws_user
            WHERE user_email IS NULL OR TRIM(user_email) = ''"
        );
        if ($emailMissing->count > 0) {
            $issues[] = [
                'field' => 'email',
                'issue_type' => 'missing',
                'count' => (int) $emailMissing->count,
                'examples' => [],
                'severity' => 'low',
            ];
        }

        // Phone malformed
        if ($driver === 'pgsql') {
            $phoneMalformed = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE msisdn IS NOT NULL AND TRIM(msisdn) != ''
                AND (msisdn !~ '^[0-9+]{7,15}$' OR msisdn ~ '[^0-9+]')"
            );
        } else {
            $phoneMalformed = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE msisdn IS NOT NULL AND TRIM(msisdn) != ''
                AND (msisdn NOT GLOB '[0-9+]*' OR LENGTH(msisdn) < 7 OR LENGTH(msisdn) > 15)"
            );
        }
        
        if ($phoneMalformed->count > 0) {
            $examples = DB::select(
                "SELECT DISTINCT msisdn FROM ws_user
                WHERE msisdn IS NOT NULL AND TRIM(msisdn) != ''
                LIMIT 5"
            );
            $issues[] = [
                'field' => 'phone',
                'issue_type' => 'malformed',
                'count' => (int) $phoneMalformed->count,
                'examples' => array_values(array_map(fn($e) => $this->maskPhoneExample($e->msisdn), $examples)),
                'severity' => 'high',
            ];
        }

        // Phone missing
        $phoneMissing = DB::selectOne(
            "SELECT COUNT(*) as count FROM ws_user
            WHERE msisdn IS NULL OR TRIM(msisdn) = ''"
        );
        if ($phoneMissing->count > 0) {
            $issues[] = [
                'field' => 'phone',
                'issue_type' => 'missing',
                'count' => (int) $phoneMissing->count,
                'examples' => [],
                'severity' => 'low',
            ];
        }

        // Birth date impossible
        if ($driver === 'pgsql') {
            $birthDateImpossible = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE birth_date < '1900-01-01' OR birth_date > CURRENT_DATE - INTERVAL '18 years'"
            );
        } else {
            $birthDateImpossible = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE birth_date < '1900-01-01' OR birth_date > DATE('now', '-18 years')"
            );
        }
        
        if ($birthDateImpossible->count > 0) {
            $issues[] = [
                'field' => 'birth_date',
                'issue_type' => 'impossible_date',
                'count' => (int) $birthDateImpossible->count,
                'examples' => [],
                'severity' => 'high',
            ];
        }

        // Birth date future
        if ($driver === 'pgsql') {
            $birthDateFuture = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE birth_date > CURRENT_DATE"
            );
        } else {
            $birthDateFuture = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE birth_date > DATE('now')"
            );
        }
        
        if ($birthDateFuture->count > 0) {
            $issues[] = [
                'field' => 'birth_date',
                'issue_type' => 'future_date',
                'count' => (int) $birthDateFuture->count,
                'examples' => [],
                'severity' => 'high',
            ];
        }

        // Hobbies with special characters
        if ($driver === 'pgsql') {
            $hobbiesSpecial = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE hobbies ~ '[^A-Za-z0-9\s,\-&.]'"
            );
        } else {
            $hobbiesSpecial = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE hobbies IS NOT NULL AND (
                    hobbies LIKE '%!%' OR hobbies LIKE '%@%' OR hobbies LIKE '%#%'
                )"
            );
        }
        
        if ($hobbiesSpecial->count > 0) {
            $examples = DB::select(
                "SELECT DISTINCT hobbies FROM ws_user
                WHERE hobbies IS NOT NULL
                LIMIT 5"
            );
            $issues[] = [
                'field' => 'hobbies',
                'issue_type' => 'special_characters',
                'count' => (int) $hobbiesSpecial->count,
                'examples' => array_column($examples, 'hobbies'),
                'severity' => 'low',
            ];
        }

        // Hobbies with emoji (PostgreSQL specific, 0 for SQLite)
        if ($driver === 'pgsql') {
            $hobbiesEmoji = DB::selectOne(
                "SELECT COUNT(*) as count FROM ws_user
                WHERE hobbies ~ '[\U0001F300-\U0001F9FF]|[\U0001F600-\U0001F64F]'"
            );
            
            if ($hobbiesEmoji->count > 0) {
                $issues[] = [
                    'field' => 'hobbies',
                    'issue_type' => 'emoji',
                    'count' => (int) $hobbiesEmoji->count,
                    'examples' => [],
                    'severity' => 'low',
                ];
            }
        }

        return $issues;
    }

    /**
     * Build email quality metrics
     */
    private function buildEmailMetrics(array $metrics, int $totalRecords): array
    {
        $present = (int) ($metrics['email_present'] ?? 0);
        $missing = $totalRecords - $present;
        $duplicateCount = $this->getEmailDuplicateCount();

        return [
            'total' => $totalRecords,
            'present' => $present,
            'missing_count' => $missing,
            'missing_percent' => round(($missing * 100.0 / max($totalRecords, 1)), 1),
            'unique' => (int) ($metrics['email_unique'] ?? 0),
            'duplicate_count' => $duplicateCount,
            'invalid_format' => $totalRecords - ((int) ($metrics['email_valid'] ?? 0)),
        ];
    }

    /**
     * Build phone quality metrics
     */
    private function buildPhoneMetrics(array $metrics, int $totalRecords): array
    {
        $present = (int) ($metrics['phone_present'] ?? 0);
        $missing = $totalRecords - $present;
        $duplicateCount = $this->getPhoneDuplicateCount();

        return [
            'total' => $totalRecords,
            'present' => $present,
            'missing_count' => $missing,
            'missing_percent' => round(($missing * 100.0 / max($totalRecords, 1)), 1),
            'unique' => (int) ($metrics['phone_unique'] ?? 0),
            'duplicate_count' => $duplicateCount,
            'malformed' => $totalRecords - ((int) ($metrics['phone_valid'] ?? 0)),
        ];
    }

    /**
     * Build birth_date quality metrics
     */
    private function buildBirthDateMetrics(array $metrics, int $totalRecords): array
    {
        $present = (int) ($metrics['birth_date_present'] ?? 0);
        $missing = (int) ($metrics['birth_date_missing'] ?? 0);

        return [
            'total' => $totalRecords,
            'present' => $present,
            'missing_count' => $missing,
            'missing_percent' => round(($missing * 100.0 / max($totalRecords, 1)), 1),
            'invalid_dates' => (int) ($metrics['birth_date_impossible'] ?? 0),
            'impossible_dates' => (int) ($metrics['birth_date_impossible'] ?? 0),
            'future_dates' => (int) ($metrics['birth_date_future'] ?? 0),
        ];
    }

    /**
     * Build hobbies quality metrics
     */
    private function buildHobbiesMetrics(array $metrics, int $totalRecords): array
    {
        $nullCount = (int) ($metrics['hobbies_null'] ?? 0);

        return [
            'total' => $totalRecords,
            'null_count' => $nullCount,
            'null_percent' => round(($nullCount * 100.0 / max($totalRecords, 1)), 1),
            'with_special_chars' => (int) ($metrics['hobbies_special_chars'] ?? 0),
            'with_emoji' => (int) ($metrics['hobbies_emoji'] ?? 0),
        ];
    }

    /**
     * Mask phone number for examples
     */
    private function maskPhoneExample(string $phone): string
    {
        if (strlen($phone) < 8) {
            return '****';
        }
        $start = substr($phone, 0, 4);
        $end = substr($phone, -2);
        return $start . '****' . $end;
    }
}
