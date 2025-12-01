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

        // Define company yearly targets based on subsegment (more realistic & varied)
        $subsegmentTargets = [
            'PTN' => 1500000000,                    // Rp 1.5 Miliar (University - stable)
            'PTS' => 800000000,                     // Rp 800 Juta (Private University - smaller)
            'Hospital' => 1200000000,               // Rp 1.2 Miliar (Healthcare - high data needs)
            'Airport' => 3000000000,                // Rp 3 Miliar (Airport - very high connectivity)
            'Media' => 2500000000,                  // Rp 2.5 Miliar (Media - high bandwidth)
            'Airlines' => 2000000000,               // Rp 2 Miliar (Airlines - enterprise level)
            'OLO' => 1800000000,                    // Rp 1.8 Miliar (Telco competitor - medium)
            'Professional Service' => 1000000000,   // Rp 1 Miliar (Consulting - moderate)
            'Tourism and MICE' => 900000000,        // Rp 900 Juta (Hotel/Convention - moderate)
            'Corporate' => 5000000000,              // Rp 5 Miliar (Large Corporate)
            'Enterprise' => 4000000000,             // Rp 4 Miliar (Enterprise)
            'Government' => 3500000000,             // Rp 3.5 Miliar (Government)
            'default' => 1000000000,                // Rp 1 Miliar (fallback)
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
            // Get target based on company subsegment with variation
            $yearlyTarget = $subsegmentTargets[$company->subsegment] ?? $subsegmentTargets['default'];
            
            // Add company size variation (±20% to make companies different even in same subsegment)
            $companySizeVariation = 1 + (rand(-20, 20) / 100);
            $yearlyTarget = $yearlyTarget * $companySizeVariation;
            
            // Calculate monthly base target (without seasonal adjustment)
            $monthlyBaseTarget = $yearlyTarget / 12;
            
            // Calculate base value for breakdown items
            $totalBaseValue = array_sum(array_column($revenueStructure, 4));
            
            // Company performance profile (some companies consistently outperform, others underperform)
            $companyPerformanceProfile = match(true) {
                rand(1, 100) <= 15 => 'excellent',  // 15% excellent performers
                rand(1, 100) <= 50 => 'good',       // 35% good performers
                rand(1, 100) <= 80 => 'average',    // 30% average
                default => 'below_average'          // 20% below average
            };
            
            // Generate data for 2023 (full year), 2024 (full year), 2025 (Jan - Nov)
            $periods = [
                ['year' => 2023, 'months' => range(1, 12), 'base_achievement' => match($companyPerformanceProfile) {
                    'excellent' => 1.05 + (rand(0, 10) / 100),      // 105-115%
                    'good' => 0.90 + (rand(0, 10) / 100),           // 90-100%
                    'average' => 0.75 + (rand(0, 10) / 100),        // 75-85%
                    'below_average' => 0.60 + (rand(0, 10) / 100), // 60-70%
                }],
                ['year' => 2024, 'months' => range(1, 12), 'base_achievement' => match($companyPerformanceProfile) {
                    'excellent' => 1.08 + (rand(0, 12) / 100),      // 108-120% (improving)
                    'good' => 0.92 + (rand(0, 10) / 100),           // 92-102%
                    'average' => 0.78 + (rand(0, 12) / 100),        // 78-90%
                    'below_average' => 0.62 + (rand(0, 10) / 100), // 62-72%
                }],
                ['year' => 2025, 'months' => range(1, 12), 'base_achievement' => match($companyPerformanceProfile) {
                    'excellent' => 1.10 + (rand(0, 15) / 100),      // 110-125% (strong)
                    'good' => 0.95 + (rand(0, 10) / 100),           // 95-105%
                    'average' => 0.80 + (rand(0, 15) / 100),        // 80-95%
                    'below_average' => 0.65 + (rand(0, 12) / 100), // 65-77%
                }],
            ];

            foreach ($periods as $period) {
                $tahun = $period['year'];
                $yearBaseAchievement = $period['base_achievement'];
                
                foreach ($period['months'] as $bulan) {
                    // Create realistic monthly variation:
                    // - Lower revenue at start of year (Q1)
                    // - Gradual increase through year
                    // - Higher revenue at end of year (Q4)
                    // - Add monthly randomness for realism
                    $seasonalFactor = match(true) {
                        $bulan <= 3 => 0.85 + (rand(0, 10) / 100),   // Q1: 85-95% (slower start)
                        $bulan <= 6 => 0.92 + (rand(0, 10) / 100),   // Q2: 92-102% (building up)
                        $bulan <= 9 => 0.98 + (rand(0, 15) / 100),   // Q3: 98-113% (momentum)
                        default => 1.08 + (rand(0, 20) / 100)        // Q4: 108-128% (year-end push)
                    };
                    
                    // Regional growth factor (TREG1-7 have different growth rates)
                    $witelId = $company->idwitels ?? 0;
                    $regionalGrowthFactor = match(true) {
                        $witelId >= 2000 && $witelId < 3000 => 1.10, // TREG2 (Jakarta) - highest growth
                        $witelId >= 5000 && $witelId < 6000 => 1.08, // TREG5 (Bali) - tourism boost
                        $witelId >= 4000 && $witelId < 5000 => 1.05, // TREG4 (Jatim) - strong
                        $witelId >= 3000 && $witelId < 4000 => 1.03, // TREG3 (Jateng) - steady
                        $witelId >= 1000 && $witelId < 2000 => 1.02, // TREG1 (Sumatra) - moderate
                        default => 1.00                              // Others - baseline
                    };
                    
                    // Calculate yearly achievement with regional factor
                    $yearAchievement = $yearBaseAchievement * (($tahun >= 2024) ? $regionalGrowthFactor : 1.0);
                    
                    // Calculate monthly target with seasonal adjustment
                    $monthlyTargetWithSeason = $monthlyBaseTarget * $seasonalFactor;
                    $targetScaleFactor = $monthlyTargetWithSeason / $totalBaseValue;
                    
                    // Calculate monthly revenue (actual achievement)
                    $revenueScaleFactor = $monthlyTargetWithSeason * $yearAchievement / $totalBaseValue;
                    
                    // Add small monthly random noise (±5%) for realism
                    $monthlyRandomNoise = 1 + (rand(-5, 5) / 100);
                    
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
                        $revenue = $baseValue * $revenueScaleFactor * $productVariation * $monthlyRandomNoise;
                        
                        $makeLeaf($company, $g1Name, $g2Name, $g3Name, $g4Name, $revenue, $target, $tahun, $bulan);
                    }
                }
            }
            
            $this->command->info('  ✓ Created breakdown for: ' . $company->nama_perusahaan . ' (' . $companyPerformanceProfile . ' performer, 2023-2025)');
        }

        $this->command->info('✅ Revenue breakdown hierarchy created for all companies!');
    }
}
