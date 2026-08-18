<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SeedDuplicateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:duplicates 
                            {count=1000 : Number of records to generate}
                            {--skip-truncate : Skip truncating existing data}
                            {--show-stats : Show detailed statistics after seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate large dataset with customizable duplicate data for testing duplicate detection API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->argument('count');
        $skipTruncate = $this->option('skip-truncate');
        $showStats = $this->option('show-stats');
        
        // Validate count
        if ($count < 10) {
            $this->error('❌ Minimum 10 records required');
            return self::FAILURE;
        }
        
        if ($count > 1000000) {
            $this->error('❌ Maximum 1,000,000 records allowed');
            return self::FAILURE;
        }
        
        // Show banner
        $this->showBanner();
        
        // Show plan
        $this->showPlan($count);
        
        // Confirm (skip in production/automated environments)
        if ($this->input->isInteractive()) {
            if (!$this->confirm("Do you want to proceed with seeding $count records?")) {
                $this->info('❌ Seeding cancelled');
                return self::FAILURE;
            }
        } else {
            $this->info("✓ Running in non-interactive mode, proceeding with seeding...");
        }
        
        // Clear existing data if needed
        if (!$skipTruncate) {
            $this->info("\n🧹 Clearing existing data...");
            DB::table('ws_user')->truncate();
            $this->info("✓ Database cleared");
        }
        
        // Run seeder
        $this->info("\n🌱 Starting seeder...\n");
        
        putenv("SEEDER_RECORD_COUNT=$count");
        Artisan::call('db:seed', [
            '--class' => 'LargeDatasetSeeder',
        ]);
        
        // Show stats if requested
        if ($showStats) {
            $this->showStatistics();
        }
        
        $this->successBanner();
        
        return self::SUCCESS;
    }
    
    private function showBanner(): void
    {
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║     📊 Duplicate Data Seeder for Testing              ║');
        $this->info('║     Generates customizable dataset with duplicates    ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
    }
    
    private function showSuccessBanner(): void
    {
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║          ✅ Seeding Complete!                         ║');
        $this->info('║     You can now test the duplicate detection API      ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
    }
    
    private function showPlan(int $count): void
    {
        $this->info("\n📋 Seeding Plan:");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        $emailDup = (int) ($count * 0.30);
        $phoneDup = (int) ($count * 0.25);
        $nameVar = (int) ($count * 0.20);
        $unique = (int) ($count * 0.20);
        $edge = $count - $emailDup - $phoneDup - $nameVar - $unique;
        
        $this->line("  📊 Total records: " . $this->formatNumber($count));
        $this->line("");
        $this->line("  📧 Email duplicates (30%):      " . $this->formatNumber($emailDup) . " records");
        $this->line("  📱 Phone duplicates (25%):      " . $this->formatNumber($phoneDup) . " records");
        $this->line("  👤 Name variations (20%):       " . $this->formatNumber($nameVar) . " records");
        $this->line("  🆔 Unique records (20%):        " . $this->formatNumber($unique) . " records");
        $this->line("  ⚠️  Edge cases (5%):             " . $this->formatNumber($edge) . " records");
        
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
    }
    
    private function showStatistics(): void
    {
        $this->info("\n📈 Database Statistics:");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        $total = DB::table('ws_user')->count();
        
        // Email analysis
        $emailGroups = DB::table('ws_user')
            ->whereNotNull('user_email')
            ->groupBy('user_email')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        $emailDupCount = DB::table('ws_user')
            ->whereNotNull('user_email')
            ->groupBy('user_email')
            ->havingRaw('COUNT(*) > 1')
            ->selectRaw('COUNT(*) as cnt')
            ->get()
            ->sum('cnt');
        
        // Phone analysis
        $phoneGroups = DB::table('ws_user')
            ->whereNotNull('msisdn')
            ->groupBy('msisdn')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        $phoneDupCount = DB::table('ws_user')
            ->whereNotNull('msisdn')
            ->groupBy('msisdn')
            ->havingRaw('COUNT(*) > 1')
            ->selectRaw('COUNT(*) as cnt')
            ->get()
            ->sum('cnt');
        
        $nullEmails = DB::table('ws_user')->whereNull('user_email')->count();
        $nullPhones = DB::table('ws_user')->whereNull('msisdn')->count();
        
        $this->line("  Total users:                 " . $this->formatNumber($total));
        $this->line("");
        $this->line("  📧 Email Analysis:");
        $this->line("    - Duplicate groups:       " . $this->formatNumber($emailGroups));
        $this->line("    - Users in duplicates:    " . $this->formatNumber($emailDupCount));
        $this->line("    - Null emails:            " . $this->formatNumber($nullEmails));
        $this->line("");
        $this->line("  📱 Phone Analysis:");
        $this->line("    - Duplicate groups:       " . $this->formatNumber($phoneGroups));
        $this->line("    - Users in duplicates:    " . $this->formatNumber($phoneDupCount));
        $this->line("    - Null phones:            " . $this->formatNumber($nullPhones));
        
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
    }
    
    private function formatNumber(int $num): string
    {
        return number_format($num);
    }
    
    private function successBanner(): void
    {
        $this->info('');
        $this->info("✅ Ready to test! Try the API:");
        $this->info("");
        $this->line("  curl -X GET \"http://localhost:8000/api/duplicates/find?method=email&limit=10\"");
        $this->line("  curl -X GET \"http://localhost:8000/api/duplicates/find?method=phone\"");
        $this->line("  curl -X GET \"http://localhost:8000/api/duplicates/find?method=combined\"");
        $this->info("");
    }
}
