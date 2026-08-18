<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class UserProfileController extends Controller
{
    /**
     * GET /api/user-profile/:user_id
     * 
     * Retrieves complete user profile with aggregated data from four related tables:
     * - ws_user (user profile)
     * - user_orders (orders)
     * - user_transactions (transactions)
     * - user_activity (activity logs)
     * 
     * Uses WITH clauses to pre-aggregate relationships before joining to avoid
     * Cartesian product multiplication (row multiplication issue).
     * 
     * @param int $user_id
     * @return Response
     */
    public function getProfile(int $user_id)
    {
        $startTime = microtime(true);

        try {
            // Optimized query using CTE (Common Table Expressions) to avoid row multiplication
            $result = DB::select('
                WITH order_summary AS (
                    SELECT 
                        user_id,
                        COUNT(*) AS order_count
                    FROM user_orders
                    WHERE user_id = ?
                    GROUP BY user_id
                ),
                transaction_summary AS (
                    SELECT 
                        user_id,
                        COUNT(*) AS transaction_count,
                        COALESCE(SUM(amount), 0) AS total_amount
                    FROM user_transactions
                    WHERE user_id = ?
                    GROUP BY user_id
                ),
                activity_summary AS (
                    SELECT 
                        user_id,
                        COUNT(*) AS activity_count,
                        MAX(activity_timestamp) AS last_activity
                    FROM user_activity
                    WHERE user_id = ?
                    GROUP BY user_id
                )
                SELECT 
                    u.user_id,
                    u.full_name AS name,
                    u.user_email AS email,
                    u.msisdn AS phone,
                    COALESCE(os.order_count, 0) AS order_count,
                    COALESCE(ts.transaction_count, 0) AS transaction_count,
                    COALESCE(ts.total_amount, 0) AS total_amount,
                    COALESCE(ac.activity_count, 0) AS activity_count,
                    ac.last_activity
                FROM ws_user u
                LEFT JOIN order_summary os ON os.user_id = u.user_id
                LEFT JOIN transaction_summary ts ON ts.user_id = u.user_id
                LEFT JOIN activity_summary ac ON ac.user_id = u.user_id
                WHERE u.user_id = ?
            ', [$user_id, $user_id, $user_id, $user_id]);

            if (empty($result)) {
                return response()->json([
                    'error' => 'User not found',
                ], 404);
            }

            $data = $result[0];
            $elapsed = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'user_id' => (int) $data->user_id,
                'profile' => [
                    'name' => $data->name,
                    'email' => $data->email,
                    'phone' => $data->phone,
                ],
                'orders' => [
                    'count' => (int) $data->order_count,
                ],
                'transactions' => [
                    'count' => (int) $data->transaction_count,
                    'total_amount' => (float) $data->total_amount,
                ],
                'activity' => [
                    'count' => (int) $data->activity_count,
                    'last_activity' => $data->last_activity ? \Carbon\Carbon::parse($data->last_activity)->toIso8601String() : null,
                ],
                '_meta' => [
                    'took_ms' => $elapsed,
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('User profile retrieval error', [
                'user_id' => $user_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Alternative implementation using subqueries (if CTEs cause issues)
     * This version is functionally equivalent but may have different performance
     */
    public function getProfileSubquery(int $user_id)
    {
        $startTime = microtime(true);

        try {
            $user = DB::table('ws_user')->where('user_id', $user_id)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Fetch aggregates separately to avoid row multiplication
            $orderCount = DB::table('user_orders')
                ->where('user_id', $user_id)
                ->count();

            $transactionData = DB::table('user_transactions')
                ->where('user_id', $user_id)
                ->selectRaw('COUNT(*) AS transaction_count, COALESCE(SUM(amount), 0) AS total_amount')
                ->first();

            $activityData = DB::table('user_activity')
                ->where('user_id', $user_id)
                ->selectRaw('COUNT(*) AS activity_count, MAX(activity_timestamp) AS last_activity')
                ->first();

            $elapsed = round((microtime(true) - $startTime) * 1000, 2);

            return response()->json([
                'user_id' => (int) $user->user_id,
                'profile' => [
                    'name' => $user->full_name,
                    'email' => $user->user_email,
                    'phone' => $user->msisdn,
                ],
                'orders' => [
                    'count' => (int) $orderCount,
                ],
                'transactions' => [
                    'count' => (int) $transactionData->transaction_count,
                    'total_amount' => (float) $transactionData->total_amount,
                ],
                'activity' => [
                    'count' => (int) $activityData->activity_count,
                    'last_activity' => $activityData->last_activity ? \Carbon\Carbon::parse($activityData->last_activity)->toIso8601String() : null,
                ],
                '_meta' => [
                    'took_ms' => $elapsed,
                    'method' => 'subquery',
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('User profile retrieval error (subquery)', [
                'user_id' => $user_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
