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
            ['nip_nas' => 'NIP001', 'nama_perusahaan' => 'PT Telkom Indonesia', 'subsegment' => 'PTN', 'source_data' => 'TIBS-NP'],
            ['nip_nas' => 'NIP002', 'nama_perusahaan' => 'RS Cipto Mangunkusumo', 'subsegment' => 'Hospital', 'source_data' => 'SISKA'],
            ['nip_nas' => 'NIP003', 'nama_perusahaan' => 'Universitas Indonesia', 'subsegment' => 'PTN', 'source_data' => 'NGTMA'],
            ['nip_nas' => 'NIP004', 'nama_perusahaan' => 'Bandara Soekarno-Hatta', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP'],
            ['nip_nas' => 'NIP005', 'nama_perusahaan' => 'Universitas Gadjah Mada', 'subsegment' => 'PTN', 'source_data' => 'NGTMA'],
            ['nip_nas' => 'NIP006', 'nama_perusahaan' => 'RS Sardjito Yogyakarta', 'subsegment' => 'Hospital', 'source_data' => 'SISKA'],
            ['nip_nas' => 'NIP007', 'nama_perusahaan' => 'Institut Teknologi Bandung', 'subsegment' => 'PTN', 'source_data' => 'NGTMA'],
            ['nip_nas' => 'NIP008', 'nama_perusahaan' => 'Universitas Airlangga', 'subsegment' => 'PTN', 'source_data' => 'NGTMA'],
            ['nip_nas' => 'NIP009', 'nama_perusahaan' => 'Bandara Juanda Surabaya', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP'],
            ['nip_nas' => 'NIP010', 'nama_perusahaan' => 'RS Hasan Sadikin Bandung', 'subsegment' => 'Hospital', 'source_data' => 'SISKA'],
            ['nip_nas' => 'NIP011', 'nama_perusahaan' => 'Universitas Bina Nusantara', 'subsegment' => 'PTS', 'source_data' => 'NGTMA'],
            ['nip_nas' => 'NIP012', 'nama_perusahaan' => 'Universitas Trisakti', 'subsegment' => 'PTS', 'source_data' => 'NGTMA'],
            ['nip_nas' => 'NIP013', 'nama_perusahaan' => 'Media Group Indonesia', 'subsegment' => 'Media', 'source_data' => 'TIBS-NP'],
            ['nip_nas' => 'NIP014', 'nama_perusahaan' => 'Trans Media Corporation', 'subsegment' => 'Media', 'source_data' => 'TIBS-NP'],
            ['nip_nas' => 'NIP015', 'nama_perusahaan' => 'Bandara Ngurah Rai Bali', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP'],
        ];

        foreach ($companies as $company) {
            DB::table('companies')->insert(array_merge($company, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        $this->command->info('✅ Companies inserted: ' . count($companies) . ' companies');

        // 2. INSERT ACCOUNT MANAGER - COMPANY ASSIGNMENTS
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

        // 3. INSERT REVENUES DATA (2024 & 2025)
        // NOTE: Revenue stored in full Rupiah (decimal 16,2)
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

        // 4. INSERT LINI WAKTU (Quarterly periods for 2024-2025)
        $liniWaktu = [];
        $quarters = [
            'Q1' => ['bulan_awal' => '-01-01', 'bulan_akhir' => '-03-31'],
            'Q2' => ['bulan_awal' => '-04-01', 'bulan_akhir' => '-06-30'],
            'Q3' => ['bulan_awal' => '-07-01', 'bulan_akhir' => '-09-30'],
            'Q4' => ['bulan_awal' => '-10-01', 'bulan_akhir' => '-12-31'],
        ];

        $sampleAMs = ['810001', '820001', '820002', '830001', '840001', '850001'];
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
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('lini_waktu')->insert($liniWaktu);
        $this->command->info('✅ Lini Waktu inserted: ' . count($liniWaktu) . ' quarterly periods');

        // 5. INSERT TARGET ACCOUNT M (KPI Targets)
        // NOTE: Adjusted to fit decimal constraints
        // t_revenue, t_scalling, t_lop, t_ngtma, t_sustain: decimal(15,2) - up to 9,999,999,999,999.99
        // t_cyc, t_cr: decimal(10,2) - up to 99,999,999.99
        // t_datin, t_wifi: decimal(7,2) - up to 99,999.99
        // t_hsi, t_wireline, t_profit, t_nps, t_maps, t_capability, t_cc: decimal(5,2) - up to 999.99
        $targets = [];
        foreach ($sampleAMs as $index => $nikAm) {
            $targets[] = [
                't_revenue' => 50000000000.00 + ($index * 10000000000),     // 50-100B
                't_scalling' => 25000000000.00 + ($index * 5000000000),    // 25-50B
                't_datin' => 15000.00 + ($index * 5000),                   // 15k-40k
                't_hsi' => 150.00 + ($index * 50),                         // 150-400
                't_wireline' => 80.00 + ($index * 20),                     // 80-180
                't_wifi' => 50000.00 + ($index * 8000),                    // 50k-90k (max 99,999.99)
                't_cyc' => 5000000.00 + ($index * 500000),                 // 5M-7.5M
                't_cr' => 3000000.00 + ($index * 500000),                  // 3M-5.5M
                't_profit' => 85.00,                                       // 85%
                't_nps' => 75.00,                                          // 75%
                't_maps' => 80.00,                                         // 80%
                't_lop' => 10000000000.00,                                 // 10B
                't_capability' => 90.00,                                   // 90%
                't_cc' => 88.00,                                           // 88%
                't_ngtma' => 15000000000.00,                               // 15B
                't_sustain' => 12000000000.00,                             // 12B
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('target_account_m')->insert($targets);
        $this->command->info('✅ Targets inserted: ' . count($targets) . ' target records');

        // 6. INSERT LINI WAKTU - TARGET PIVOT (with realisasi)
        $liniWaktuIds = DB::table('lini_waktu')->pluck('id')->toArray();
        $targetIds = DB::table('target_account_m')->pluck('id')->toArray();
        
        $liniWaktuTarget = [];
        foreach ($liniWaktuIds as $index => $liniWaktuId) {
            $targetId = $targetIds[$index % count($targetIds)];
            
            // Get target data
            $target = DB::table('target_account_m')->where('id', $targetId)->first();
            
            // Calculate realisasi (achievement 70-110%)
            $achievement = rand(70, 110) / 100;
            
            $liniWaktuTarget[] = [
                'lini_waktu_id' => $liniWaktuId,
                'target_id' => $targetId,
                'r_revenue' => $target->t_revenue * $achievement,
                'r_scalling' => $target->t_scalling * $achievement,
                'r_datin' => $target->t_datin * $achievement,
                'r_hsi' => $target->t_hsi * $achievement,
                'r_wireline' => $target->t_wireline * $achievement,
                'r_wifi' => $target->t_wifi * $achievement,
                'r_cyc' => $target->t_cyc * $achievement,
                'r_cr' => $target->t_cr * $achievement,
                'r_profit' => $target->t_profit * $achievement,
                'r_nps' => $target->t_nps * $achievement,
                'r_maps' => $target->t_maps * $achievement,
                'r_cc' => $target->t_cc * $achievement,
                'r_sustain' => $target->t_sustain * $achievement,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('lini_waktu_target')->insert($liniWaktuTarget);
        $this->command->info('✅ Lini Waktu-Target pivot inserted: ' . count($liniWaktuTarget) . ' realisasi records');

        $this->command->info('');
        $this->command->info('🎉 SEMUA DUMMY DATA BERHASIL DIINSERT! 🎉');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('- Companies: ' . count($companies));
        $this->command->info('- AM-Company Assignments: ' . count($assignments));
        $this->command->info('- Revenues: ' . count($revenues));
        $this->command->info('- Lini Waktu: ' . count($liniWaktu));
        $this->command->info('- Targets: ' . count($targets));
        $this->command->info('- Realisasi: ' . count($liniWaktuTarget));
    }
    }
}