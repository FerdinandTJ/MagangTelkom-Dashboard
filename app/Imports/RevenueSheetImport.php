<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\CompanyTarget;
use App\Models\Group1;
use App\Models\Group2;
use App\Models\Group3;
use App\Models\Group4;
use App\Models\Revenue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class RevenueSheetImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected int $year;
    protected ?int $specificMonth = null; // Specific month to import (null = all months)
    protected array $errors = [];
    protected int $successCount = 0;
    protected int $skipCount = 0;
    protected array $monthsImported = []; // Track which months have data
    protected array $companyTargets = []; // Track company targets: [nip_nas][month] = target_amount
    protected ?string $lastNipNas = null; // Track last valid NIP_NAS for merged cells
    protected ?string $lastCompanyName = null; // Track last valid company name
    protected ?string $lastSubSegment = null; // Track last valid subsegment
    protected ?string $lastSourceData = null; // Track last valid source data
    protected ?int $lastIdWitels = null; // Track last valid witel ID
    protected ?string $lastGroup1 = null; // Track last valid group1 for merged cells
    protected ?string $lastGroup2 = null; // Track last valid group2 for merged cells
    protected ?string $lastGroup3 = null; // Track last valid group3 for merged cells

    public function __construct(int $year, ?int $month = null)
    {
        $this->year = $year;
        $this->specificMonth = $month;
    }

    /**
     * Process the collection in chunks for memory efficiency
     */
    public function collection(Collection $rows)
    {
        // Validasi header/struktur file di awal
        if ($rows->isEmpty()) {
            throw new \Exception('File Excel kosong atau tidak memiliki data.');
        }
        
        // Get first row to check structure
        $firstRow = $rows->first();
        $rowData = $firstRow instanceof Collection ? $firstRow->toArray() : (array) $firstRow;
        
        // Log struktur file untuk debugging
        Log::info('Excel structure validation', [
            'year' => $this->year,
            'columns' => array_keys($rowData),
            'first_row_sample' => array_slice($rowData, 0, 5, true)
        ]);
        
        // Validasi kolom wajib ada
        $requiredColumns = [
            'nip_nas' => 'NIP_NAS',
            'standard_name' => 'STANDARD_NAME',
            'source_data' => 'SOURCE_DATA',
            'group1' => 'GROUP1',
            'group2' => 'GROUP2',
            'group3' => 'GROUP3',
            'group4' => 'GROUP4',
        ];
        
        $missingColumns = [];
        foreach ($requiredColumns as $key => $displayName) {
            if (!array_key_exists($key, $rowData)) {
                $missingColumns[] = $displayName;
            }
        }
        
        if (!empty($missingColumns)) {
            Log::error('Excel structure mismatch', [
                'missing_columns' => $missingColumns,
                'found_columns' => array_keys($rowData)
            ]);
            
            throw new \Exception(
                'Struktur Excel tidak sesuai dengan template revenue. ' . 
                'Kolom yang hilang: ' . implode(', ', $missingColumns) . 
                '. Pastikan file memiliki header: NIP_NAS, STANDARD_NAME, SOURCE_DATA, GROUP1-GROUP4, dan kolom bulan 1-12.'
            );
        }
        
        // Validasi kolom bulan (1-12) ada
        $monthColumns = [];
        for ($i = 1; $i <= 12; $i++) {
            if (!array_key_exists((string)$i, $rowData)) {
                $monthColumns[] = $i;
            }
        }
        
        if (!empty($monthColumns)) {
            Log::error('Excel month columns incomplete', [
                'missing_months' => $monthColumns,
                'found_columns' => array_keys($rowData)
            ]);
            
            throw new \Exception(
                'Kolom bulan tidak lengkap. Kolom yang hilang: ' . 
                implode(', ', $monthColumns) . 
                '. File harus memiliki 12 kolom bulan (1-12) untuk data revenue.'
            );
        }
        
        // Cek apakah ada data revenue yang valid (minimal 1 row dengan revenue > 0)
        $hasValidData = false;
        foreach ($rows as $row) {
            $rowData = $row instanceof Collection ? $row->toArray() : (array) $row;
            
            // Skip jika NIP_NAS kosong
            if (empty($rowData['nip_nas'])) {
                continue;
            }
            
            // Cek apakah ada revenue di bulan manapun
            for ($month = 1; $month <= 12; $month++) {
                $revenue = $this->parseRevenue($rowData[(string)$month] ?? 0);
                if ($revenue > 0) {
                    $hasValidData = true;
                    break 2; // keluar dari kedua loop
                }
            }
        }
        
        if (!$hasValidData) {
            Log::warning('No valid revenue data found in Excel');
            
            throw new \Exception(
                'File tidak memiliki data revenue yang valid. ' .
                'Pastikan setidaknya ada 1 baris dengan NIP_NAS dan nilai revenue > 0 pada salah satu bulan. ' .
                'File ini bukan template revenue import yang benar.'
            );
        }
        
        // Proses setiap baris
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because: 1 for header, 1 for 0-based index
            
            try {
                // Convert row to array if it's a Collection
                $rowData = $row instanceof Collection ? $row->toArray() : (array) $row;
                
                // Handle merged cells: use last valid NIP_NAS if current is empty
                if (empty($rowData['nip_nas'])) {
                    if ($this->lastNipNas === null) {
                        // No previous NIP_NAS to use, skip this row
                        $this->skipCount++;
                        continue;
                    }
                    // Use last valid NIP_NAS for merged cells
                    $rowData['nip_nas'] = $this->lastNipNas;
                } else {
                    // Update last valid NIP_NAS
                    $this->lastNipNas = trim($rowData['nip_nas']);
                }
                
                // Handle merged cells for company name
                if (empty($rowData['standard_name']) && $this->lastCompanyName !== null) {
                    $rowData['standard_name'] = $this->lastCompanyName;
                } else if (!empty($rowData['standard_name'])) {
                    $this->lastCompanyName = trim($rowData['standard_name']);
                }
                
                // Handle merged cells for subsegment
                if (empty($rowData['sub_segment']) && $this->lastSubSegment !== null) {
                    $rowData['sub_segment'] = $this->lastSubSegment;
                } else if (!empty($rowData['sub_segment'])) {
                    $this->lastSubSegment = trim($rowData['sub_segment']);
                }
                
                // Handle merged cells for source data
                if (empty($rowData['source_data']) && $this->lastSourceData !== null) {
                    $rowData['source_data'] = $this->lastSourceData;
                } else if (!empty($rowData['source_data'])) {
                    $this->lastSourceData = trim($rowData['source_data']);
                }
                
                // Handle merged cells for witel ID
                if (empty($rowData['witel_id']) && $this->lastIdWitels !== null) {
                    $rowData['witel_id'] = $this->lastIdWitels;
                } else if (!empty($rowData['witel_id'])) {
                    $this->lastIdWitels = (int) $rowData['witel_id'];
                }
                
                // Handle merged cells for GROUP1
                if (empty($rowData['group1']) && $this->lastGroup1 !== null) {
                    $rowData['group1'] = $this->lastGroup1;
                } else if (!empty($rowData['group1'])) {
                    $this->lastGroup1 = trim($rowData['group1']);
                }
                
                // Handle merged cells for GROUP2
                if (empty($rowData['group2']) && $this->lastGroup2 !== null) {
                    $rowData['group2'] = $this->lastGroup2;
                } else if (!empty($rowData['group2'])) {
                    $this->lastGroup2 = trim($rowData['group2']);
                }
                
                // Handle merged cells for GROUP3
                if (empty($rowData['group3']) && $this->lastGroup3 !== null) {
                    $rowData['group3'] = $this->lastGroup3;
                } else if (!empty($rowData['group3'])) {
                    $this->lastGroup3 = trim($rowData['group3']);
                }

                // Clean and validate the row data
                $cleanedRow = $this->cleanRowData($rowData);
                
                // Log first few rows for debugging
                if ($rowNumber <= 2) {
                    Log::info("Processing row {$rowNumber}", [
                        'raw_witel_id' => $rowData['witel_id'] ?? 'NOT_SET',
                        'raw_idwitels' => $rowData['idwitels'] ?? 'NOT_SET',
                        'nip_nas' => $cleanedRow['nip_nas'],
                        'company' => $cleanedRow['standard_name'],
                        'group1' => $cleanedRow['group1'],
                        'group2' => $cleanedRow['group2'],
                        'group3' => $cleanedRow['group3'],
                        'group4' => $cleanedRow['group4'],
                        'target_1' => $cleanedRow['target_1'] ?? 'NOT_SET',
                        'target_2' => $cleanedRow['target_2'] ?? 'NOT_SET',
                        'raw_t_1' => $rowData['t_1'] ?? 'NOT_SET',
                        'raw_t_2' => $rowData['t_2'] ?? 'NOT_SET',
                    ]);
                }
                
                // Validate required fields
                if (!$this->validateRow($cleanedRow, $rowNumber)) {
                    continue;
                }

                // Process the row
                $this->processRow($cleanedRow, $rowNumber);
                
                $this->successCount++;
                
            } catch (\Exception $e) {
                $rowData = $row instanceof Collection ? $row->toArray() : (array) $row;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'nip_nas' => $rowData['nip_nas'] ?? 'N/A',
                    'company' => $rowData['standard_name'] ?? 'N/A',
                    'error' => $e->getMessage()
                ];
                
                Log::error("Revenue import error at row {$rowNumber}", [
                    'error' => $e->getMessage(),
                    'data' => $rowData
                ]);
            }
        }
    }

    /**
     * Clean and normalize row data
     */
    protected function cleanRowData(array $row): array
    {
        // Support both "WITEL ID" (converted to witel_id) and "idwitels" header formats
        $witelId = null;
        
        // Check witel_id (from "WITEL ID" header)
        if (isset($row['witel_id'])) {
            $trimmed = trim((string)$row['witel_id']);
            if ($trimmed !== '' && $trimmed !== '0') {
                $witelId = (int) $trimmed;
            }
        }
        
        // Fallback to idwitels if witel_id not found
        if ($witelId === null && isset($row['idwitels'])) {
            $trimmed = trim((string)$row['idwitels']);
            if ($trimmed !== '' && $trimmed !== '0') {
                $witelId = (int) $trimmed;
            }
        }
        
        return [
            'sub_segment' => trim($row['sub_segment'] ?? ''),
            'nip_nas' => trim($row['nip_nas'] ?? ''),
            'standard_name' => trim($row['standard_name'] ?? ''),
            'source_data' => trim($row['source_data'] ?? ''),
            'idwitels' => $witelId, // Witel ID from Excel (supports both "WITEL ID" and "idwitels" headers)
            'group1' => trim($row['group1'] ?? ''),
            'group2' => trim($row['group2'] ?? ''),
            'group3' => trim($row['group3'] ?? ''),
            'group4' => trim($row['group4'] ?? ''),
            // Month columns (1-12)
            'month_1' => $this->parseRevenue($row['1'] ?? 0),
            'month_2' => $this->parseRevenue($row['2'] ?? 0),
            'month_3' => $this->parseRevenue($row['3'] ?? 0),
            'month_4' => $this->parseRevenue($row['4'] ?? 0),
            'month_5' => $this->parseRevenue($row['5'] ?? 0),
            'month_6' => $this->parseRevenue($row['6'] ?? 0),
            'month_7' => $this->parseRevenue($row['7'] ?? 0),
            'month_8' => $this->parseRevenue($row['8'] ?? 0),
            'month_9' => $this->parseRevenue($row['9'] ?? 0),
            'month_10' => $this->parseRevenue($row['10'] ?? 0),
            'month_11' => $this->parseRevenue($row['11'] ?? 0),
            'month_12' => $this->parseRevenue($row['12'] ?? 0),
            // Target columns (T-1 to T-12)
            'target_1' => $this->parseRevenue($row['t_1'] ?? 0),
            'target_2' => $this->parseRevenue($row['t_2'] ?? 0),
            'target_3' => $this->parseRevenue($row['t_3'] ?? 0),
            'target_4' => $this->parseRevenue($row['t_4'] ?? 0),
            'target_5' => $this->parseRevenue($row['t_5'] ?? 0),
            'target_6' => $this->parseRevenue($row['t_6'] ?? 0),
            'target_7' => $this->parseRevenue($row['t_7'] ?? 0),
            'target_8' => $this->parseRevenue($row['t_8'] ?? 0),
            'target_9' => $this->parseRevenue($row['t_9'] ?? 0),
            'target_10' => $this->parseRevenue($row['t_10'] ?? 0),
            'target_11' => $this->parseRevenue($row['t_11'] ?? 0),
            'target_12' => $this->parseRevenue($row['t_12'] ?? 0),
        ];
    }

    /**
     * Parse revenue value, handling empty strings and null values
     */
    protected function parseRevenue($value): float
    {
        if (empty($value) || $value === '(blank)') {
            return 0.0;
        }
        
        // Remove any currency symbols and whitespace
        $cleaned = preg_replace('/[^\d.-]/', '', $value);
        
        return (float) $cleaned;
    }

    /**
     * Validate row data
     */
    protected function validateRow(array $row, int $rowNumber): bool
    {
        $requiredFields = ['nip_nas', 'standard_name', 'source_data', 'group1', 'group2', 'group3', 'group4'];
        
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'nip_nas' => $row['nip_nas'] ?? 'N/A',
                    'company' => $row['standard_name'] ?? 'N/A',
                    'error' => "Kolom {$field} tidak boleh kosong"
                ];
                $this->skipCount++;
                return false;
            }
        }
        
        // Validate NIP_NAS length
        if (strlen($row['nip_nas']) > 25) {
            $this->errors[] = [
                'row' => $rowNumber,
                'nip_nas' => $row['nip_nas'],
                'company' => $row['standard_name'],
                'error' => 'NIP_NAS melebihi maksimal 25 karakter'
            ];
            $this->skipCount++;
            return false;
        }
        
        // Validate STANDARD_NAME length
        if (strlen($row['standard_name']) > 55) {
            $this->errors[] = [
                'row' => $rowNumber,
                'nip_nas' => $row['nip_nas'],
                'company' => $row['standard_name'],
                'error' => 'STANDARD_NAME melebihi maksimal 55 karakter'
            ];
            $this->skipCount++;
            return false;
        }
        
        // Validate SOURCE_DATA values
        $validSourceData = ['TIBS-NP', 'SISKA', 'NGTMA'];
        if (!in_array($row['source_data'], $validSourceData)) {
            $this->errors[] = [
                'row' => $rowNumber,
                'nip_nas' => $row['nip_nas'],
                'company' => $row['standard_name'],
                'error' => "SOURCE_DATA '{$row['source_data']}' tidak valid. Harus salah satu dari: " . implode(', ', $validSourceData)
            ];
            $this->skipCount++;
            return false;
        }
        
        // Validate GROUP fields length
        $groupFields = ['group1', 'group2', 'group3', 'group4'];
        foreach ($groupFields as $field) {
            if (strlen($row[$field]) > 45) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'nip_nas' => $row['nip_nas'],
                    'company' => $row['standard_name'],
                    'error' => "{$field} melebihi maksimal 45 karakter"
                ];
                $this->skipCount++;
                return false;
            }
        }
        
        // Validate ada minimal 1 bulan dengan revenue > 0
        $hasRevenue = false;
        for ($month = 1; $month <= 12; $month++) {
            if (isset($row["month_{$month}"]) && $row["month_{$month}"] > 0) {
                $hasRevenue = true;
                break;
            }
        }
        
        if (!$hasRevenue) {
            // Ini warning saja, tidak error - skip row tanpa revenue
            $this->skipCount++;
            return false;
        }
        
        return true;
    }

    /**
     * Process a single row: create/update company, groups, and revenues
     * Uses hierarchical lookup to find or create the complete path
     */
    protected function processRow(array $row, int $rowNumber): void
    {
        // 1. Create or update company with witel_id
        $companyData = [
            'nama_perusahaan' => $row['standard_name'],
            'subsegment' => $row['sub_segment'] ?: null,
            'source_data' => $row['source_data'],
        ];
        
        // Add idwitels if provided in Excel
        if (!empty($row['idwitels'])) {
            $companyData['idwitels'] = $row['idwitels'];
            Log::info("Setting idwitels for company", [
                'nip_nas' => $row['nip_nas'],
                'idwitels' => $row['idwitels']
            ]);
        } else {
            Log::warning("No idwitels found for company", [
                'nip_nas' => $row['nip_nas'],
                'row_data' => $row
            ]);
        }
        
        $company = Company::updateOrCreate(
            ['nip_nas' => $row['nip_nas']],
            $companyData
        );

        // 2. Find or create Group1 (linked to company)
        $group1 = Group1::firstOrCreate(
            [
                'company_id' => $company->nip_nas,
                'nama_group1' => $row['group1']
            ]
        );

        // 3. Find or create Group2 (linked to Group1)
        // Check if this exact Group1 already has this Group2 name
        $group2 = Group2::firstOrCreate(
            [
                'group1_id' => $group1->idGroup1,
                'nama_group2' => $row['group2']
            ]
        );

        // 4. Find or create Group3 (linked to Group2)
        // Check if this exact Group2 already has this Group3 name
        $group3 = Group3::firstOrCreate(
            [
                'group2_id' => $group2->idGroup2,
                'nama_group3' => $row['group3']
            ]
        );

        // 5. Find or create Group4 (product/service - linked to Group3)
        // Each Group4 represents a unique product/service under Group3
        $group4 = Group4::firstOrCreate(
            [
                'group3_id' => $group3->idGroup3,
                'nama_group4' => $row['group4']
            ]
        );

        // 6. Create or update revenues for specified month (or all 12 months if not specified)
        // When uploading per month, only process that specific month
        $monthsToProcess = $this->specificMonth ? [$this->specificMonth] : range(1, 12);
        
        foreach ($monthsToProcess as $month) {
            $revenueValue = $row["month_{$month}"];
            $targetValue = $row["target_{$month}"] ?? 0;
            
            // Only create/update if there's a revenue value (skip 0 or null)
            if ($revenueValue > 0) {
                Revenue::updateOrCreate(
                    [
                        'group4_id' => $group4->idGroup4,
                        'tahun' => $this->year,
                        'bulan' => $month
                    ],
                    [
                        'revenue_realisasi' => $revenueValue,
                        'revenue_target' => 0 // Target moved to company_targets table
                    ]
                );
                
                // Track that this month has data
                if (!in_array($month, $this->monthsImported)) {
                    $this->monthsImported[] = $month;
                }
                
                // Aggregate target per company per month
                if ($targetValue > 0) {
                    $nipNas = $company->nip_nas;
                    if (!isset($this->companyTargets[$nipNas])) {
                        $this->companyTargets[$nipNas] = [];
                    }
                    if (!isset($this->companyTargets[$nipNas][$month])) {
                        $this->companyTargets[$nipNas][$month] = 0;
                    }
                    $this->companyTargets[$nipNas][$month] += $targetValue;
                }
            }
        }
    }
    
    /**
     * Called when collection is finished to save company targets
     */
    public function __destruct()
    {
        // Save aggregated company targets
        foreach ($this->companyTargets as $nipNas => $months) {
            foreach ($months as $month => $targetAmount) {
                CompanyTarget::updateOrCreate(
                    [
                        'nip_nas' => $nipNas,
                        'tahun' => $this->year,
                        'bulan' => $month
                    ],
                    [
                        'target_revenue' => $targetAmount
                    ]
                );
            }
        }
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 100; // Process 100 rows at a time
    }

    /**
     * Get import statistics
     */
    public function getStats(): array
    {
        return [
            'success' => $this->successCount,
            'errors' => count($this->errors),
            'skipped' => $this->skipCount,
            'total' => $this->successCount + count($this->errors) + $this->skipCount,
            'months_imported' => $this->monthsImported
        ];
    }

    /**
     * Get months that were imported
     */
    public function getMonthsImported(): array
    {
        return $this->monthsImported;
    }

    /**
     * Get error details
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if import has errors
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
