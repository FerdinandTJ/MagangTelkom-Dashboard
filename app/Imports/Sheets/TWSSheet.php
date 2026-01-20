<?php

namespace App\Imports\Sheets;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\AccountManager;
use App\Models\Company;
use App\Models\AccountManagerCompany;
use App\Models\TargetAccountM;
use App\Models\LiniWaktu;
use App\Models\LiniWaktuTarget;
use App\Models\Witel;
use App\Models\Region;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TWSSheet implements ToModel, WithStartRow
{
    protected $quarter;
    protected $year;
    protected $sharedData;
    protected $conflicts;
    protected $rowCount;
    protected $currentRow = 0;

    public function __construct($quarter, $year, &$sharedData, &$conflicts, &$rowCount)
    {
        $this->quarter = $quarter;
        $this->year = $year;
        $this->sharedData = &$sharedData;
        $this->conflicts = &$conflicts;
        $this->rowCount = &$rowCount;
    }

    public function startRow(): int
    {
        return 3; // Start from row 3 (row 1 has Q/Year, row 2 is header)
    }

    /**
     * Clean currency format from Excel
     * Handles: "Rp 1.000.000", "1.000.000", "1,000,000", "Rp1.000.000,50" etc.
     */
    private function cleanCurrencyFormat($value)
    {
        if (is_null($value) || $value === '' || $value === 0) {
            return 0;
        }

        // If already numeric, return as is
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Convert to string
        $value = (string) $value;
        $original = $value; // Keep for logging

        // Remove currency symbols (Rp, $, etc) and spaces
        $value = preg_replace('/[Rp\$\s]/i', '', $value);
        
        // Count dots and commas to determine format
        $dotCount = substr_count($value, '.');
        $commaCount = substr_count($value, ',');
        
        if ($dotCount > 0 && $commaCount > 0) {
            // Both present - determine which is decimal separator
            $lastDotPos = strrpos($value, '.');
            $lastCommaPos = strrpos($value, ',');
            
            if ($lastDotPos > $lastCommaPos) {
                // Format: 1.234.567,89 or 1,234,567.89
                // Dot is decimal, comma is thousand
                $value = str_replace(',', '', $value); // Remove thousands
            } else {
                // Format: 1,234,567.89 or 1.234.567,89
                // Comma is decimal, dot is thousand
                $value = str_replace('.', '', $value); // Remove thousands
                $value = str_replace(',', '.', $value); // Replace decimal
            }
        } elseif ($dotCount > 1) {
            // Format: 1.234.567 (dots as thousand separator)
            $value = str_replace('.', '', $value);
        } elseif ($commaCount > 1) {
            // Format: 1,234,567 (commas as thousand separator)
            $value = str_replace(',', '', $value);
        } elseif ($commaCount == 1) {
            // Format: 1234,56 or 1234567,89 (comma as decimal)
            $value = str_replace(',', '.', $value);
        }
        // else: single dot or no separator - keep as is

        // Convert to float
        $result = (float) $value;
        
        // Log if parsing seems wrong (result is 0 but original had content)
        if ($result == 0 && strlen($original) > 0 && $original !== '0') {
            Log::warning("Currency parsing may have failed: '{$original}' -> {$result}");
        }

        return $result;
    }

    public function model(array $row)
    {
        $this->currentRow++;

        try {
            // Extract all column values (A to X = index 0-23)
            $data = [
                'nik' => $row[0] ?? null,                    // A
                'nama' => $row[1] ?? null,                   // B
                'posisi' => $row[2] ?? null,                 // C
                'region_id' => $row[3] ?? null,              // D
                'id_witels' => $row[4] ?? null,              // E
                'no_gsm' => $row[7] ?? null,                 // H
                'pembagian' => $row[8] ?? null,              // I
                'nip_nas' => $row[9] ?? null,                // J
                'nama_perusahaan' => $row[10] ?? null,       // K
                'company_witel_id' => $row[13] ?? null,      // N
                'company_region_id' => $row[14] ?? null,     // O
                'proporsi' => $row[15] ?? 0,                 // P
                't_revenue' => $this->cleanCurrencyFormat($row[16] ?? 0),    // Q
                't_sustain' => $this->cleanCurrencyFormat($row[17] ?? 0),    // R
                't_scalling' => $this->cleanCurrencyFormat($row[18] ?? 0),   // S
                't_ngtma' => $this->cleanCurrencyFormat($row[19] ?? 0),      // T
                'r_revenue' => $this->cleanCurrencyFormat($row[20] ?? 0),    // U
                'r_sustain' => $this->cleanCurrencyFormat($row[21] ?? 0),    // V
                'r_scalling' => $this->cleanCurrencyFormat($row[22] ?? 0),   // W
                'r_ngtma' => $this->cleanCurrencyFormat($row[23] ?? 0),      // X
            ];

            // Skip empty rows
            if (empty($data['nik']) || empty($data['nip_nas'])) {
                return null;
            }

            // Validate required fields
            if (empty($data['id_witels'])) {
                Log::error("Row {$this->currentRow}: Missing Witel ID (Column E). Row data: " . json_encode($row));
                throw new \Exception("Missing Witel ID in column E");
            }

            DB::beginTransaction();

            try {
                // 1. Process Account Manager
                $accountManager = $this->processAccountManager($data);
                
                // 2. Process Company
                $company = $this->processCompany($data);
                
                // 3. Process Account Manager - Company Relationship
                $amCompany = $this->processAMCompanyRelationship($accountManager, $company, $data);
                
                // 4. Process Target
                $target = $this->processTarget($amCompany, $data);
                
                // 5. Process Lini Waktu & Realisasi
                $this->processLiniWaktuAndRealisasi($accountManager, $target, $data);

                DB::commit();
                $this->rowCount++;

                return null;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error processing TWS row {$this->currentRow}: " . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error("Error in TWS Sheet row {$this->currentRow}: " . $e->getMessage());
            return null;
        }
    }

    protected function processAccountManager($data)
    {
        // Validate witel exists and belongs to correct region
        $witel = Witel::where('idwitels', $data['id_witels'])->first();
        
        if (!$witel) {
            throw new \Exception("Witel ID {$data['id_witels']} not found");
        }

        if ($witel->region_id != $data['region_id']) {
            throw new \Exception("Witel {$data['id_witels']} does not belong to Region {$data['region_id']}");
        }

        $accountManager = AccountManager::updateOrCreate(
            ['nik' => $data['nik']],
            [
                'nama' => $data['nama'],
                'posisi' => $data['posisi'],
                'idwitels' => $data['id_witels'],
                'no_gsm' => $data['no_gsm'],
            ]
        );

        Log::info("Account Manager processed: NIK={$accountManager->nik}, Name={$accountManager->nama}");

        return $accountManager;
    }

    protected function processCompany($data)
    {
        // STRICT MODE: Company harus sudah ada di database (dari Revenue Dashboard)
        $company = Company::where('nip_nas', $data['nip_nas'])->first();
        
        if (!$company) {
            // Throw error dengan pesan khusus jika company belum ada
            throw new \Exception("Upload Data Revenue Dashboard First - NIP NAS {$data['nip_nas']} not found in database");
        }

        // Validate company witel exists
        $companyWitel = Witel::where('idwitels', $data['company_witel_id'])->first();
        
        if (!$companyWitel) {
            throw new \Exception("Company Witel ID {$data['company_witel_id']} not found");
        }

        // Pengecekan data consistency (hanya log, tidak update)
        if ($company->nama_perusahaan !== $data['nama_perusahaan']) {
            Log::warning("Company name mismatch: DB={$company->nama_perusahaan}, Sheet={$data['nama_perusahaan']}");
        }
        
        if ($company->idwitels != $data['company_witel_id']) {
            Log::warning("Company witel mismatch: DB={$company->idwitels}, Sheet={$data['company_witel_id']}");
        }

        // Cek region mismatch melalui witel
        if ($companyWitel->region_id != $data['company_region_id']) {
            Log::warning("Company Witel {$data['company_witel_id']} region mismatch: Witel Region={$companyWitel->region_id}, Sheet Region={$data['company_region_id']}");
        }

        Log::info("Company validated: NIP={$company->nip_nas}, Name={$company->nama_perusahaan}");

        return $company;
    }

    protected function processAMCompanyRelationship($accountManager, $company, $data)
    {
        // Cek apakah hubungan sudah ada
        $amCompany = AccountManagerCompany::where('nik_am', $accountManager->nik)
            ->where('nip_nas', $company->nip_nas)
            ->first();

        if ($amCompany) {
            // Jika sudah ada, hanya lakukan pengecekan (tidak update)
            if ($amCompany->pembagian !== strtoupper($data['pembagian'])) {
                Log::warning("AM-Company pembagian mismatch: DB={$amCompany->pembagian}, Sheet={$data['pembagian']}");
            }
            
            if ($amCompany->proporsi != $data['proporsi']) {
                Log::warning("AM-Company proporsi mismatch: DB={$amCompany->proporsi}, Sheet={$data['proporsi']}");
            }
            
            Log::info("AM-Company relationship exists: AM={$accountManager->nik}, Company={$company->nip_nas}, ID={$amCompany->id}");
        } else {
            // Jika belum ada, tambahkan data baru
            $amCompany = AccountManagerCompany::create([
                'nik_am' => $accountManager->nik,
                'nip_nas' => $company->nip_nas,
                'pembagian' => strtoupper($data['pembagian']), // SINGLE or SHARE
                'proporsi' => $data['proporsi'],
            ]);
            
            Log::info("AM-Company relationship created: AM={$accountManager->nik}, Company={$company->nip_nas}, Proporsi={$data['proporsi']}%");
        }

        return $amCompany;
    }

    protected function processTarget($amCompany, $data)
    {
        $target = TargetAccountM::updateOrCreate(
            ['account_manager_company_id' => $amCompany->id],
            [
                't_revenue' => $data['t_revenue'],
                't_sustain' => $data['t_sustain'],
                't_scalling' => $data['t_scalling'],
                't_ngtma' => $data['t_ngtma'],
            ]
        );

        Log::info("Target processed: ID={$target->id}, Revenue={$data['t_revenue']}");

        return $target;
    }

    protected function processLiniWaktuAndRealisasi($accountManager, $target, $data)
    {
        // Calculate quarter dates
        $quarterDates = [
            1 => ['start' => $this->year . '-01-01', 'end' => $this->year . '-03-31'],
            2 => ['start' => $this->year . '-04-01', 'end' => $this->year . '-06-30'],
            3 => ['start' => $this->year . '-07-01', 'end' => $this->year . '-09-30'],
            4 => ['start' => $this->year . '-10-01', 'end' => $this->year . '-12-31'],
        ];
        
        $dates = $quarterDates[$this->quarter];
        
        // Find or create Lini Waktu with default percentages to pass validation
        $liniWaktu = LiniWaktu::withoutEvents(function () use ($accountManager, $dates) {
            return LiniWaktu::firstOrCreate(
                [
                    'nik_am' => $accountManager->nik,
                    'quartal' => 'Q' . $this->quarter,
                    'tahun' => $this->year,
                ],
                [
                    'bulan_awal' => $dates['start'],
                    'bulan_akhir' => $dates['end'],
                    // Set default percentages to satisfy validation (100% total)
                    'percentage_result' => 100,
                    'percentage_proses' => 0,
                    'percentage_revenue' => 100,
                    'percentage_scaling' => 0,
                    'percentage_datin' => 0,
                    'percentage_hsi' => 0,
                    'percentage_wireline' => 0,
                    'percentage_wifi' => 0,
                    'percentage_cyc' => 0,
                    'percentage_cr' => 0,
                    'percentage_profit' => 0,
                    'percentage_customer' => 0,
                    'percentage_maps' => 0,
                    'percentage_lop' => 0,
                    'percentage_capability' => 0,
                    'percentage_cc' => 0,
                ]
            );
        });

        // Create or update Lini Waktu Target (pivot table with realisasi)
        LiniWaktuTarget::updateOrCreate(
            [
                'lini_waktu_id' => $liniWaktu->id,
                'target_id' => $target->id,
            ],
            [
                'r_revenue' => $data['r_revenue'],
                'r_sustain' => $data['r_sustain'],
                'r_scalling' => $data['r_scalling'],
                'r_ngtma' => $data['r_ngtma'],
            ]
        );

        Log::info("Lini Waktu & Realisasi processed: Q{$this->quarter} {$this->year}, Realisasi Revenue={$data['r_revenue']}");
    }
}
