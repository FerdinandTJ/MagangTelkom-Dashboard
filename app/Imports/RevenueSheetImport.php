<?php

namespace App\Imports;

use App\Models\Company;
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
    protected array $errors = [];
    protected int $successCount = 0;
    protected int $skipCount = 0;
    protected array $monthsImported = []; // Track which months have data
    protected ?string $lastNipNas = null; // Track last valid NIP_NAS for merged cells
    protected ?string $lastCompanyName = null; // Track last valid company name
    protected ?string $lastSubSegment = null; // Track last valid subsegment
    protected ?string $lastSourceData = null; // Track last valid source data

    public function __construct(int $year)
    {
        $this->year = $year;
    }

    /**
     * Process the collection in chunks for memory efficiency
     */
    public function collection(Collection $rows)
    {
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

                // Clean and validate the row data
                $cleanedRow = $this->cleanRowData($rowData);
                
                // Log first few rows for debugging
                if ($rowNumber <= 5) {
                    Log::info("Processing row {$rowNumber}", [
                        'nip_nas' => $cleanedRow['nip_nas'],
                        'company' => $cleanedRow['standard_name'],
                        'group1' => $cleanedRow['group1'],
                        'group2' => $cleanedRow['group2'],
                        'group3' => $cleanedRow['group3'],
                        'group4' => $cleanedRow['group4'],
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
        return [
            'sub_segment' => trim($row['sub_segment'] ?? ''),
            'nip_nas' => trim($row['nip_nas'] ?? ''),
            'standard_name' => trim($row['standard_name'] ?? ''),
            'source_data' => trim($row['source_data'] ?? ''),
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
                    'nip_nas' => $row['nip_nas'],
                    'company' => $row['standard_name'],
                    'error' => "Missing required field: {$field}"
                ];
                return false;
            }
        }
        
        // Validate NIP_NAS length
        if (strlen($row['nip_nas']) > 25) {
            $this->errors[] = [
                'row' => $rowNumber,
                'nip_nas' => $row['nip_nas'],
                'company' => $row['standard_name'],
                'error' => 'NIP_NAS exceeds maximum length of 25 characters'
            ];
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
        // 1. Create or update company
        $company = Company::updateOrCreate(
            ['nip_nas' => $row['nip_nas']],
            [
                'nama_perusahaan' => $row['standard_name'],
                'subsegment' => $row['sub_segment'] ?: null,
                'source_data' => $row['source_data'],
            ]
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

        // 6. Create or update revenues for all 12 months
        for ($month = 1; $month <= 12; $month++) {
            $revenueValue = $row["month_{$month}"];
            
            // Only create/update if there's a value (skip 0 or null)
            if ($revenueValue > 0) {
                Revenue::updateOrCreate(
                    [
                        'group4_id' => $group4->idGroup4,
                        'tahun' => $this->year,
                        'bulan' => $month
                    ],
                    [
                        'revenue_realisasi' => $revenueValue,
                        'revenue_target' => 0 // Can be updated later with target data
                    ]
                );
                
                // Track that this month has data
                if (!in_array($month, $this->monthsImported)) {
                    $this->monthsImported[] = $month;
                }
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
