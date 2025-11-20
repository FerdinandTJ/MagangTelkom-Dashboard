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

        // 3. INSERT GROUP1-4 HIERARCHY (Revenue Breakdown Structure)
        // NOTE: Revenue realisasi now stored in group4 (leaf nodes), not in revenues table
        // This will create hierarchical breakdown: Companies -> Group1 -> Group2 -> Group3 -> Group4
        $this->command->info('🔄 Seeding Group1-4 hierarchical revenue breakdown...');
        $this->call(GroupBreakdownSeeder::class);

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
        // NOTE: PERUBAHAN PENTING - Targets sekarang dikaitkan dengan account_manager_company_id
        // Setiap assignment AM-Company akan punya target per year dan quarter
        // Struktur: account_manager_company_id → target (One-to-One per period)
        // t_revenue, t_scalling, t_lop, t_ngtma, t_sustain: decimal(15,2) - nilai absolut
        // t_cyc, t_cr, t_profit, t_maps: decimal(7,3) - persentase (0-100%)
        // t_datin, t_wifi: decimal(7,2) - nilai absolut
        // t_hsi, t_wireline, t_nps, t_capability, t_cc: decimal(5,2) - nilai absolut atau persentase
        
        $targets = [];
        
        // Get all AM-Company assignments dengan ID-nya
        $amCompanyAssignments = DB::table('account_manager_company')
            ->select('id', 'nik_am', 'nip_nas')
            ->orderBy('id')
            ->get();
        
        // Generate base targets untuk setiap assignment
        $baseRevenueRange = [40000000000, 50000000000, 60000000000, 70000000000, 80000000000, 90000000000, 100000000000];
        $years = [2024, 2025];
        $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
        
        foreach ($amCompanyAssignments as $index => $assignment) {
            // Base revenue varies by assignment index
            $baseRevenue = $baseRevenueRange[$index % count($baseRevenueRange)];
            $baseScaling = $baseRevenue * 0.5; // Scaling = 50% of revenue
            
            // Generate targets for each year and quarter for this AM-Company assignment
            foreach ($years as $year) {
                // Yearly increase factor (2025 targets are 12% higher than 2024)
                $yearFactor = ($year == 2024) ? 1.0 : 1.12;
                
                foreach ($quarters as $quartal) {
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
                        'account_manager_company_id' => $assignment->id, // NEW: FK to account_manager_company
                        't_revenue' => round($finalRevenue, 2),
                        't_scalling' => round($finalScaling, 2),
                        // NOTE: Adjusted values untuk decimal limits
                        't_datin' => round((10000.00 + ($index * 1200)) * $quarterFactor, 2),   // decimal(7,2) - max 99999.99
                        't_hsi' => round((100.00 + ($index * 15)) * $quarterFactor, 2),         // decimal(5,2) - max 999.99
                        't_wireline' => round((50.00 + ($index * 10)) * $quarterFactor, 2),     // decimal(5,2) - max 999.99
                        't_wifi' => round((15000.00 + ($index * 1500)) * $quarterFactor, 2),    // decimal(7,2) - max 99999.99
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
                }
            }
        }

        DB::table('target_account_m')->insert($targets);
        $this->command->info('✅ Targets inserted: ' . count($targets) . ' target records (per AM-Company x Year x Quarter)');

        // 6. INSERT LINI WAKTU - TARGET PIVOT (with realisasi dan achievement)
        // NOTE: PERUBAHAN LOGIKA - Sekarang mapping berdasarkan AM-Company + Year + Quarter
        // Struktur: 
        // - lini_waktu: berdasarkan nik_am + year + quarter (43 AMs × 2 years × 4 quarters = 344)
        // - target: berdasarkan account_manager_company_id (16 assignments × 2 years × 4 quarters = 128)
        // - Satu lini_waktu bisa punya multiple targets (karena 1 AM bisa handle multiple companies)
        
        $liniWaktuTarget = [];
        
        // Get all lini_waktu records ordered by AM, year, quarter
        $liniWaktuRecords = DB::table('lini_waktu')
            ->orderBy('nik_am')
            ->orderBy('tahun')
            ->orderBy('quartal')
            ->get();
        
        // Get all targets with their AM-Company info, ordered by assignment then period
        $targetRecords = DB::table('target_account_m as t')
            ->join('account_manager_company as amc', 't.account_manager_company_id', '=', 'amc.id')
            ->select('t.*', 'amc.nik_am', 'amc.nip_nas', 'amc.id as am_company_id')
            ->orderBy('amc.id')  // Order by AM-Company assignment
            ->orderBy('t.id')     // Then by target ID (which follows year-quarter sequence)
            ->get();
        
        // Create mapping: Group targets by AM NIK, Year, and Quarter
        // Structure: $targetsByAm[nik_am][year][quarter] = [target1, target2, ...]
        $targetsByAm = [];
        $years = [2024, 2025];
        $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
        
        // Group targets by AM-Company assignment
        $targetsByAssignment = [];
        foreach ($targetRecords as $target) {
            $assignmentId = $target->am_company_id;
            if (!isset($targetsByAssignment[$assignmentId])) {
                $targetsByAssignment[$assignmentId] = [];
            }
            $targetsByAssignment[$assignmentId][] = $target;
        }
        
        // Now map each assignment's targets to year-quarter periods
        foreach ($targetsByAssignment as $assignmentId => $assignmentTargets) {
            $targetIndex = 0;
            
            // Each assignment has 8 targets (2 years × 4 quarters)
            foreach ($years as $year) {
                foreach ($quarters as $quarter) {
                    if ($targetIndex < count($assignmentTargets)) {
                        $target = $assignmentTargets[$targetIndex];
                        $amNik = $target->nik_am;
                        
                        // Initialize nested arrays if not exists
                        if (!isset($targetsByAm[$amNik])) {
                            $targetsByAm[$amNik] = [];
                        }
                        if (!isset($targetsByAm[$amNik][$year])) {
                            $targetsByAm[$amNik][$year] = [];
                        }
                        if (!isset($targetsByAm[$amNik][$year][$quarter])) {
                            $targetsByAm[$amNik][$year][$quarter] = [];
                        }
                        
                        // Add target to this AM's period
                        $targetsByAm[$amNik][$year][$quarter][] = $target;
                        $targetIndex++;
                    }
                }
            }
        }
        
        // Now match lini_waktu with targets
        foreach ($liniWaktuRecords as $liniWaktu) {
            $amNik = $liniWaktu->nik_am;
            $year = $liniWaktu->tahun;
            $quarter = $liniWaktu->quartal;
            
            // Get all targets for this AM in this period
            if (isset($targetsByAm[$amNik][$year][$quarter])) {
                $targets = $targetsByAm[$amNik][$year][$quarter];
                
                // Create realisasi for each target (AM might have multiple companies)
                foreach ($targets as $target) {
                    // Calculate realisasi (achievement 85-110%)
                    $achievementRate = (rand(85, 110) / 100);
                    
                    $liniWaktuTarget[] = [
                        'lini_waktu_id' => $liniWaktu->id,
                        'target_id' => $target->id,
                        
                        // REALISASI - Nilai Absolut (dikalikan dengan achievement rate)
                        'r_revenue' => round($target->t_revenue * $achievementRate, 2),
                        'r_scalling' => round($target->t_scalling * $achievementRate, 2),
                        'r_datin' => round($target->t_datin * $achievementRate, 2),
                        'r_hsi' => round($target->t_hsi * $achievementRate, 2),
                        'r_wireline' => round($target->t_wireline * $achievementRate, 2),
                        'r_wifi' => round($target->t_wifi * $achievementRate, 2),
                        'r_nps' => round($target->t_nps * $achievementRate, 2),
                        'r_lop' => round($target->t_lop * $achievementRate, 2),
                        'r_capability' => round($target->t_capability * $achievementRate, 2),
                        'r_cc' => round($target->t_cc * $achievementRate, 2),
                        
                        // REALISASI - Persentase (target + variasi)
                        'r_cyc' => min(100, max(0, $target->t_cyc + rand(-5, 10))),
                        'r_cr' => min(100, max(0, $target->t_cr + rand(-5, 10))),
                        'r_profit' => min(100, max(0, $target->t_profit + rand(-5, 10))),
                        'r_maps' => min(100, max(0, $target->t_maps + rand(-5, 10))),
                        
                        // ACHIEVEMENT PERCENTAGE
                        'ach_revenue_plan' => round(($achievementRate * 100), 3),
                        'ach_scaling' => round(($achievementRate * 100), 3),
                        'ach_sales_datin' => round(($achievementRate * 100), 3),
                        'ach_hsi' => round(($achievementRate * 100), 3),
                        'ach_wireline' => round(($achievementRate * 100), 3),
                        'ach_wifi' => round(($achievementRate * 100), 3),
                        'ach_cyc' => round(rand(85, 110), 3),
                        'ach_cr' => round(rand(85, 110), 3),
                        'ach_profit' => round(rand(85, 110), 3),
                        'ach_nps' => round(($achievementRate * 100), 3),
                        'ach_maps' => round(rand(85, 110), 3),
                        'ach_lop' => round(($achievementRate * 100), 3),
                        'ach_capability' => round(($achievementRate * 100), 3),
                        'ach_cc' => round(($achievementRate * 100), 3),
                        
                        // OVERALL ACHIEVEMENT
                        'ach_result' => round(rand(85, 110), 3),
                        'ach_proses' => round(rand(85, 110), 3),
                        
                        // NKI ADJUSTMENT
                        'nki_adjustment' => round(rand(0, 10), 3),
                        
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('lini_waktu_target')->insert($liniWaktuTarget);
        $this->command->info('✅ Lini Waktu-Target pivot inserted: ' . count($liniWaktuTarget) . ' realisasi records');

        $this->command->info('');
        $this->command->info('🎉 SEMUA DUMMY DATA BERHASIL DIINSERT! 🎉');
        $this->command->info('');
    }
}