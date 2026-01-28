<?php

namespace App\Imports;

use App\Models\Lop;
use App\Models\Bulan;
use App\Models\LopBulan;
use App\Models\Hari;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LopBulanSheetImport implements ToCollection
{
    private $lastIdLop = null; // Untuk menyimpan ID_LOP terakhir

    public function __construct($sheetName = 'List LOP 2026')
    {
        // Constructor tidak perlu extract tahun lagi, akan diambil dari sheet
    }

    public function collection(Collection $rows)
    {
        Log::info("LopBulanSheetImport: Starting collection processing", ['row_count' => $rows->count()]);
        
        try {
            if ($rows->count() < 4) {
                Log::warning('Sheet List LOP kosong atau tidak valid', ['row_count' => $rows->count()]);
                throw new \Exception('Format file tidak valid: Sheet "List LOP 2026" harus memiliki minimal 4 baris data.');
            }

            // Baris 1 (index 0): Header dengan metadata
            // Kolom B (index 1) = Bulan
            // Kolom C (index 2) = Tahun
            $headerRow = $rows[0];
            $bulanValue = $headerRow[1] ?? null; // Kolom B
            $tahunValue = $headerRow[2] ?? null; // Kolom C

            Log::info("LopBulanSheetImport: Header row values", [
                'bulan_raw' => $bulanValue,
                'tahun_raw' => $tahunValue
            ]);

            $bulan = (int) $bulanValue;
            $tahun = (int) $tahunValue;

            if (!$bulan || $bulan < 1 || $bulan > 12) {
                Log::error("Bulan tidak valid: {$bulanValue}");
                throw new \Exception("Format file tidak valid: Bulan harus berada di antara 1-12. Ditemukan: {$bulanValue}");
            }

            if (!$tahun || $tahun < 2000) {
                Log::error("Tahun tidak valid: {$tahunValue}");
                throw new \Exception("Format file tidak valid: Tahun harus >= 2000. Ditemukan: {$tahunValue}");
            }

            Log::info("Processing List LOP untuk bulan {$bulan}/{$tahun}");

            // Get or create bulan record
            $bulanRecord = Bulan::firstOrCreate(
                ['bulan' => $bulan, 'tahun' => $tahun],
                [
                    't_sustain' => 0,
                    'kebutuhan_scaling' => 0,
                    'r_scaling' => 0,
                    'sodomoro' => 0,
                    'adjustment' => 0,
                    'target_cm' => 0,
                    'target_ytd' => 0,
                    'rev_cm' => 0,
                    'rev_ytd' => 0,
                    'ach_cm' => 0,
                    'ach_ytd' => 0,
                ]
            );

            // DELETE data lama untuk bulan ini sebelum insert (REPLACE behavior)
            $deletedCount = LopBulan::where('bulan_id', $bulanRecord->id)->delete();
            Log::info("Deleted old LOP data for bulan {$bulan}/{$tahun}: {$deletedCount} records");

            // RESET progress_scaling di tabel hari untuk bulan ini (REPLACE behavior)
            $resetCount = Hari::where('bulan_id', $bulanRecord->id)->update(['progress_scaling' => 0]);
            Log::info("Reset progress_scaling for bulan {$bulan}/{$tahun}: {$resetCount} hari records");

            // Baris 2 (index 1): Header kolom tabel (ID LOP, AM, TREG, dll) - SKIP
            // Loop dari baris 3 (index 2) hingga seterusnya untuk data
            $processedCount = 0;
            
            for ($i = 2; $i < $rows->count(); $i++) {
                $row = $rows[$i];

                // Kolom A (index 0) = ID_LOP
                $idLop = trim($row[0] ?? '');

                // Jika kolom A kosong, gunakan ID_LOP terakhir
                if (empty($idLop)) {
                    if ($this->lastIdLop) {
                        $idLop = $this->lastIdLop;
                        Log::info("Baris " . ($i + 1) . ": ID_LOP kosong, menggunakan ID_LOP terakhir: {$idLop}");
                    } else {
                        // Skip row jika tidak ada ID_LOP sama sekali
                        Log::warning("Baris " . ($i + 1) . ": Skip karena ID_LOP kosong dan belum ada ID_LOP sebelumnya");
                        continue;
                    }
                } else {
                    // Update ID_LOP terakhir
                    $this->lastIdLop = $idLop;
                    Log::info("Baris " . ($i + 1) . ": ID_LOP baru ditemukan: {$idLop}");
                }

                // Cek apakah kolom B-G ada isinya
                $hasData = false;
                for ($col = 1; $col <= 6; $col++) {
                    if (!empty(trim($row[$col] ?? ''))) {
                        $hasData = true;
                        break;
                    }
                }

                if (!$hasData) {
                    Log::debug("Baris " . ($i + 1) . ": Skip karena kolom B-G kosong");
                    continue;
                }

                // Create or update LOP (hanya sekali per ID_LOP unik)
                $lop = Lop::firstOrCreate(
                    ['ID_LOP' => $idLop],
                    ['timestamp' => now()]
                );

                // Kolom B (index 1) = AM
                // Kolom C (index 2) = ID_Region
                // Kolom D (index 3) = Nama_CC
                // Kolom E (index 4) = Project
                // Kolom F (index 5) = Scaling
                // Kolom G (index 6) = Progress

                $am = trim($row[1] ?? '');
                $idRegion = $this->parseRegionId(trim($row[2] ?? ''));
                $namaCC = trim($row[3] ?? '');
                $project = trim($row[4] ?? '');
                $scaling = $this->parseNumber($row[5] ?? 0);
                $progress = trim($row[6] ?? '');
                $tanggalClosed = $row[7] ?? null; // Kolom H

                // Create or update LOP-Bulan pivot
                // PENTING: Setiap baris adalah entry BARU di lop_bulan
                // Karena bisa ada multiple project untuk satu LOP di bulan yang sama
                $lopBulan = LopBulan::create([
                    'ID_LOP' => $idLop,
                    'bulan_id' => $bulanRecord->id,
                    'AM' => $am,
                    'ID_Region' => $idRegion,
                    'Nama_CC' => $namaCC,
                    'Project' => $project,
                    'Scaling' => $scaling,
                    'Progress' => $progress,
                ]);

                // Update progress_scaling jika progress = 'closed' dan ada tanggal closed
                // Validasi: trim dan lowercase untuk handle berbagai variasi (Closed, CLOSED, closed, dll)
                $progressNormalized = strtolower(trim($progress));
                if ($progressNormalized === 'closed' && $tanggalClosed) {
                    $parsedDate = $this->parseTanggalClosed($tanggalClosed, $bulan, $tahun);
                    if ($parsedDate) {
                        // Get or create hari record
                        $hari = Hari::firstOrCreate(
                            [
                                'bulan_id' => $bulanRecord->id,
                                'tanggal' => $parsedDate,
                                'tahun' => $tahun  // Tambahkan tahun di sini
                            ],
                            [
                                'sustain' => 0,
                                'progress_scaling' => 0,
                                'sodomoro' => 0,
                                'adjustment' => 0,
                                'progress_revenue' => 0,
                                'ach_revenue' => 0,
                            ]
                        );

                        // Increment progress_scaling dengan nilai scaling
                        $hari->increment('progress_scaling', $scaling);
                        
                        Log::info("✓ Updated progress_scaling for tanggal {$parsedDate}: +{$scaling} (Project: {$project})");
                    }
                }

                $processedCount++;
                Log::info("✓ Imported LOP {$idLop} - Project: {$project} - Baris " . ($i + 1) . " (Entry #{$processedCount})");
            }
            
            Log::info("List LOP import completed for bulan {$bulan}/{$tahun}. Total entries: {$processedCount}");
        } catch (\Exception $e) {
            Log::error('Error importing LOP sheet: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse region ID dari value
     */
    private function parseRegionId($value)
    {
        if (empty($value)) {
            return null;
        }

        // Jika sudah berupa angka, return langsung
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Extract angka dari string (misal: "2 HQ" -> 2, "TREG 3" -> 3)
        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
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

    /**
     * Parse tanggal closed dari berbagai format Excel
     * Returns tanggal (day of month) atau null jika invalid
     */
    private function parseTanggalClosed($value, $bulan, $tahun)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Case 1: Excel serial number (numeric)
            if (is_numeric($value)) {
                // Jika angka kecil (1-31), anggap sebagai tanggal langsung
                if ($value >= 1 && $value <= 31) {
                    return (int) $value;
                }
                
                // Excel serial date (days since 1900-01-01)
                $unixTimestamp = ($value - 25569) * 86400;
                $date = \Carbon\Carbon::createFromTimestamp($unixTimestamp);
                
                // Validasi bulan/tahun sesuai
                if ($date->month == $bulan && $date->year == $tahun) {
                    return $date->day;
                }
            }
            
            // Case 2: String date format
            $dateString = trim($value);
            
            // Try parsing Y-m-d format
            if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $dateString, $matches)) {
                $y = (int) $matches[1];
                $m = (int) $matches[2];
                $d = (int) $matches[3];
                
                if ($y == $tahun && $m == $bulan && $d >= 1 && $d <= 31) {
                    return $d;
                }
            }
            
            // Try parsing d/m/Y or d-m-Y format
            if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $dateString, $matches)) {
                $d = (int) $matches[1];
                $m = (int) $matches[2];
                $y = (int) $matches[3];
                
                if ($y == $tahun && $m == $bulan && $d >= 1 && $d <= 31) {
                    return $d;
                }
            }
            
            // Try Carbon parsing as fallback
            $date = \Carbon\Carbon::parse($dateString);
            if ($date->month == $bulan && $date->year == $tahun) {
                return $date->day;
            }
            
        } catch (\Exception $e) {
            Log::warning("Failed to parse tanggal closed: {$value}", ['error' => $e->getMessage()]);
        }

        return null;
    }
}
