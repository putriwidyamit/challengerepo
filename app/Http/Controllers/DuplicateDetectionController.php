<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DuplicateDetectionController extends Controller
{
    private const CACHE_TTL_SECONDS = 10;
    private const COMMON_IPS = [
        '127.0.0.1', '127.0.0.2', '192.168.0.1', '192.168.1.1',
        '10.0.0.1', '10.0.0.2', '172.16.0.1', '172.31.255.254'
    ];

    /**
     * GET /api/duplicates/find?method=ip_address|phone|email|name|combined&limit=50
     * Detect potential duplicate accounts using specified method
     */
    public function find()
    {
        $method = request()->query('method', 'combined');
        $limit = min(abs((int) request()->query('limit', 50)), 500);

        // Validate method
        $validMethods = ['ip_address', 'phone', 'email', 'name', 'combined'];
        if (!in_array($method, $validMethods)) {
            return response()->json([
                'error' => 'Unsupported duplicate detection method',
                'supported_methods' => $validMethods,
            ], 400);
        }

        $startTime = microtime(true);

        try {
            $duplicateGroups = [];

            switch ($method) {
                case 'ip_address':
                    $duplicateGroups = $this->detectByIpAddress($limit);
                    break;
                case 'phone':
                    $duplicateGroups = $this->detectByPhone($limit);
                    break;
                case 'email':
                    $duplicateGroups = $this->detectByEmail($limit);
                    break;
                case 'name':
                    $duplicateGroups = $this->detectByName($limit);
                    break;
                case 'combined':
                    $duplicateGroups = $this->detectCombined($limit);
                    break;
            }

            // Sort by similarity score descending
            usort($duplicateGroups, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

            // Count unique users across all groups
            $uniqueUsers = [];
            foreach ($duplicateGroups as $group) {
                foreach ($group['user_ids'] as $userId) {
                    $uniqueUsers[$userId] = true;
                }
            }

            $tookMs = (int) round((microtime(true) - $startTime) * 1000);

            return response()->json([
                'method' => $method,
                'duplicate_groups' => array_values(array_slice($duplicateGroups, 0, $limit)),
                'total_groups_found' => count($duplicateGroups),
                'total_duplicate_users' => count($uniqueUsers),
                'took_ms' => $tookMs,
            ]);
        } catch (\Throwable $e) {
            logger()->error('Duplicate detection error', [
                'method' => $method,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Duplicate detection failed',
            ], 503);
        }
    }

    /**
     * NOTE: IP-based detection not implemented because ws_user_activity table doesn't exist
     * This would require activity tracking which is not available in current schema
     */
    private function detectByIpAddress(int $limit): array
    {
        // IP tracking not available - no ws_user_activity table exists
        logger()->warning('IP address duplicate detection requested but ws_user_activity table not available');
        return [];
    }

    /**
     * Detect duplicates by exact phone number match
     */
    private function detectByPhone(int $limit): array
    {
        $groups = [];

        // Find phone numbers used by multiple users
        $duplicatePhones = DB::select(
            "SELECT TRIM(msisdn) as phone, COUNT(DISTINCT user_id) as user_count,
                    ARRAY_AGG(DISTINCT user_id) as user_ids
             FROM ws_user
             WHERE msisdn IS NOT NULL AND TRIM(msisdn) != ''
             GROUP BY TRIM(msisdn)
             HAVING COUNT(DISTINCT user_id) > 1
             ORDER BY user_count DESC
             LIMIT :limit",
            ['limit' => $limit]
        );

        $groupId = 1;
        foreach ($duplicatePhones as $row) {
            $userIds = json_decode($row->user_ids);
            
            if (count($userIds) < 2) {
                continue;
            }

            $users = $this->loadUsers($userIds);

            $groups[] = [
                'group_id' => $groupId++,
                'shared_attribute' => $this->maskPhoneExample($row->phone),
                'attribute_type' => 'phone',
                'user_count' => count($userIds),
                'user_ids' => $userIds,
                'user_names' => array_map(fn($u) => $u->full_name ?? $u->user_name ?? "User {$u->user_id}", $users),
                'first_activity' => min(array_map(fn($u) => $u->create_time, $users)),
                'last_activity' => max(array_map(fn($u) => $u->update_time, $users)),
                'confidence' => 'high',
                'similarity_score' => 0.95,
                'match_reasons' => [
                    count($userIds) . ' users share the same phone number',
                    'Exact phone match (high confidence)',
                ],
            ];
        }

        return $groups;
    }

    /**
     * Detect duplicates by exact email match (case-insensitive)
     */
    private function detectByEmail(int $limit): array
    {
        $groups = [];

        // Find emails used by multiple users (case-insensitive)
        $duplicateEmails = DB::select(
            "SELECT LOWER(TRIM(user_email)) as email, COUNT(DISTINCT user_id) as user_count,
                    ARRAY_AGG(DISTINCT user_id) as user_ids
             FROM ws_user
             WHERE user_email IS NOT NULL AND TRIM(user_email) != ''
             GROUP BY LOWER(TRIM(user_email))
             HAVING COUNT(DISTINCT user_id) > 1
             ORDER BY user_count DESC
             LIMIT :limit",
            ['limit' => $limit]
        );

        $groupId = 1;
        foreach ($duplicateEmails as $row) {
            $userIds = json_decode($row->user_ids);
            
            if (count($userIds) < 2) {
                continue;
            }

            $users = $this->loadUsers($userIds);

            $groups[] = [
                'group_id' => $groupId++,
                'shared_attribute' => $row->email,
                'attribute_type' => 'email',
                'user_count' => count($userIds),
                'user_ids' => $userIds,
                'user_names' => array_map(fn($u) => $u->full_name ?? $u->user_name ?? "User {$u->user_id}", $users),
                'first_activity' => min(array_map(fn($u) => $u->create_time, $users)),
                'last_activity' => max(array_map(fn($u) => $u->update_time, $users)),
                'confidence' => 'high',
                'similarity_score' => 0.98,
                'match_reasons' => [
                    count($userIds) . ' users share the same email address',
                    'Exact email match (normalized, case-insensitive)',
                ],
            ];
        }

        return $groups;
    }

    /**
     * Detect duplicates by name similarity (Levenshtein distance)
     */
    private function detectByName(int $limit): array
    {
        $groups = [];

        // Get all users with names for similarity comparison
        $users = DB::select(
            "SELECT user_id, full_name, user_name, create_time, update_time
             FROM ws_user
             WHERE full_name IS NOT NULL AND TRIM(full_name) != ''
             ORDER BY user_id"
        );

        if (empty($users)) {
            return [];
        }

        // Group similar names (similarity >= 0.85)
        $processed = [];
        $groupId = 1;

        for ($i = 0; $i < count($users) && count($groups) < $limit; $i++) {
            if (isset($processed[$users[$i]->user_id])) {
                continue;
            }

            $similarGroup = [$users[$i]];
            $processed[$users[$i]->user_id] = true;

            for ($j = $i + 1; $j < count($users); $j++) {
                if (isset($processed[$users[$j]->user_id])) {
                    continue;
                }

                $similarity = $this->calculateNameSimilarity(
                    $this->normalizeName($users[$i]->full_name),
                    $this->normalizeName($users[$j]->full_name)
                );

                if ($similarity >= 0.85) {
                    $similarGroup[] = $users[$j];
                    $processed[$users[$j]->user_id] = true;
                }
            }

            if (count($similarGroup) >= 2) {
                $userIds = array_map(fn($u) => $u->user_id, $similarGroup);

                $groups[] = [
                    'group_id' => $groupId++,
                    'shared_attribute' => $users[$i]->full_name,
                    'attribute_type' => 'name_similarity',
                    'user_count' => count($similarGroup),
                    'user_ids' => $userIds,
                    'user_names' => array_map(fn($u) => $u->full_name ?? $u->user_name ?? "User {$u->user_id}", $similarGroup),
                    'first_activity' => min(array_map(fn($u) => $u->create_time, $similarGroup)),
                    'last_activity' => max(array_map(fn($u) => $u->update_time, $similarGroup)),
                    'confidence' => $this->getNameSimilarityConfidence(count($similarGroup)),
                    'similarity_score' => $this->calculateGroupNameSimilarity($similarGroup),
                    'match_reasons' => [
                        count($similarGroup) . ' users have highly similar names',
                        'Name similarity score: ' . round($this->calculateGroupNameSimilarity($similarGroup), 2),
                    ],
                ];
            }
        }

        return $groups;
    }

    /**
     * Combined duplicate detection using multiple signals
     */
    private function detectCombined(int $limit): array
    {
        $allGroups = [];

        // Combine all detection methods
        $allGroups = array_merge(
            $this->detectByEmail(floor($limit / 3)),
            $this->detectByPhone(floor($limit / 3)),
            $this->detectByName(floor($limit / 3))
        );

        // Also detect users with multiple matching signals
        $multiSignalGroups = $this->detectMultipleSignals($limit);
        $allGroups = array_merge($allGroups, $multiSignalGroups);

        // Remove duplicates and sort
        $uniqueGroups = [];
        $seen = [];
        
        foreach ($allGroups as $group) {
            $groupKey = implode(',', sort($group['user_ids']));
            if (!isset($seen[$groupKey])) {
                $seen[$groupKey] = true;
                $uniqueGroups[] = $group;
            }
        }

        usort($uniqueGroups, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

        return array_slice($uniqueGroups, 0, $limit);
    }

    /**
     * Detect duplicates with multiple matching signals (same email OR phone)
     */
    private function detectMultipleSignals(int $limit): array
    {
        $groups = [];

        // Find users sharing either email or phone
        $multiSignalDuplicates = DB::select(
            "SELECT 
                CASE 
                    WHEN LOWER(TRIM(u1.user_email)) = LOWER(TRIM(u2.user_email)) AND u1.user_email IS NOT NULL
                        THEN 'email'
                    WHEN TRIM(u1.msisdn) = TRIM(u2.msisdn) AND u1.msisdn IS NOT NULL
                        THEN 'phone'
                    ELSE 'other'
                END as signal_type,
                ARRAY[u1.user_id, u2.user_id] as user_ids,
                u1.full_name as name1,
                u2.full_name as name2
             FROM ws_user u1
             JOIN ws_user u2 ON u1.user_id < u2.user_id
             WHERE (LOWER(TRIM(u1.user_email)) = LOWER(TRIM(u2.user_email)) AND u1.user_email IS NOT NULL)
                OR (TRIM(u1.msisdn) = TRIM(u2.msisdn) AND u1.msisdn IS NOT NULL)
             LIMIT :limit",
            ['limit' => $limit]
        );

        $groupId = 1;
        $seen = [];

        foreach ($multiSignalDuplicates as $row) {
            $groupKey = implode(',', sort($row->user_ids));
            if (isset($seen[$groupKey])) {
                continue;
            }
            $seen[$groupKey] = true;

            $users = $this->loadUsers($row->user_ids);
            $sharedAttribute = $row->signal_type === 'email' 
                ? $users[0]->user_email 
                : $this->maskPhoneExample($users[0]->msisdn);

            $groups[] = [
                'group_id' => $groupId++,
                'shared_attribute' => $sharedAttribute,
                'attribute_type' => $row->signal_type,
                'user_count' => 2,
                'user_ids' => $row->user_ids,
                'user_names' => array_map(fn($u) => $u->full_name ?? $u->user_name ?? "User {$u->user_id}", $users),
                'first_activity' => min(array_map(fn($u) => $u->create_time, $users)),
                'last_activity' => max(array_map(fn($u) => $u->update_time, $users)),
                'confidence' => 'high',
                'similarity_score' => 0.92,
                'match_reasons' => [
                    '2 users share the same ' . $row->signal_type,
                ],
            ];
        }

        return $groups;
    }

    /**
     * Load full user details by IDs
     */
    private function loadUsers(array $userIds): array
    {
        return DB::select(
            "SELECT user_id, user_name, full_name, user_email, msisdn, status, create_time, update_time
             FROM ws_user
             WHERE user_id = ANY(:ids)
             ORDER BY user_id",
            ['ids' => $userIds]
        );
    }

    /**
     * Normalize names for comparison
     */
    private function normalizeName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }

    /**
     * Calculate similarity between two names using Levenshtein distance
     */
    private function calculateNameSimilarity(string $name1, string $name2): float
    {
        $maxLen = max(strlen($name1), strlen($name2));
        if ($maxLen === 0) {
            return 1.0;
        }

        $distance = levenshtein($name1, $name2);
        return 1.0 - ($distance / $maxLen);
    }

    /**
     * Calculate average name similarity for a group
     */
    private function calculateGroupNameSimilarity(array $users): float
        {
        if (count($users) < 2) {
            return 1.0;
        }

        $similarities = [];
        $normalizedNames = array_map(fn($u) => $this->normalizeName($u->full_name ?? $u->user_name ?? ''), $users);

        for ($i = 0; $i < count($normalizedNames); $i++) {
            for ($j = $i + 1; $j < count($normalizedNames); $j++) {
                $similarities[] = $this->calculateNameSimilarity($normalizedNames[$i], $normalizedNames[$j]);
            }
        }

        return empty($similarities) ? 0.0 : array_sum($similarities) / count($similarities);
    }

    /**
     * Get confidence level based on group size and signals
     */
    private function getNameSimilarityConfidence(int $groupSize): string
    {
        if ($groupSize >= 4) {
            return 'high';
        } else if ($groupSize === 3) {
            return 'medium';
        }
        return 'low';
    }

    /**
     * Mask phone number example
     */
    private function maskPhoneExample(string $phone): string
    {
        if (strlen($phone) < 8) {
            return '****';
        }
        $start = substr($phone, 0, 4);
        $end = substr($phone, -2);
        return "{$start}****{$end}";
    }
}
