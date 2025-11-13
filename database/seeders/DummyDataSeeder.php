<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Insert dummy data untuk testing tanpa perlu seeder terpisah
     */
    public function run(): void
    {
        // 1. INSERT COMPANIES DATA (if empty)
        if (DB::table('companies')->count() == 0) {
            $companies = [
            // TREG1 - Sumatera (Aceh witel)
            ['nip_nas' => 'NIP001', 'nama_perusahaan' => 'PT Telkom Indonesia', 'subsegment' => 'PTN', 'source_data' => 'TIBS-NP', 'idwitels' => 1001],
            
            // TREG2 - Jakarta & Jabar
            ['nip_nas' => 'NIP002', 'nama_perusahaan' => 'RS Cipto Mangunkusumo', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP003', 'nama_perusahaan' => 'Universitas Indonesia', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 2002], // Jakarta Selatan
            ['nip_nas' => 'NIP004', 'nama_perusahaan' => 'Bandara Soekarno-Hatta', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 2007], // Tangerang
            ['nip_nas' => 'NIP007', 'nama_perusahaan' => 'Institut Teknologi Bandung', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 2008], // Bandung
            ['nip_nas' => 'NIP010', 'nama_perusahaan' => 'RS Hasan Sadikin Bandung', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 2008], // Bandung
            ['nip_nas' => 'NIP011', 'nama_perusahaan' => 'Universitas Bina Nusantara', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 2003], // Jakarta Barat
            ['nip_nas' => 'NIP013', 'nama_perusahaan' => 'Media Group Indonesia', 'subsegment' => 'Media', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP014', 'nama_perusahaan' => 'Trans Media Corporation', 'subsegment' => 'Media', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            
            // TREG3 - Jateng & DIY
            ['nip_nas' => 'NIP005', 'nama_perusahaan' => 'Universitas Gadjah Mada', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 3005], // Yogyakarta
            ['nip_nas' => 'NIP006', 'nama_perusahaan' => 'RS Sardjito Yogyakarta', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 3005], // Yogyakarta
            ['nip_nas' => 'NIP012', 'nama_perusahaan' => 'Universitas Trisakti', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 3001], // Semarang
            
            // TREG4 - Jawa Timur
            ['nip_nas' => 'NIP008', 'nama_perusahaan' => 'Universitas Airlangga', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 4001], // Surabaya
            ['nip_nas' => 'NIP009', 'nama_perusahaan' => 'Bandara Juanda Surabaya', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 4001], // Surabaya
            
            // TREG5 - Bali
            ['nip_nas' => 'NIP015', 'nama_perusahaan' => 'Bandara Ngurah Rai Bali', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 5001], // Bali
        ];

            foreach ($companies as $company) {
                DB::table('companies')->insert(array_merge($company, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->command->info('✅ Companies inserted: ' . count($companies) . ' companies with witel assignments');
        } else {
            $this->command->info('⚠️  Companies already exist, skipped.');
        }

        // 2. INSERT ACCOUNT MANAGER - COMPANY ASSIGNMENTS (if empty)
        if (DB::table('account_manager_company')->count() == 0) {
            $assignments = [
                // AM untuk TREG1 (Sumatera) - Aceh
                ['nik_am' => '810001', 'nip_nas' => 'NIP001', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                
                // AM untuk TREG2 (Jakarta) - Multiple companies
                ['nik_am' => '820001', 'nip_nas' => 'NIP002', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Hospital'],
                ['nik_am' => '820002', 'nip_nas' => 'NIP003', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                ['nik_am' => '820003', 'nip_nas' => 'NIP004', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Airport'],
                ['nik_am' => '820004', 'nip_nas' => 'NIP013', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Media'],
                ['nik_am' => '820005', 'nip_nas' => 'NIP014', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Media'],
                
                // AM untuk TREG3 (Jateng) - Semarang & Yogyakarta
                ['nik_am' => '830001', 'nip_nas' => 'NIP005', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                ['nik_am' => '830002', 'nip_nas' => 'NIP006', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Hospital'],
                ['nik_am' => '830003', 'nip_nas' => 'NIP011', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'PTS'],
                
                // AM untuk TREG2 (Bandung) - Shared company
                ['nik_am' => '820006', 'nip_nas' => 'NIP007', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                ['nik_am' => '820007', 'nip_nas' => 'NIP010', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Hospital'],
                ['nik_am' => '820007', 'nip_nas' => 'NIP011', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'PTS'], // Shared with TREG3
                
                // AM untuk TREG4 (Surabaya)
                ['nik_am' => '840001', 'nip_nas' => 'NIP008', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                ['nik_am' => '840002', 'nip_nas' => 'NIP009', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Airport'],
                ['nik_am' => '840003', 'nip_nas' => 'NIP012', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTS'],
                
                // AM untuk TREG5 (Bali)
                ['nik_am' => '850001', 'nip_nas' => 'NIP015', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Airport'],
            ];

            foreach ($assignments as $assignment) {
                DB::table('account_manager_company')->insert(array_merge($assignment, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->command->info('✅ AM-Company assignments inserted: ' . count($assignments) . ' assignments');
        } else {
            $this->command->info('⚠️  AM-Company assignments already exist, skipped.');
        }

        // 3. INSERT REVENUES DATA (2024 & 2025) - if empty
        // NOTE: Revenue stored in full Rupiah (decimal 16,2)
        if (DB::table('revenues')->count() == 0) {
            $revenues = [];
            $companyRevenues = [
            'NIP001' => [2024 => 5000000000, 2025 => 5500000000],  // 5-5.5 Billion Rp
            'NIP002' => [2024 => 3500000000, 2025 => 4000000000],  // 3.5-4 Billion Rp
            'NIP003' => [2024 => 8000000000, 2025 => 9000000000],  // 8-9 Billion Rp
            'NIP004' => [2024 => 12000000000, 2025 => 13000000000], // 12-13 Billion Rp
            'NIP005' => [2024 => 7000000000, 2025 => 7500000000],  // 7-7.5 Billion Rp
            'NIP006' => [2024 => 4000000000, 2025 => 4500000000],  // 4-4.5 Billion Rp
            'NIP007' => [2024 => 9000000000, 2025 => 9500000000],  // 9-9.5 Billion Rp
            'NIP008' => [2024 => 6500000000, 2025 => 7000000000],  // 6.5-7 Billion Rp
            'NIP009' => [2024 => 10000000000, 2025 => 11000000000], // 10-11 Billion Rp
            'NIP010' => [2024 => 3800000000, 2025 => 4200000000],  // 3.8-4.2 Billion Rp
            'NIP011' => [2024 => 2500000000, 2025 => 2800000000],  // 2.5-2.8 Billion Rp
            'NIP012' => [2024 => 2200000000, 2025 => 2500000000],  // 2.2-2.5 Billion Rp
            'NIP013' => [2024 => 15000000000, 2025 => 16000000000], // 15-16 Billion Rp
            'NIP014' => [2024 => 14000000000, 2025 => 15000000000], // 14-15 Billion Rp
            'NIP015' => [2024 => 11000000000, 2025 => 12000000000], // 11-12 Billion Rp
        ];

        foreach ($companyRevenues as $nipNas => $yearData) {
            foreach ($yearData as $year => $baseRevenue) {
                // Generate monthly data with slight variations
                for ($month = 1; $month <= 12; $month++) {
                    // Only generate up to current month for 2025
                    if ($year == 2025 && $month > 11) break;
                    
                    // Add 5-15% variation per month
                    $variation = rand(95, 115) / 100;
                    $monthlyRevenue = ($baseRevenue / 12) * $variation;
                    
                    $revenues[] = [
                        'nip_nas' => $nipNas,
                        'tahun' => $year,
                        'bulan' => $month,
                        'total_revenue' => round($monthlyRevenue, 2),
                        'target' => round($baseRevenue / 12, 2),
                        'note' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

            // Batch insert revenues
            foreach (array_chunk($revenues, 100) as $chunk) {
                DB::table('revenues')->insert($chunk);
            }
            $this->command->info('✅ Revenues inserted: ' . count($revenues) . ' revenue records');
        } else {
            $this->command->info('⚠️  Revenues already exist, skipped.');
        }

        // 4. INSERT LINI WAKTU (Quarterly periods for 2024-2025)
        $liniWaktu = [];
        $quarters = [
            'Q1' => ['bulan_awal' => '-01-01', 'bulan_akhir' => '-03-31'],
            'Q2' => ['bulan_awal' => '-04-01', 'bulan_akhir' => '-06-30'],
            'Q3' => ['bulan_awal' => '-07-01', 'bulan_akhir' => '-09-30'],
            'Q4' => ['bulan_awal' => '-10-01', 'bulan_akhir' => '-12-31'],
        ];

        // Get all Account Manager NIKs from database untuk generate lini_waktu
        // NOTE: Generate untuk semua AM agar semua region punya data
        $sampleAMs = DB::table('account_managers')->pluck('nik')->toArray();
        $years = [2024, 2025];

        foreach ($sampleAMs as $nikAm) {
            foreach ($years as $year) {
                foreach ($quarters as $quartal => $dates) {
                    $liniWaktu[] = [
                        'nik_am' => $nikAm,
                        'tahun' => $year,
                        'quartal' => $quartal,
                        'bulan_awal' => $year . $dates['bulan_awal'],
                        'bulan_akhir' => $year . $dates['bulan_akhir'],
                        
                        // Percentage/Weight untuk masing-masing KPI (total = 100%)
                        // NOTE: Bobot Result dan Process (Result 70% + Process 30%)
                        'percentage_result' => 70.000,        // Bobot result dalam total score (70%)
                        'percentage_proses' => 30.000,        // Bobot process dalam total score (30%)
                        
                        // Breakdown Result KPI (total 70% dari overall)
                        'percentage_revenue' => 20.000,       // Revenue: 20% dari total
                        'percentage_scaling' => 15.000,       // Scaling: 15% dari total
                        'percentage_datin' => 10.000,         // Datin: 10% dari total
                        'percentage_hsi' => 5.000,            // HSI: 5% dari total
                        'percentage_wireline' => 5.000,       // Wireline: 5% dari total
                        'percentage_wifi' => 5.000,           // WiFi: 5% dari total
                        'percentage_cyc' => 5.000,            // CYC: 5% dari total
                        'percentage_cr' => 3.000,             // CR: 3% dari total
                        'percentage_profit' => 2.000,         // Profit: 2% dari total
                        
                        // Breakdown Process KPI (total 30% dari overall)
                        'percentage_customer' => 15.000,      // Customer satisfaction: 15% dari total
                        'percentage_maps' => 7.000,           // MAPS: 7% dari total
                        'percentage_lop' => 5.000,            // LOP: 5% dari total
                        'percentage_capability' => 2.000,     // Capability: 2% dari total
                        'percentage_cc' => 1.000,             // CC: 1% dari total
                        
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('lini_waktu')->insert($liniWaktu);
        $this->command->info('✅ Lini Waktu inserted: ' . count($liniWaktu) . ' quarterly periods');

        // 5. INSERT TARGET ACCOUNT M (KPI Targets)
        // NOTE: Generate targets untuk setiap AM, tahun, dan quartal
        // Target revenue akan berbeda untuk setiap kombinasi AM + Year + Quarter
        // t_revenue, t_scalling, t_lop, t_ngtma, t_sustain: decimal(15,2) - nilai absolut
        // t_cyc, t_cr, t_profit, t_maps: decimal(7,3) - persentase (0-100%)
        // t_datin, t_wifi: decimal(7,2) - nilai absolut
        // t_hsi, t_wireline, t_nps, t_capability, t_cc: decimal(5,2) - nilai absolut atau persentase
        
        $targets = [];
        $targetIndex = 0;
        
        // Generate base targets untuk SEMUA AM
        // Formula: Base revenue 40M - 100M, varying by region
        $baseTargets = [];
        $baseRevenueRange = [40000000000, 50000000000, 60000000000, 70000000000, 80000000000, 90000000000, 100000000000];
        
        foreach ($sampleAMs as $index => $nikAm) {
            // Distribute revenue evenly across range
            $baseRevenue = $baseRevenueRange[$index % count($baseRevenueRange)];
            $baseTargets[$nikAm] = [
                'revenue' => $baseRevenue,
                'scaling' => $baseRevenue * 0.5, // Scaling = 50% of revenue
            ];
        }
        
        // Generate targets for each AM, year, and quarter
        foreach ($sampleAMs as $index => $nikAm) {
            $baseRevenue = $baseTargets[$nikAm]['revenue'];
            $baseScaling = $baseTargets[$nikAm]['scaling'];
            
            foreach ($years as $year) {
                // Yearly increase factor (2025 targets are 10-15% higher than 2024)
                $yearFactor = ($year == 2024) ? 1.0 : 1.12;
                
                foreach ($quarters as $quartal => $dates) {
                    // Quarterly variation (Q1: 90%, Q2: 100%, Q3: 105%, Q4: 110%)
                    $quarterFactor = match($quartal) {
                        'Q1' => 0.90,
                        'Q2' => 1.00,
                        'Q3' => 1.05,
                        'Q4' => 1.10,
                    };
                    
                    // Calculate final target with year and quarter factors
                    $finalRevenue = $baseRevenue * $yearFactor * $quarterFactor;
                    $finalScaling = $baseScaling * $yearFactor * $quarterFactor;
                    
                    $targets[] = [
                        't_revenue' => round($finalRevenue, 2),
                        't_scalling' => round($finalScaling, 2),
                        // NOTE: Adjusted values untuk decimal limits (max untuk 40 AMs dengan quarterFactor 1.10)
                        't_datin' => round((10000.00 + ($index * 1200)) * $quarterFactor, 2),   // decimal(7,2) - max 99999.99 (56880 at max)
                        't_hsi' => round((100.00 + ($index * 15)) * $quarterFactor, 2),         // decimal(5,2) - max 999.99 (748 at max)
                        't_wireline' => round((50.00 + ($index * 10)) * $quarterFactor, 2),     // decimal(5,2) - max 999.99 (484 at max)
                        't_wifi' => round((15000.00 + ($index * 1500)) * $quarterFactor, 2),    // decimal(7,2) - max 99999.99 (79200 at max)
                        't_cyc' => 85.000 + ($index * 2),
                        't_cr' => 90.000 + ($index * 1.5),
                        't_profit' => 75.000 + ($index * 2),
                        't_nps' => 75.00,
                        't_maps' => 80.000 + ($index * 1),
                        't_lop' => round(10000000000.00 * $quarterFactor, 2),
                        't_capability' => 90.00,
                        't_cc' => 88.00,
                        't_ngtma' => round(15000000000.00 * $quarterFactor, 2),
                        't_sustain' => round(12000000000.00 * $quarterFactor, 2),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $targetIndex++;
                }
            }
        }

        DB::table('target_account_m')->insert($targets);
        $this->command->info('✅ Targets inserted: ' . count($targets) . ' target records (per AM x Year x Quarter)');

        // 6. INSERT LINI WAKTU - TARGET PIVOT (with realisasi dan achievement)
        // NOTE: Sekarang ada 1:1 mapping antara lini_waktu dan target_account_m
        // Setiap kombinasi AM + Year + Quarter punya target sendiri
        $liniWaktuRecords = DB::table('lini_waktu')
            ->orderBy('nik_am')
            ->orderBy('tahun')
            ->orderBy('quartal')
            ->get();
        
        $targetRecords = DB::table('target_account_m')
            ->orderBy('id')
            ->get();
        
        $liniWaktuTarget = [];
        foreach ($liniWaktuRecords as $index => $liniWaktu) {
            // 1:1 mapping berdasarkan urutan insert
            $target = $targetRecords[$index];
            
            // Calculate realisasi (achievement 70-110%)
            // NOTE: Untuk field nilai absolut, kalikan dengan achievement rate
            // Untuk field persentase (t_cyc, t_cr, t_profit, t_maps), gunakan nilai langsung dengan variasi
            $achievementRate = (rand(85, 110) / 100);  // 85-110% achievement
            
            $liniWaktuTarget[] = [
                'lini_waktu_id' => $liniWaktu->id,
                'target_id' => $target->id,
                
                // REALISASI - Nilai Absolut (dikalikan dengan achievement rate)
                // NOTE: Perhatikan tipe data - beberapa kolom punya limit kecil
                'r_revenue' => round($target->t_revenue * $achievementRate, 2),     // decimal(15,2)
                'r_scalling' => round($target->t_scalling * $achievementRate, 2),   // decimal(15,2)
                'r_datin' => round($target->t_datin * $achievementRate, 2),         // decimal(7,2) - max 99999.99
                'r_hsi' => round($target->t_hsi * $achievementRate, 2),             // decimal(5,2) - max 999.99
                'r_wireline' => round($target->t_wireline * $achievementRate, 2),   // decimal(5,2) - max 999.99
                'r_wifi' => round($target->t_wifi * $achievementRate, 2),           // decimal(7,2) - max 99999.99
                'r_nps' => round($target->t_nps * $achievementRate, 2),             // decimal(5,2) - max 999.99
                'r_lop' => round($target->t_lop * $achievementRate, 2),             // decimal(15,2)
                'r_capability' => round($target->t_capability * $achievementRate, 2), // decimal(5,2) - max 999.99
                'r_cc' => round($target->t_cc * $achievementRate, 2),               // decimal(5,2) - max 999.99
                
                // REALISASI - Persentase (target + variasi -5 sampai +10)
                // NOTE: Field ini sekarang decimal(7,3) untuk persentase
                'r_cyc' => min(100, max(0, $target->t_cyc + rand(-5, 10))),      // Variasi ±5-10%
                'r_cr' => min(100, max(0, $target->t_cr + rand(-5, 10))),        // Variasi ±5-10%
                'r_profit' => min(100, max(0, $target->t_profit + rand(-5, 10))), // Variasi ±5-10%
                'r_maps' => min(100, max(0, $target->t_maps + rand(-5, 10))),    // Variasi ±5-10%
                
                // ACHIEVEMENT PERCENTAGE - Persentase pencapaian per KPI
                // NOTE: Calculated as (realisasi / target) * 100
                'ach_revenue_plan' => round(($achievementRate * 100), 3),     // Achievement revenue (%)
                'ach_scaling' => round(($achievementRate * 100), 3),          // Achievement scaling (%)
                'ach_sales_datin' => round(($achievementRate * 100), 3),      // Achievement datin (%)
                'ach_hsi' => round(($achievementRate * 100), 3),              // Achievement HSI (%)
                'ach_wireline' => round(($achievementRate * 100), 3),         // Achievement wireline (%)
                'ach_wifi' => round(($achievementRate * 100), 3),             // Achievement WiFi (%)
                'ach_cyc' => round(rand(85, 110), 3),                         // Achievement CYC (%)
                'ach_cr' => round(rand(85, 110), 3),                          // Achievement CR (%)
                'ach_profit' => round(rand(85, 110), 3),                      // Achievement profit (%)
                'ach_nps' => round(($achievementRate * 100), 3),              // Achievement NPS (%)
                'ach_maps' => round(rand(85, 110), 3),                        // Achievement MAPS (%)
                'ach_lop' => round(($achievementRate * 100), 3),              // Achievement LOP (%)
                'ach_capability' => round(($achievementRate * 100), 3),       // Achievement capability (%)
                'ach_cc' => round(($achievementRate * 100), 3),               // Achievement CC (%)
                
                // OVERALL ACHIEVEMENT
                'ach_result' => round(rand(85, 110), 3),                      // Achievement result overall (%)
                'ach_proses' => round(rand(85, 110), 3),                      // Achievement process overall (%)
                
                // NKI ADJUSTMENT
                'nki_adjustment' => round(rand(0, 10), 3),                    // NKI adjustment factor (0-10%)
                
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('lini_waktu_target')->insert($liniWaktuTarget);
        $this->command->info('✅ Lini Waktu-Target pivot inserted: ' . count($liniWaktuTarget) . ' realisasi records');

        $this->command->info('');
        $this->command->info('🎉 SEMUA DUMMY DATA BERHASIL DIINSERT! 🎉');
        $this->command->info('');
    }
}