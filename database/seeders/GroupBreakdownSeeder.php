<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Group1;
use App\Models\Group2;
use App\Models\Group3;
use App\Models\Group4;

class GroupBreakdownSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies from database
        $companies = Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->warn('⚠️  No companies found. Please seed companies first.');
            return;
        }

        $this->command->info('🔄 Creating revenue breakdown hierarchy for ' . $companies->count() . ' companies...');

        // Helper to create hierarchy with target
        $makeLeaf = function ($company, $g1Name, $g2Name, $g3Name, $g4Name, $rev, $target, $tahun, $bulan) {
            $g1 = Group1::firstOrCreate([
                'company_id' => $company->nip_nas,
                'nama_group1' => $g1Name
            ]);

            $g2 = Group2::firstOrCreate([
                'group1_id' => $g1->idGroup1,
                'nama_group2' => $g2Name
            ]);

            $g3 = Group3::firstOrCreate([
                'group2_id' => $g2->idGroup2,
                'nama_group3' => $g3Name
            ]);

            Group4::create([
                'group3_id' => $g3->idGroup3,
                'nama_group4' => $g4Name,
                'revenue_realisasi' => $rev,
                'revenue_target' => $target,
                'tahun' => $tahun,
                'bulan' => $bulan,
            ]);
        };

        // Define company yearly targets based on subsegment
        $subsegmentTargets = [
            'PTN' => 1500000000,      // Rp 1.5 Miliar
            'PTS' => 500000000,       // Rp 500 Juta
            'Hospital' => 700000000,  // Rp 700 Juta
            'Airport' => 2000000000,  // Rp 2 Miliar
            'Media' => 2500000000,    // Rp 2.5 Miliar
        ];

        // Define revenue breakdown structure (base values will be scaled per company)
        $revenueStructure = [
            ['CONNECTIVITY', 'Fixed Broadband', 'High Speed Internet', 'Abo HSI', 150000000],
            ['CONNECTIVITY', 'Fixed Broadband', 'High Speed Internet', 'PSB HSI', 75000000],
            ['CONNECTIVITY', 'Fixed Broadband', 'Wifi', 'Wifi Service', 50000000],
            ['CONNECTIVITY', 'ICT Platform', 'Devices / Hardware', 'NTE', 120000000],
            ['CONNECTIVITY', 'ICT Platform', 'Enterprise Connectivity', 'ASTINet', 90000000],
            ['LEGACY', 'Fixed Legacy', 'Voice & SMS', 'Wireline', 50000000],
            ['PLATFORM', 'Application Services & Smart Enablers', 'Device Enabler', 'Device Enabler', 30000000],
            ['PLATFORM', 'Data Center & Cloud', 'Cloud', 'Cloud Service', 20000000],
            ['SERVICE', 'Application Services & Smart Enablers', 'Content', 'Content Service', 25000000],
            ['SERVICE', 'Consumer Digital', 'Mobile Digital (Game, Music)', 'Game', 15000000],
            ['SERVICE', 'Consumer Digital', 'Mobile Digital (Game, Music)', 'Music', 10000000],
            ['SERVICE', 'Video / TV', 'Video / TV', 'IPTV', 40000000],
            ['SERVICE', 'Video / TV', 'Video / TV', 'USeeTV', 20000000],
        ];

        // Loop through each company and create revenue breakdown
        foreach ($companies as $company) {
            // Get target based on company subsegment
            $yearlyTarget = $subsegmentTargets[$company->subsegment] ?? 1000000000;
            
            // Calculate monthly base target (without seasonal adjustment)
            $monthlyBaseTarget = $yearlyTarget / 12;
            
            // Calculate base value for breakdown items
            $totalBaseValue = array_sum(array_column($revenueStructure, 4));
            
            // Generate data for 2023 (full year), 2024 (full year), 2025 (Jan - Nov)
            $periods = [
                ['year' => 2023, 'months' => range(1, 12), 'achievement' => 0.80 + (rand(0, 10) / 100)], // 80-90% (historical)
                ['year' => 2024, 'months' => range(1, 12), 'achievement' => 0.85 + (rand(0, 10) / 100)], // 85-95% (improved)
                ['year' => 2025, 'months' => range(1, 11), 'achievement' => 0.88 + (rand(0, 12) / 100)], // 88-100% (target year)
            ];

            foreach ($periods as $period) {
                $tahun = $period['year'];
                $yearAchievement = $period['achievement'];
                
                foreach ($period['months'] as $bulan) {
                    // Create realistic monthly variation:
                    // - Lower revenue at start of year (Q1)
                    // - Gradual increase through year
                    // - Higher revenue at end of year (Q4)
                    $seasonalFactor = 1.0;
                    if ($bulan <= 3) {
                        $seasonalFactor = 0.85 + (rand(0, 10) / 100); // Q1: 85-95%
                    } elseif ($bulan <= 6) {
                        $seasonalFactor = 0.90 + (rand(0, 10) / 100); // Q2: 90-100%
                    } elseif ($bulan <= 9) {
                        $seasonalFactor = 0.95 + (rand(0, 15) / 100); // Q3: 95-110%
                    } else {
                        $seasonalFactor = 1.05 + (rand(0, 20) / 100); // Q4: 105-125%
                    }
                    
                    // Calculate monthly target with seasonal adjustment
                    $monthlyTargetWithSeason = $monthlyBaseTarget * $seasonalFactor;
                    $targetScaleFactor = $monthlyTargetWithSeason / $totalBaseValue;
                    
                    // Calculate monthly revenue (actual achievement)
                    $revenueScaleFactor = $monthlyTargetWithSeason * $yearAchievement / $totalBaseValue;
                    
                    // Add small random noise (±5%) for realism
                    $randomNoise = 1 + (rand(-5, 5) / 100);
                    
                    foreach ($revenueStructure as $item) {
                        [$g1Name, $g2Name, $g3Name, $g4Name, $baseValue] = $item;
                        
                        // Add variation per product line (some products perform better than others)
                        $productVariation = 1 + (rand(-15, 15) / 100);
                        
                        // Calculate target and revenue for this product line
                        $target = $baseValue * $targetScaleFactor * (1 + (rand(-5, 5) / 100));
                        $revenue = $baseValue * $revenueScaleFactor * $productVariation * $randomNoise;
                        
                        $makeLeaf($company, $g1Name, $g2Name, $g3Name, $g4Name, $revenue, $target, $tahun, $bulan);
                    }
                }
            }
            
            $this->command->info('  ✓ Created breakdown for: ' . $company->nama_perusahaan . ' (2023-2025)');
        }

        $this->command->info('✅ Revenue breakdown hierarchy created for all companies!');
    }
}
