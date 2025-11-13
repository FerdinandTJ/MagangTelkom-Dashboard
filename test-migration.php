#!/usr/bin/env php
<?php

/**
 * Database Migration Testing Script
 * 
 * This script tests the database migrations and data integrity
 * after the major restructure from company_regions to account_manager structure.
 * 
 * Usage: php artisan migrate:fresh && php artisan db:seed && php test-migration.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationTester
{
    protected $results = [];
    protected $passed = 0;
    protected $failed = 0;

    public function run()
    {
        echo "\n╔════════════════════════════════════════════════════════╗\n";
        echo "║   Database Migration Testing Script                   ║\n";
        echo "║   Testing new database structure                       ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n\n";

        // Run tests
        $this->testTableStructure();
        $this->testPrimaryKeys();
        $this->testForeignKeys();
        $this->testDataIntegrity();
        $this->testRelationships();
        $this->testQueries();

        // Print summary
        $this->printSummary();

        return $this->failed === 0;
    }

    protected function testTableStructure()
    {
        echo "📋 Testing Table Structure...\n";
        
        $tables = [
            'regions' => ['id', 'code', 'description'],
            'witels' => ['idwitels', 'nama_witels', 'region_id'],
            'account_managers' => ['nik', 'nama', 'posisi', 'no_gsm', 'idwitels'],
            'companies' => ['nip_nas', 'nama_perusahaan', 'subsegment', 'source_data'],
            'revenues' => ['id', 'nip_nas', 'tahun', 'bulan', 'total_revenue'],
            'account_manager_company' => ['id', 'nik_am', 'nip_nas', 'proporsi', 'pembagian'],
            'lini_waktu' => ['id', 'nik_am', 'tahun', 'quartal', 'bulan_awal', 'bulan_akhir'],
            'target_account_m' => ['id', 't_revenue', 't_scalling', 't_datin'],
            'lini_waktu_target' => ['id', 'lini_waktu_id', 'target_id', 'r_revenue'],
        ];

        foreach ($tables as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $this->fail("Table '{$table}' does not exist");
                continue;
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $this->pass("Column '{$table}.{$column}' exists");
                } else {
                    $this->fail("Column '{$table}.{$column}' missing");
                }
            }
        }

        // Check that old table is removed
        if (!Schema::hasTable('company_regions')) {
            $this->pass("Old 'company_regions' table successfully removed");
        } else {
            $this->fail("Old 'company_regions' table still exists");
        }
    }

    protected function testPrimaryKeys()
    {
        echo "\n🔑 Testing Primary Keys...\n";

        // Test Companies PK
        $company = DB::table('companies')->first();
        if ($company && isset($company->nip_nas) && !isset($company->id)) {
            $this->pass("Companies table uses 'nip_nas' as PK (not 'id')");
        } else {
            $this->fail("Companies table PK structure incorrect");
        }

        // Test Witels PK
        $witel = DB::table('witels')->first();
        if ($witel && isset($witel->idwitels)) {
            $this->pass("Witels table uses 'idwitels' as PK");
        } else {
            $this->fail("Witels table PK structure incorrect");
        }

        // Test AccountManager PK
        $am = DB::table('account_managers')->first();
        if ($am && isset($am->nik)) {
            $this->pass("Account managers table uses 'nik' as PK");
        } else {
            $this->fail("Account managers table PK structure incorrect");
        }
    }

    protected function testForeignKeys()
    {
        echo "\n🔗 Testing Foreign Keys...\n";

        // Test Revenue FK to Companies
        $revenue = DB::table('revenues')
            ->join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
            ->first();
        
        if ($revenue) {
            $this->pass("Revenues properly joined to Companies via 'nip_nas'");
        } else {
            $this->fail("Revenues FK to Companies broken");
        }

        // Test AM FK to Witels
        $am = DB::table('account_managers')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->first();
        
        if ($am) {
            $this->pass("Account Managers properly joined to Witels");
        } else {
            $this->fail("Account Managers FK to Witels broken");
        }

        // Test Witels FK to Regions
        $witel = DB::table('witels')
            ->join('regions', 'witels.region_id', '=', 'regions.id')
            ->first();
        
        if ($witel) {
            $this->pass("Witels properly joined to Regions");
        } else {
            $this->fail("Witels FK to Regions broken");
        }

        // Test Pivot FK
        $pivot = DB::table('account_manager_company')
            ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
            ->join('companies', 'account_manager_company.nip_nas', '=', 'companies.nip_nas')
            ->first();
        
        if ($pivot) {
            $this->pass("Pivot table properly joined to both AMs and Companies");
        } else {
            $this->fail("Pivot table FK relationships broken");
        }
    }

    protected function testDataIntegrity()
    {
        echo "\n✓ Testing Data Integrity...\n";

        // Test Regions
        $regionCount = DB::table('regions')->count();
        if ($regionCount >= 6) {
            $this->pass("Regions seeded successfully ($regionCount regions)");
        } else {
            $this->fail("Regions not properly seeded (found $regionCount, expected >= 6)");
        }

        // Check for new ENUM codes
        $hqRegion = DB::table('regions')->where('code', 'HQ TREG2')->first();
        $treg1 = DB::table('regions')->where('code', 'TREG1')->first();
        
        if ($hqRegion && $treg1) {
            $this->pass("New region ENUM codes (HQ TREG2, TREG1-5) properly set");
        } else {
            $this->fail("Old region codes detected (expected HQ TREG2, TREG1-5)");
        }

        // Test Witels
        $witelCount = DB::table('witels')->count();
        if ($witelCount >= 40) {
            $this->pass("Witels seeded successfully ($witelCount witels)");
        } else {
            $this->fail("Witels not properly seeded (found $witelCount, expected >= 40)");
        }

        // Test custom IDs
        $witelWithCustomId = DB::table('witels')
            ->whereBetween('idwitels', [1001, 5012])
            ->count();
        
        if ($witelWithCustomId > 0) {
            $this->pass("Witels using custom ID pattern (1001-5012)");
        } else {
            $this->fail("Witels not using custom ID pattern");
        }

        // Test Account Managers
        $amCount = DB::table('account_managers')->count();
        if ($amCount > 0) {
            $this->pass("Account managers seeded successfully ($amCount AMs)");
        } else {
            $this->fail("No account managers found in database");
        }

        // Test field renames
        $witel = DB::table('witels')->first();
        if ($witel && isset($witel->nama_witels) && !isset($witel->name)) {
            $this->pass("Witel field renamed from 'name' to 'nama_witels'");
        } else {
            $this->fail("Witel field rename not applied correctly");
        }

        $revenue = DB::table('revenues')->first();
        if ($revenue && isset($revenue->total_revenue) && !isset($revenue->revenue)) {
            $this->pass("Revenue field renamed from 'revenue' to 'total_revenue'");
        } else {
            $this->fail("Revenue field rename not applied correctly");
        }
    }

    protected function testRelationships()
    {
        echo "\n🔄 Testing Model Relationships...\n";

        // Test Company → AccountManagers (many-to-many)
        try {
            $company = \App\Models\Company::with('accountManagers')->first();
            if ($company && method_exists($company, 'accountManagers')) {
                $this->pass("Company → AccountManagers relationship exists");
            } else {
                $this->fail("Company → AccountManagers relationship missing");
            }
        } catch (\Exception $e) {
            $this->fail("Company model relationship error: " . $e->getMessage());
        }

        // Test AccountManager → Witel (belongs to)
        try {
            $am = \App\Models\AccountManager::with('witel')->first();
            if ($am && method_exists($am, 'witel')) {
                $this->pass("AccountManager → Witel relationship exists");
            } else {
                $this->fail("AccountManager → Witel relationship missing");
            }
        } catch (\Exception $e) {
            $this->fail("AccountManager model relationship error: " . $e->getMessage());
        }

        // Test Witel → Region (belongs to)
        try {
            $witel = \App\Models\Witel::with('region')->first();
            if ($witel && method_exists($witel, 'region')) {
                $this->pass("Witel → Region relationship exists");
            } else {
                $this->fail("Witel → Region relationship missing");
            }
        } catch (\Exception $e) {
            $this->fail("Witel model relationship error: " . $e->getMessage());
        }

        // Test Revenue → Company (belongs to via nip_nas)
        try {
            $revenue = \App\Models\Revenue::with('company')->first();
            if ($revenue && method_exists($revenue, 'company')) {
                $this->pass("Revenue → Company relationship exists");
            } else {
                $this->fail("Revenue → Company relationship missing");
            }
        } catch (\Exception $e) {
            $this->fail("Revenue model relationship error: " . $e->getMessage());
        }
    }

    protected function testQueries()
    {
        echo "\n🔍 Testing Complex Queries...\n";

        // Test query: Get companies with their regions through AMs
        try {
            $result = DB::table('companies')
                ->join('account_manager_company', 'companies.nip_nas', '=', 'account_manager_company.nip_nas')
                ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
                ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
                ->join('regions', 'witels.region_id', '=', 'regions.id')
                ->select('companies.nama_perusahaan', 'regions.code', 'regions.description')
                ->limit(1)
                ->first();
            
            if ($result) {
                $this->pass("Complex query: Company → AM → Witel → Region works");
            } else {
                $this->fail("Complex query returned no results");
            }
        } catch (\Exception $e) {
            $this->fail("Complex query failed: " . $e->getMessage());
        }

        // Test query: Get revenues with company data
        try {
            $result = DB::table('revenues')
                ->join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
                ->select('revenues.total_revenue', 'companies.nama_perusahaan')
                ->limit(1)
                ->first();
            
            if ($result && isset($result->total_revenue)) {
                $this->pass("Revenue join query using 'nip_nas' FK works");
            } else {
                $this->fail("Revenue join query failed or returned wrong fields");
            }
        } catch (\Exception $e) {
            $this->fail("Revenue query failed: " . $e->getMessage());
        }

        // Test query: Regional revenue aggregation
        try {
            $result = DB::table('revenues')
                ->join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
                ->join('account_manager_company', 'companies.nip_nas', '=', 'account_manager_company.nip_nas')
                ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
                ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
                ->join('regions', 'witels.region_id', '=', 'regions.id')
                ->select('regions.code', DB::raw('SUM(revenues.total_revenue) as total'))
                ->groupBy('regions.code')
                ->get();
            
            if ($result && $result->count() > 0) {
                $this->pass("Regional revenue aggregation query works ({$result->count()} regions)");
            } else {
                $this->fail("Regional aggregation returned no results");
            }
        } catch (\Exception $e) {
            $this->fail("Regional aggregation failed: " . $e->getMessage());
        }
    }

    protected function pass($message)
    {
        $this->passed++;
        echo "  ✅ " . $message . "\n";
    }

    protected function fail($message)
    {
        $this->failed++;
        echo "  ❌ " . $message . "\n";
    }

    protected function printSummary()
    {
        $total = $this->passed + $this->failed;
        $passRate = $total > 0 ? round(($this->passed / $total) * 100, 1) : 0;

        echo "\n╔════════════════════════════════════════════════════════╗\n";
        echo "║   Test Summary                                         ║\n";
        echo "╠════════════════════════════════════════════════════════╣\n";
        printf("║   Total Tests:     %-33s  ║\n", $total);
        printf("║   Passed:          %-33s  ║\n", "\033[32m$this->passed\033[0m");
        printf("║   Failed:          %-33s  ║\n", $this->failed > 0 ? "\033[31m$this->failed\033[0m" : "0");
        printf("║   Pass Rate:       %-33s  ║\n", "$passRate%");
        echo "╠════════════════════════════════════════════════════════╣\n";
        
        if ($this->failed === 0) {
            echo "║   \033[32m✓ All tests passed! Migration successful!\033[0m         ║\n";
        } else {
            echo "║   \033[31m✗ Some tests failed. Check errors above.\033[0m          ║\n";
        }
        
        echo "╚════════════════════════════════════════════════════════╝\n\n";
    }
}

// Run the tester
$tester = new MigrationTester();
$success = $tester->run();

exit($success ? 0 : 1);
