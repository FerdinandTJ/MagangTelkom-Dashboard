<?php

namespace App\Imports;

use App\Models\LiniWaktu;
use App\Models\LiniWaktuTarget;
use App\Models\TargetAccountM;
use App\Models\AccountManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class NkiSheetImport implements ToModel, WithStartRow
{
    protected $year;
    protected $percentageData = [];
    protected $hasProcessedPercentages = false;
    protected $rowCount = 0;
    protected $errors = [];

    public function __construct(int $year)
    {
        $this->year = $year;
    }

    /**
     * Start from row 1 to capture percentage data
     */
    public function startRow(): int
    {
        return 1;
    }

    /**
     * Process each row
     */
    public function model(array $row)
    {
        // Process percentage data from rows 1-2 only once
        if (!$this->hasProcessedPercentages && $this->rowCount < 2) {
            $this->capturePercentageData($row, $this->rowCount + 1);
            $this->rowCount++;
            return null;
        }

        // Skip row 3 (header row)
        if ($this->rowCount == 2) {
            $this->rowCount++;
            return null;
        }

        // Process data rows starting from row 4
        if ($this->rowCount >= 3) {
            $this->processDataRow($row);
            $this->rowCount++;
        }

        return null;
    }

    /**
     * Capture percentage data from rows 1 and 2
     */
    protected function capturePercentageData(array $row, int $rowNumber)
    {
        if ($rowNumber == 1) {
            // Row 1: G (percentage_result), AK (percentage_proses)
            // Excel stores percentage as decimal (75% = 0.75), multiply by 100 to match ach_result format
            $this->percentageData['percentage_result'] = isset($row[6]) ? $row[6] * 100 : null; // Column G (index 6)
            $this->percentageData['percentage_proses'] = isset($row[36]) ? $row[36] * 100 : null; // Column AK (index 36)
        } elseif ($rowNumber == 2) {
            // Row 2: All other percentages
            // Excel stores percentage as decimal (20% = 0.20), multiply by 100 to match ach_* format
            $this->percentageData['percentage_revenue'] = isset($row[6]) ? $row[6] * 100 : null;   // Column G
            $this->percentageData['percentage_scaling'] = isset($row[9]) ? $row[9] * 100 : null;   // Column J
            $this->percentageData['percentage_datin'] = isset($row[12]) ? $row[12] * 100 : null;    // Column M
            $this->percentageData['percentage_hsi'] = isset($row[15]) ? $row[15] * 100 : null;      // Column P
            $this->percentageData['percentage_wireline'] = isset($row[18]) ? $row[18] * 100 : null; // Column S
            $this->percentageData['percentage_wifi'] = isset($row[21]) ? $row[21] * 100 : null;     // Column V
            $this->percentageData['percentage_cyc'] = isset($row[24]) ? $row[24] * 100 : null;      // Column Y
            $this->percentageData['percentage_cr'] = isset($row[27]) ? $row[27] * 100 : null;       // Column AB
            $this->percentageData['percentage_profit'] = isset($row[30]) ? $row[30] * 100 : null;   // Column AE
            $this->percentageData['percentage_customer'] = isset($row[33]) ? $row[33] * 100 : null; // Column AH
            $this->percentageData['percentage_maps'] = isset($row[36]) ? $row[36] * 100 : null;     // Column AK
            $this->percentageData['percentage_lop'] = isset($row[39]) ? $row[39] * 100 : null;      // Column AN
            $this->percentageData['percentage_capability'] = isset($row[42]) ? $row[42] * 100 : null; // Column AQ
            $this->percentageData['percentage_cc'] = isset($row[45]) ? $row[45] * 100 : null;       // Column AT

            $this->hasProcessedPercentages = true;
        }
    }

    /**
     * Process data rows (row 4 onwards)
     */
    protected function processDataRow(array $row)
    {
        try {
            // Column A: Quartal (Q1, Q2, Q3, Q4)
            $quartal = $row[0] ?? null;
            if (!$quartal) {
                return;
            }

            // Column B: NIK AM
            $nikAm = $row[1] ?? null;
            if (!$nikAm) {
                Log::warning("NKI Import: Missing NIK at row " . ($this->rowCount + 1));
                return;
            }

            // Validate AM exists
            $am = AccountManager::where('nik', $nikAm)->first();
            if (!$am) {
                $this->errors[] = "NIK {$nikAm} tidak ditemukan di database";
                return;
            }

            // Column C: Nama AM (validation)
            $namaAm = $row[2] ?? null;
            if ($namaAm && $am->nama !== $namaAm) {
                $this->errors[] = "Nama AM untuk NIK {$nikAm} tidak sesuai. Database: {$am->nama}, Excel: {$namaAm}";
            }

            // Column D: Segment -> update to account_manager_company
            $segment = $row[3] ?? null;
            if ($segment) {
                // Update segment in all account_manager_company records for this AM
                DB::table('account_manager_company')
                    ->where('nik_am', $nikAm)
                    ->update(['segment' => $segment]);
            }
            
            // Column E: Witel (validation)
            $witelName = $row[4] ?? null;
            if ($witelName && $am->witel) {
                if ($am->witel->witel !== $witelName) {
                    $this->errors[] = "Witel untuk NIK {$nikAm} tidak sesuai. Database: {$am->witel->witel}, Excel: {$witelName}";
                }
            }

            // Get lini_waktu record for this AM, quartal, and year
            $liniWaktu = LiniWaktu::where('nik_am', $nikAm)
                ->where('quartal', $quartal)
                ->where('tahun', $this->year)
                ->first();

            if (!$liniWaktu) {
                $this->errors[] = "Data lini_waktu untuk NIK {$nikAm}, {$quartal} {$this->year} tidak ditemukan";
                return;
            }

            // Update percentage data to lini_waktu (only once per quartal)
            $this->updatePercentageData($quartal);

            // Get ALL lini_waktu_target records for this NIK AM
            // Each row in Excel = data for 1 NIK AM, but may have multiple assignments (companies)
            // UPDATE: Now we update ALL records for this AM, not just the latest
            $liniWaktuTargets = LiniWaktuTarget::where('lini_waktu_id', $liniWaktu->id)->get();

            if ($liniWaktuTargets->isEmpty()) {
                $this->errors[] = "Data lini_waktu_target untuk NIK {$nikAm}, {$quartal} {$this->year} tidak ditemukan";
                return;
            }
            
            // For backward compatibility with old code, keep first record as main reference
            $liniWaktuTarget = $liniWaktuTargets->first();
            
            // Skip validation during import to allow data from Excel as-is
            $liniWaktuTarget->skipValidation = true;

            // Column F: Total Target Revenue (validation)
            $totalTargetRevenue = $row[5] ?? null;
            if ($totalTargetRevenue) {
                $actualTotal = LiniWaktuTarget::where('lini_waktu_id', $liniWaktu->id)
                    ->join('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
                    ->sum('target_account_m.t_revenue');
                
                if (abs($actualTotal - $totalTargetRevenue) > 0.01) {
                    Log::warning("Total Target Revenue mismatch for {$nikAm}: Expected {$totalTargetRevenue}, Got {$actualTotal}");
                }
            }

            // Column G: Total Realisasi Revenue (validation)
            $totalRealisasiRevenue = $row[6] ?? null;
            if ($totalRealisasiRevenue) {
                $actualTotal = LiniWaktuTarget::where('lini_waktu_id', $liniWaktu->id)
                    ->sum('r_revenue');
                
                if (abs($actualTotal - $totalRealisasiRevenue) > 0.01) {
                    Log::warning("Total Realisasi Revenue mismatch for {$nikAm}: Expected {$totalRealisasiRevenue}, Got {$actualTotal}");
                }
            }

            // Column H: Achievement Revenue Plan -> lini_waktu_target.ach_revenue_plan
            $achRevenuePlan = $row[7] ?? null;
            if ($achRevenuePlan !== null) {
                $liniWaktuTarget->ach_revenue_plan = $achRevenuePlan * 100;
            }

            // Column I: Total Target Scaling (validation)
            $totalTargetScaling = $row[8] ?? null;
            if ($totalTargetScaling) {
                $actualTotal = LiniWaktuTarget::where('lini_waktu_id', $liniWaktu->id)
                    ->join('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
                    ->sum('target_account_m.t_scalling');
                
                if (abs($actualTotal - $totalTargetScaling) > 0.01) {
                    Log::warning("Total Target Scaling mismatch for {$nikAm}: Expected {$totalTargetScaling}, Got {$actualTotal}");
                }
            }

            // Column J: Total Realisasi Scaling (validation)
            $totalRealisasiScaling = $row[9] ?? null;
            if ($totalRealisasiScaling) {
                $actualTotal = LiniWaktuTarget::where('lini_waktu_id', $liniWaktu->id)
                    ->sum('r_scalling');
                
                if (abs($actualTotal - $totalRealisasiScaling) > 0.01) {
                    Log::warning("Total Realisasi Scaling mismatch for {$nikAm}: Expected {$totalRealisasiScaling}, Got {$actualTotal}");
                }
            }

            // Column K: Achievement Scaling -> lini_waktu_target.ach_scaling
            $achScaling = $row[10] ?? null;
            if ($achScaling !== null) {
                $liniWaktuTarget->ach_scaling = $achScaling * 100;
            }

            // Column L: Target Datin -> target_account_m.t_datin
            $targetDatin = $row[11] ?? null;
            if ($targetDatin !== null) {
                $targetAccountM = TargetAccountM::find($liniWaktuTarget->target_id);
                if ($targetAccountM) {
                    $targetAccountM->t_datin = $targetDatin;
                    $targetAccountM->save();
                }
            }

            // Column M: Realisasi Datin -> lini_waktu_target.r_datin
            $realisasiDatin = $row[12] ?? null;
            if ($realisasiDatin !== null) {
                $liniWaktuTarget->r_datin = $realisasiDatin;
            }

            // Column N: Achievement Sales Datin -> lini_waktu_target.ach_sales_datin
            $achSalesDatin = $row[13] ?? null;
            if ($achSalesDatin !== null) {
                $liniWaktuTarget->ach_sales_datin = $achSalesDatin * 100;
            }

            // Get target_account_m for updating target fields
            $targetAccountM = TargetAccountM::find($liniWaktuTarget->target_id);

            // Column O: Target HSI -> target_account_m.t_hsi
            if (isset($row[14]) && $targetAccountM) {
                $targetAccountM->t_hsi = $row[14];
            }

            // Column P: Realisasi HSI -> lini_waktu_target.r_hsi
            if (isset($row[15])) {
                $liniWaktuTarget->r_hsi = $row[15];
            }

            // Column Q: Achievement HSI -> lini_waktu_target.ach_hsi
            if (isset($row[16])) {
                $liniWaktuTarget->ach_hsi = $row[16] * 100;
            }

            // Column R: Target Wireline -> target_account_m.t_wireline
            if (isset($row[17]) && $targetAccountM) {
                $targetAccountM->t_wireline = $row[17];
            }

            // Column S: Realisasi Wireline -> lini_waktu_target.r_wireline
            if (isset($row[18])) {
                $liniWaktuTarget->r_wireline = $row[18];
            }

            // Column T: Achievement Wireline -> lini_waktu_target.ach_wireline
            if (isset($row[19])) {
                $liniWaktuTarget->ach_wireline = $row[19] * 100;
            }

            // Column U: Target Wifi -> target_account_m.t_wifi
            if (isset($row[20]) && $targetAccountM) {
                $targetAccountM->t_wifi = $row[20];
            }

            // Column V: Realisasi Wifi -> lini_waktu_target.r_wifi
            if (isset($row[21])) {
                $liniWaktuTarget->r_wifi = $row[21];
            }

            // Column W: Achievement Wifi -> lini_waktu_target.ach_wifi
            if (isset($row[22])) {
                $liniWaktuTarget->ach_wifi = $row[22] * 100;
            }

            // Column X: Target CYC -> target_account_m.t_cyc
            if (isset($row[23]) && $targetAccountM) {
                $targetAccountM->t_cyc = $row[23];
            }

            // Column Y: Realisasi CYC -> lini_waktu_target.r_cyc
            if (isset($row[24])) {
                $liniWaktuTarget->r_cyc = $row[24];
            }

            // Column Z: Achievement CYC -> lini_waktu_target.ach_cyc
            if (isset($row[25])) {
                $liniWaktuTarget->ach_cyc = $row[25] * 100;
            }

            // Column AA: Target CR -> target_account_m.t_cr
            if (isset($row[26]) && $targetAccountM) {
                $targetAccountM->t_cr = $row[26];
            }

            // Column AB: Realisasi CR -> lini_waktu_target.r_cr
            if (isset($row[27])) {
                $liniWaktuTarget->r_cr = $row[27];
            }

            // Column AC: Achievement CR -> lini_waktu_target.ach_cr
            if (isset($row[28])) {
                $liniWaktuTarget->ach_cr = $row[28] * 100;
            }

            // Column AD: Target Profit -> target_account_m.t_profit
            if (isset($row[29]) && $targetAccountM) {
                $targetAccountM->t_profit = $row[29];
            }

            // Column AE: Realisasi Profit -> lini_waktu_target.r_profit
            if (isset($row[30])) {
                $liniWaktuTarget->r_profit = $row[30];
            }

            // Column AF: Achievement Profit -> lini_waktu_target.ach_profit
            if (isset($row[31])) {
                $liniWaktuTarget->ach_profit = $row[31] * 100;
            }

            // Column AG: Target NPS -> target_account_m.t_nps
            if (isset($row[32]) && $targetAccountM) {
                $targetAccountM->t_nps = $row[32];
            }

            // Column AH: Realisasi NPS -> lini_waktu_target.r_nps
            if (isset($row[33])) {
                $liniWaktuTarget->r_nps = $row[33];
            }

            // Column AI: Achievement NPS -> lini_waktu_target.ach_nps
            if (isset($row[34])) {
                $liniWaktuTarget->ach_nps = $row[34] * 100;
            }

            // Column AJ: Target MAPS -> target_account_m.t_maps
            if (isset($row[35]) && $targetAccountM) {
                $targetAccountM->t_maps = $row[35];
            }

            // Column AK: Realisasi MAPS -> lini_waktu_target.r_maps
            if (isset($row[36])) {
                $liniWaktuTarget->r_maps = $row[36];
            }

            // Column AL: Achievement MAPS -> lini_waktu_target.ach_maps
            if (isset($row[37])) {
                $liniWaktuTarget->ach_maps = $row[37] * 100;
            }

            // Column AM: Target LOP -> target_account_m.t_lop
            if (isset($row[38]) && $targetAccountM) {
                $targetAccountM->t_lop = $row[38];
            }

            // Column AN: Realisasi LOP -> lini_waktu_target.r_lop
            if (isset($row[39])) {
                $liniWaktuTarget->r_lop = $row[39];
            }

            // Column AO: Achievement LOP -> lini_waktu_target.ach_lop
            if (isset($row[40])) {
                $liniWaktuTarget->ach_lop = $row[40] * 100;
            }

            // Column AP: Target Capability -> target_account_m.t_capability
            if (isset($row[41]) && $targetAccountM) {
                $targetAccountM->t_capability = $row[41];
            }

            // Column AQ: Realisasi Capability -> lini_waktu_target.r_capability
            if (isset($row[42])) {
                $liniWaktuTarget->r_capability = $row[42];
            }

            // Column AR: Achievement Capability -> lini_waktu_target.ach_capability
            if (isset($row[43])) {
                $liniWaktuTarget->ach_capability = $row[43] * 100;
            }

            // Column AS: Target CC -> target_account_m.t_cc
            if (isset($row[44]) && $targetAccountM) {
                $targetAccountM->t_cc = $row[44];
            }

            // Column AT: Realisasi CC -> lini_waktu_target.r_cc
            if (isset($row[45])) {
                $liniWaktuTarget->r_cc = $row[45];
            }

            // Column AU: Achievement CC -> lini_waktu_target.ach_cc
            if (isset($row[46])) {
                $liniWaktuTarget->ach_cc = $row[46] * 100;
            }

            // Column AV: Achievement Result -> lini_waktu_target.ach_result
            if (isset($row[47])) {
                $liniWaktuTarget->ach_result = $row[47] * 100;
            }

            // Column AW: Achievement Proses -> lini_waktu_target.ach_proses
            if (isset($row[48])) {
                $liniWaktuTarget->ach_proses = $row[48] * 100;
            }

            // Column AX: NKI Adjustment -> lini_waktu_target.nki_adjustment
            // Excel stores percentage as decimal (100% = 1.0), multiply by 100 to get actual percentage
            if (isset($row[49])) {
                $liniWaktuTarget->nki_adjustment = $row[49] * 100;
            }

            // Save target_account_m if modified
            if ($targetAccountM) {
                $targetAccountM->save();
            }
            
            // Save ALL lini_waktu_target records for this AM (not just the first one)
            // This ensures all assignments get the same achievement data
            foreach ($liniWaktuTargets as $lwTarget) {
                // Copy achievement data from the main reference to all records
                $lwTarget->ach_revenue_plan = $liniWaktuTarget->ach_revenue_plan;
                $lwTarget->ach_scaling = $liniWaktuTarget->ach_scaling;
                $lwTarget->ach_sales_datin = $liniWaktuTarget->ach_sales_datin;
                $lwTarget->ach_hsi = $liniWaktuTarget->ach_hsi;
                $lwTarget->ach_wireline = $liniWaktuTarget->ach_wireline;
                $lwTarget->ach_wifi = $liniWaktuTarget->ach_wifi;
                $lwTarget->ach_cyc = $liniWaktuTarget->ach_cyc;
                $lwTarget->ach_cr = $liniWaktuTarget->ach_cr;
                $lwTarget->ach_profit = $liniWaktuTarget->ach_profit;
                $lwTarget->ach_nps = $liniWaktuTarget->ach_nps;
                $lwTarget->ach_maps = $liniWaktuTarget->ach_maps;
                $lwTarget->ach_lop = $liniWaktuTarget->ach_lop;
                $lwTarget->ach_capability = $liniWaktuTarget->ach_capability;
                $lwTarget->ach_cc = $liniWaktuTarget->ach_cc;
                $lwTarget->ach_result = $liniWaktuTarget->ach_result;
                $lwTarget->ach_proses = $liniWaktuTarget->ach_proses;
                $lwTarget->nki_adjustment = $liniWaktuTarget->nki_adjustment;
                
                // Skip validation during import to allow data from Excel as-is
                $lwTarget->skipValidation = true;
                
                // Note: We don't copy realisasi (r_*) fields because they are specific to each assignment/company
                $lwTarget->save();
            }

        } catch (\Exception $e) {
            Log::error("NKI Import Error at row " . ($this->rowCount + 1) . ": " . $e->getMessage());
            $this->errors[] = "Error at row " . ($this->rowCount + 1) . ": " . $e->getMessage();
        }
    }

    /**
     * Update percentage data to lini_waktu for specific quartal
     */
    protected function updatePercentageData(string $quartal)
    {
        static $updatedQuartals = [];

        // Only update once per quartal
        if (in_array($quartal, $updatedQuartals)) {
            return;
        }

        if (empty($this->percentageData)) {
            return;
        }

        // Update all lini_waktu records for this quartal and year
        LiniWaktu::where('quartal', $quartal)
            ->where('tahun', $this->year)
            ->update($this->percentageData);

        $updatedQuartals[] = $quartal;
        
        Log::info("Updated percentage data for {$quartal} {$this->year}");
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get row count
     */
    public function getRowCount(): int
    {
        return max(0, $this->rowCount - 3); // Exclude rows 1-3
    }
}
