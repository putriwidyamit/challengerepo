<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WsUser;
use Carbon\Carbon;

class SearchController extends Controller
{
    /**
     * Search across ws_user table with type-specific optimization
     *
     * GET /api/search?q=query&type=email|phone|user_id|name&limit=10&offset=0
     */
    public function search(Request $request)
    {
        $startTime = microtime(true);

        // Validate input
        $validated = $this->validateSearchInput($request);
        if ($validated !== true) {
            return response()->json($validated, 400);
        }

        $query = $request->input('q', '');
        $type = $request->input('type', 'name');
        $limit = (int) $request->input('limit', 10);
        $offset = (int) $request->input('offset', 0);

        try {
            // Route to type-specific search method
            $result = match ($type) {
                'email' => $this->searchEmail($query, $limit, $offset),
                'phone' => $this->searchPhone($query, $limit, $offset),
                'user_id' => $this->searchUserId($query, $limit, $offset),
                'name' => $this->searchName($query, $limit, $offset),
                default => [
                    'results' => [],
                    'total' => 0,
                ],
            };

            $took = (int) round((microtime(true) - $startTime) * 1000);

            return response()->json([
                'query' => $query,
                'type' => $type,
                'limit' => $limit,
                'offset' => $offset,
                'results' => $result['results'],
                'total' => $result['total'],
                'took_ms' => $took,
            ]);
        } catch (\Exception $e) {
            logger()->error('Search error', [
                'exception' => $e->getMessage(),
                'query' => $query,
                'type' => $type,
            ]);

            return response()->json([
                'error' => 'Database error',
            ], 500);
        }
    }

    /**
     * Search by exact email
     */
    private function searchEmail(string $email, int $limit, int $offset): array
    {
        // Get total count
        $total = DB::table('ws_user')
            ->whereRaw('user_email = ?', [$email])
            ->count();

        if ($total === 0) {
            return ['results' => [], 'total' => 0];
        }

        // Get results
        $users = DB::table('ws_user')
            ->selectRaw('user_id, user_name, full_name, user_email, msisdn, status, create_time')
            ->whereRaw('user_email = ?', [$email])
            ->limit($limit)
            ->offset($offset)
            ->get();

        return [
            'results' => $this->formatResults($users),
            'total' => $total,
        ];
    }

    /**
     * Search by exact phone number
     */
    private function searchPhone(string $phone, int $limit, int $offset): array
    {
        // Get total count
        $total = DB::table('ws_user')
            ->whereRaw('msisdn = ?', [$phone])
            ->count();

        if ($total === 0) {
            return ['results' => [], 'total' => 0];
        }

        // Get results
        $users = DB::table('ws_user')
            ->selectRaw('user_id, user_name, full_name, user_email, msisdn, status, create_time')
            ->whereRaw('msisdn = ?', [$phone])
            ->limit($limit)
            ->offset($offset)
            ->get();

        return [
            'results' => $this->formatResults($users),
            'total' => $total,
        ];
    }

    /**
     * Search by user ID (primary key)
     */
    private function searchUserId(string $userIdInput, int $limit, int $offset): array
    {
        // Validate and parse as BIGINT
        if (!is_numeric($userIdInput) || $userIdInput < 0) {
            return ['results' => [], 'total' => 0];
        }

        $userId = (int) $userIdInput;

        // Primary key search (always exact, max 1 result)
        $user = DB::table('ws_user')
            ->selectRaw('user_id, user_name, full_name, user_email, msisdn, status, create_time')
            ->whereRaw('user_id = ?', [$userId])
            ->first();

        if (!$user) {
            return ['results' => [], 'total' => 0];
        }

        return [
            'results' => [$this->formatResult($user)],
            'total' => 1,
        ];
    }

    /**
     * Search by name (fuzzy/partial)
     */
    private function searchName(string $name, int $limit, int $offset): array
    {
        // Get total count
        $total = DB::table('ws_user')
            ->whereRaw('full_name ILIKE ?', ['%' . $name . '%'])
            ->count();

        if ($total === 0) {
            return ['results' => [], 'total' => 0];
        }

        // Get results with deterministic ordering
        $users = DB::table('ws_user')
            ->selectRaw('user_id, user_name, full_name, user_email, msisdn, status, create_time')
            ->whereRaw('full_name ILIKE ?', ['%' . $name . '%'])
            ->orderByRaw('full_name ASC, user_id ASC')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return [
            'results' => $this->formatResults($users),
            'total' => $total,
        ];
    }

    /**
     * Format multiple results
     */
    private function formatResults($users): array
    {
        return $users->map(fn($user) => $this->formatResult($user))->toArray();
    }

    /**
     * Format single result with proper field mapping and phone masking
     */
    private function formatResult($user): array
    {
        return [
            'user_id' => $user->user_id,
            'full_name' => $user->full_name,
            'user_email' => $user->user_email,
            'msisdn' => WsUser::maskPhone($user->msisdn),
            'status' => $user->status,
            'created_at' => $user->create_time ? Carbon::parse($user->create_time)->toIso8601String() : null,
        ];
    }

    /**
     * Validate search input
     */
    private function validateSearchInput(Request $request): array|bool
    {
        $query = $request->input('q');
        $type = $request->input('type');
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);

        // Check required fields
        if ($query === null || $query === '') {
            return ['error' => 'Query parameter q is required'];
        }

        if ($type === null || $type === '') {
            return ['error' => 'Type parameter is required'];
        }

        // Validate type
        $validTypes = ['email', 'phone', 'user_id', 'name'];
        if (!in_array($type, $validTypes)) {
            return ['error' => 'Invalid type. Must be one of: ' . implode(', ', $validTypes)];
        }

        // Validate limit
        if (!is_numeric($limit)) {
            return ['error' => 'Limit must be numeric'];
        }

        $limit = (int) $limit;
        if ($limit < 1 || $limit > 100) {
            return ['error' => 'Limit must be between 1 and 100'];
        }

        // Validate offset
        if (!is_numeric($offset)) {
            return ['error' => 'Offset must be numeric'];
        }

        $offset = (int) $offset;
        if ($offset < 0) {
            return ['error' => 'Offset must be non-negative'];
        }

        // Validate query length (prevent excessively long queries)
        if (strlen($query) > 1000) {
            return ['error' => 'Query string exceeds maximum length of 1000 characters'];
        }

        return true;
    }
}
