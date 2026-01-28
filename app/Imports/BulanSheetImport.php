<?php

namespace App\Imports;

use App\Models\Bulan;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BulanSheetImport implements ToCollection
{
    private $year;

    public function __construct($sheetName = 'Target 2026')
    {
        // Extract tahun dari nama sheet (Target 2026 -> 2026)
        preg_match('/\d{4}/', $sheetName, $matches);
        $this->year = !empty($matches) ? (int)$matches[0] : now()->year;
    }

    public function collection(Collection $rows)
    {
        try {
            // Baris 1 kolom B-M berisi bulan (1-12)
            // Baris 2-12 adalah data metrics
            
            if ($rows->count() < 12) {
                Log::warning('Sheet Target bulan kosong atau tidak valid');
                throw new \Exception('Format file tidak valid: Sheet "Target 2026" harus memiliki minimal 12 baris data.');
            }

            // Ambil header bulan dari baris 1, kolom B-M (index 1-12)
            $headerRow = $rows->first();
            
            // Map row index ke field name sesuai spesifikasi
            $metricMap = [
                1 => 't_sustain',           // Row 2 (index 1): TOTAL SUSTAIN
                2 => 'kebutuhan_scaling',   // Row 3 (index 2): KEBUTUHAN SCALING
                3 => 'r_scaling',           // Row 4 (index 3): REALISASI SCALING
                4 => 'sodomoro',            // Row 5 (index 4): SODOMORO
                5 => 'adjustment',          // Row 6 (index 5): ADJUSTMENT
                6 => 'target_cm',           // Row 7 (index 6): TARGET CM
                7 => 'target_ytd',          // Row 8 (index 7): TARGET YTD
                8 => 'rev_cm',              // Row 9 (index 8): REV CM
                9 => 'rev_ytd',             // Row 10 (index 9): REV YTD
                10 => 'ach_cm',             // Row 11 (index 10): ACH CM
                11 => 'ach_ytd',            // Row 12 (index 11): ACH YTD
            ];

            // Loop kolom B-M (index 1-12) untuk bulan 1-12
            for ($colIndex = 1; $colIndex <= 12; $colIndex++) {
                // Ambil nilai bulan dari baris 1
                $bulanValue = $headerRow[$colIndex] ?? null;
                $bulan = $this->parseBulan($bulanValue);
                
                if (!$bulan || $bulan < 1 || $bulan > 12) {
                    Log::warning("Skip kolom {$colIndex}: bulan tidak valid ({$bulanValue})");
                    continue;
                }

                // Kumpulkan data untuk bulan ini dari semua row
                $data = [];
                foreach ($metricMap as $rowIndex => $fieldName) {
                    if (isset($rows[$rowIndex])) {
                        // Ambil nilai dari koordinat [rowIndex][colIndex]
                        $value = $rows[$rowIndex][$colIndex] ?? null;
                        $data[$fieldName] = $this->parseNumber($value);
                    }
                }

                // Simpan ke database (auto-generate ID jika belum ada)
                Bulan::updateOrCreate(
                    [
                        'bulan' => $bulan,
                        'tahun' => $this->year,
                    ],
                    $data
                );

                Log::info("Imported bulan {$bulan}/{$this->year} dari kolom " . chr(65 + $colIndex), $data);
            }
        } catch (\Exception $e) {
            Log::error('Error importing bulan sheet: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse bulan value dari string atau angka
     */
    private function parseBulan($value)
    {
        if (is_numeric($value)) {
            $bulan = (int) $value;
            return ($bulan >= 1 && $bulan <= 12) ? $bulan : null;
        }

        // Jika berupa nama bulan (Indonesia atau English)
        $months = [
            'januari' => 1, 'january' => 1,
            'februari' => 2, 'february' => 2,
            'maret' => 3, 'march' => 3,
            'april' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'june' => 6,
            'juli' => 7, 'july' => 7,
            'agustus' => 8, 'august' => 8,
            'september' => 9,
            'oktober' => 10, 'october' => 10,
            'november' => 11,
            'desember' => 12, 'december' => 12,
        ];

        $lowerValue = strtolower(trim($value));
        return $months[$lowerValue] ?? null;
    }

    /**
     * Parse number dari format Indonesia atau standar
     */
    private function parseNumber($value)
    {
        if ($value === null || $value === '' || $value === '-') {
            return 0;
        }

        // Remove currency symbols and spaces
        $value = preg_replace('/[^\d,.-]/', '', $value);
        
        // Convert Indonesian format (1.000.000,00) to standard (1000000.00)
        if (substr_count($value, '.') > 1 || (strpos($value, '.') !== false && strpos($value, ',') !== false)) {
            // Indonesian format
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }
}
