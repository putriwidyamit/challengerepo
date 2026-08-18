<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HealthController extends Controller
{
    /**
     * Get health status of the application
     */
    public function check()
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            $database = 'connected';
        } catch (\Exception $e) {
            $database = 'disconnected';
        }

        // Get total records from database (sum of all tables)
        $totalRecords = $this->getTotalRecords();

        $response = [
            'status' => 'ready',
            'total_records' => $totalRecords,
            'database' => $database,
            'timestamp' => Carbon::now()->toIso8601String(),
        ];

        return response()->json($response);
    }

    /**
     * Get total records count from all tables
     */
    private function getTotalRecords()
    {
        try {
            $tables = DB::select("
                SELECT schemaname, tablename 
                FROM pg_tables 
                WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
            ");

            $totalRecords = 0;

            foreach ($tables as $table) {
                $count = DB::table($table->tablename)->count();
                $totalRecords += $count;
            }

            return $totalRecords;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Show health check UI
     */
    public function dashboard()
    {
        return view('health-dashboard');
    }
}
