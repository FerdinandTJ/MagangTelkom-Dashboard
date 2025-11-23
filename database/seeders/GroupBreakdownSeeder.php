<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Group1;
use App\Models\Group2;
use App\Models\Group3;
use App\Models\Group4;
use App\Models\Revenue;

class GroupBreakdownSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * UPDATED TO USE NORMALIZED SCHEMA:
     * - Group4: Master product data only (no revenue fields)
     * - Revenue: Time-series revenue data (FK to Group4)
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

        // Helper to create hierarchy with revenue records (normalized schema)
        $makeLeaf = function ($company, $g1Name, $g2Name, $g3Name, $g4Name, $rev, $target, $tahun, $bulan) {
            // Step 1: Create/get Group hierarchy (master data - no time-series)
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

            // Step 2: Get or create Group4 product (master record - stable across time)
            // Use group3_id + nama_group4 as natural unique key
            $g4 = Group4::firstOrCreate(
                [
                    'group3_id' => $g3->idGroup3,
                    'nama_group4' => $g4Name
                ]
            );

            // Step 3: Create revenue record for this period (time-series data)
            // This is separate from product master - allows stable product ID across all months!
            Revenue::updateOrCreate(
                [
                    'group4_id' => $g4->idGroup4,
                    'tahun' => $tahun,
                    'bulan' => $bulan
                ],
                [
                    'revenue_realisasi' => $rev,
                    'revenue_target' => $target
                ]
            );
        };

        // Define company yearly targets based on subsegment
        $subsegmentTargets = [
            'PTN' => 1500000000,      // Rp 1.5 Miliar
            'PTS' => 500000000,       // Rp 500 Juta
            'Hospital' => 700000000,  // Rp 700 Juta
            'Airport' => 2000000000,  // Rp 2 Miliar
            'Media' => 2500000000,    // Rp 2.5 Miliar
        ];

        // Define revenue breakdown structure based on real Telkom product portfolio
        $revenueStructure = [
            // CONNECTIVITY - Fixed Broadband
            ['CONNECTIVITY', 'Fixed Broadband', 'High Speed Internet', ['Abo HSI'], 180000000],
            ['CONNECTIVITY', 'Fixed Broadband', 'High Speed Internet', ['PSB HSI'], 90000000],
            ['CONNECTIVITY', 'Fixed Broadband', 'Managed Wifi', ['Wifi.id', 'Managed Wifi Service'], 70000000],
            
            // CONNECTIVITY - ICT Platform
            ['CONNECTIVITY', 'ICT Platform', 'Devices / Hardware', ['NTE', 'CPE Device', 'Router Enterprise'], 120000000],
            ['CONNECTIVITY', 'ICT Platform', 'Enterprise Connectivity', ['ASTINet', 'MPLS', 'VPN IP'], 100000000],
            
            // CONNECTIVITY - Mobile Broadband
            ['CONNECTIVITY', 'Mobile Broadband', 'Mobile Data Solution', ['Orbit', '4G LTE', 'Mobile WiFi Router'], 85000000],
            ['CONNECTIVITY', 'Mobile Broadband', 'IoT & M2M', ['IoT Connectivity', 'M2M Platform'], 60000000],
            
            // LEGACY - Fixed Line Services
            ['LEGACY', 'Fixed Line', 'Telephony', ['PSTN', 'Speedy Legacy', 'Flexi Home'], 50000000],
            ['LEGACY', 'Fixed Line', 'ISDN & PRI', ['ISDN Service', 'PRI Trunk'], 35000000],
            
            // PLATFORM - Data Center & Cloud
            ['PLATFORM', 'Data Center & Cloud', 'Cloud Computing', ['Telkom Cloud', 'VPS', 'Cloud Storage'], 95000000],
            ['PLATFORM', 'Data Center & Cloud', 'Colocation Services', ['Rack Colocation', 'Private Suite'], 75000000],
            ['PLATFORM', 'Data Center & Cloud', 'Managed Services', ['Managed Server', 'DRC Services'], 65000000],
            
            // PLATFORM - IoT & Smart Solutions
            ['PLATFORM', 'IoT & Digital Platform', 'Smart City', ['Smart Parking', 'Smart Lighting', 'Traffic Management'], 55000000],
            ['PLATFORM', 'IoT & Digital Platform', 'Smart Building', ['BMS', 'Access Control', 'CCTV Integration'], 50000000],
            
            // SERVICE - Digital Entertainment
            ['SERVICE', 'Digital Content', 'Video Services', ['IndiHome TV', 'UseeTV Go'], 80000000],
            ['SERVICE', 'Digital Content', 'Entertainment Platform', ['MAXstream', 'Music Streaming'], 45000000],
            ['SERVICE', 'Digital Content', 'Gaming & E-Sports', ['Dunia Games', 'E-Sports Arena'], 35000000],
            
            // SERVICE - Enterprise Applications
            ['SERVICE', 'Enterprise Solutions', 'Business Applications', ['ERP Solutions', 'CRM Platform'], 70000000],
            ['SERVICE', 'Enterprise Solutions', 'Collaboration Tools', ['Video Conference', 'Collaboration Suite'], 60000000],
            ['SERVICE', 'Enterprise Solutions', 'Security Services', ['Cybersecurity', 'DDoS Protection'], 55000000],
            
            // SERVICE - Digital Services
            ['SERVICE', 'Digital Banking & Payment', 'Payment Gateway', ['T-Cash', 'Digital Wallet'], 40000000],
            ['SERVICE', 'Digital Banking & Payment', 'Financial Platform', ['LinkAja Integration'], 30000000],
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
                        [$g1Name, $g2Name, $g3Name, $g4Options, $baseValue] = $item;
                        
                        // Pick ONE product from the options array (different per company to create variation)
                        $companyIndex = $companies->search($company);
                        $g4Name = is_array($g4Options) 
                            ? $g4Options[$companyIndex % count($g4Options)] 
                            : $g4Options;
                        
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
