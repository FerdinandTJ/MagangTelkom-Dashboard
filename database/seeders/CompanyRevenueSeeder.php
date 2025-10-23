<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Revenue;
use Illuminate\Database\Seeder;

class CompanyRevenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data perusahaan dummy
        $companies = [
            // Airport
            ['subsegment' => 'Airport', 'source_data' => 'TIBS-NP'],
            ['subsegment' => 'Airport', 'source_data' => 'SISKA'],
            ['subsegment' => 'Airport', 'source_data' => 'NGTMA'],
            ['subsegment' => 'Airport', 'source_data' => 'TIBS-NP'],
            
            // Hospital
            ['subsegment' => 'Hospital', 'source_data' => 'SISKA'],
            ['subsegment' => 'Hospital', 'source_data' => 'NGTMA'],
            ['subsegment' => 'Hospital', 'source_data' => 'TIBS-NP'],
            ['subsegment' => 'Hospital', 'source_data' => 'SISKA'],
            
            // PTN (Perguruan Tinggi Negeri)
            ['subsegment' => 'PTN', 'source_data' => 'NGTMA'],
            ['subsegment' => 'PTN', 'source_data' => 'TIBS-NP'],
            ['subsegment' => 'PTN', 'source_data' => 'SISKA'],
            ['subsegment' => 'PTN', 'source_data' => 'NGTMA'],
            
            // PTS (Perguruan Tinggi Swasta)
            ['subsegment' => 'PTS', 'source_data' => 'TIBS-NP'],
            ['subsegment' => 'PTS', 'source_data' => 'SISKA'],
            ['subsegment' => 'PTS', 'source_data' => 'NGTMA'],
            ['subsegment' => 'PTS', 'source_data' => 'TIBS-NP'],
            
            // Media
            ['subsegment' => 'Media', 'source_data' => 'SISKA'],
            ['subsegment' => 'Media', 'source_data' => 'NGTMA'],
            ['subsegment' => 'Media', 'source_data' => 'TIBS-NP'],
            ['subsegment' => 'Media', 'source_data' => 'SISKA'],
        ];

        $companyNames = [
            'Airport' => [
                'PT Angkasa Pura I',
                'PT Angkasa Pura II', 
                'Bandara Internasional Soekarno-Hatta',
                'Bandara Internasional Ngurah Rai'
            ],
            'Hospital' => [
                'RSUD Dr. Soetomo',
                'RS Cipto Mangunkusumo',
                'RS Persahabatan',
                'RS Fatmawati'
            ],
            'PTN' => [
                'Universitas Indonesia',
                'Institut Teknologi Bandung',
                'Universitas Gadjah Mada',
                'Institut Teknologi Sepuluh Nopember'
            ],
            'PTS' => [
                'Universitas Bina Nusantara',
                'Universitas Trisakti',
                'Universitas Tarumanagara',
                'Universitas Pelita Harapan'
            ],
            'Media' => [
                'PT Media Nusantara Citra',
                'PT Surya Citra Media',
                'PT Rajawali Citra Televisi',
                'PT Indosiar Visual Mandiri'
            ]
        ];

        $createdCompanies = [];
        
        foreach ($companies as $index => $companyData) {
            $subsegment = $companyData['subsegment'];
            $subsegmentIndex = array_search($subsegment, array_keys($companyNames));
            $companyIndex = $index % 4; // Cycle through 4 companies per subsegment
            
            $company = Company::create([
                'nip_nas' => sprintf('NIP%03d%04d', $subsegmentIndex + 1, $index + 1),
                'nama_perusahaan' => $companyNames[$subsegment][$companyIndex],
                'subsegment' => $subsegment,
                'source_data' => $companyData['source_data'],
                'status' => 'active'
            ]);
            
            $createdCompanies[] = $company;
        }

        // Generate revenue data untuk 2023, 2024, dan 2025 (sampai Oktober)
        foreach ($createdCompanies as $company) {
            // 2023 - Full year
            for ($month = 1; $month <= 12; $month++) {
                Revenue::create([
                    'company_id' => $company->id,
                    'tahun' => 2023,
                    'bulan' => $month,
                    'revenue' => $this->generateRevenue($company->subsegment, $month),
                ]);
            }

            // 2024 - Full year
            for ($month = 1; $month <= 12; $month++) {
                Revenue::create([
                    'company_id' => $company->id,
                    'tahun' => 2024,
                    'bulan' => $month,
                    'revenue' => $this->generateRevenue($company->subsegment, $month),
                ]);
            }

            // 2025 - Januari sampai Oktober
            for ($month = 1; $month <= 10; $month++) {
                Revenue::create([
                    'company_id' => $company->id,
                    'tahun' => 2025,
                    'bulan' => $month,
                    'revenue' => $this->generateRevenue($company->subsegment, $month),
                ]);
            }
        }
    }

    /**
     * Generate realistic revenue values based on subsegment
     */
    private function generateRevenue(string $subsegment, int $month): float
    {
        // Base revenue per subsegment (dalam miliar)
        $baseRevenues = [
            'Airport' => mt_rand(800, 1500), // 0.8 - 1.5 miliar
            'Hospital' => mt_rand(500, 1200), // 0.5 - 1.2 miliar
            'PTN' => mt_rand(300, 800),      // 0.3 - 0.8 miliar
            'PTS' => mt_rand(200, 600),      // 0.2 - 0.6 miliar
            'Media' => mt_rand(1000, 2500),  // 1.0 - 2.5 miliar
        ];

        $baseRevenue = $baseRevenues[$subsegment] ?? 500;

        // Seasonal adjustment (Q4 usually higher)
        $seasonalMultiplier = 1.0;
        if (in_array($month, [10, 11, 12])) {
            $seasonalMultiplier = mt_rand(110, 130) / 100; // 10-30% higher
        } elseif (in_array($month, [1, 2])) {
            $seasonalMultiplier = mt_rand(85, 95) / 100; // 5-15% lower
        }

        // Add some randomness (-20% to +20%)
        $randomMultiplier = mt_rand(80, 120) / 100;

        return $baseRevenue * $seasonalMultiplier * $randomMultiplier * 1000000; // Convert to actual rupiah
    }
}
