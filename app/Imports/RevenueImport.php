<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Illuminate\Support\Facades\Log;

class RevenueImport implements WithMultipleSheets, SkipsUnknownSheets
{
    protected array $sheetImporters = [];
    protected ?int $specifiedYear = null;
    protected ?int $specifiedMonth = null;

    /**
     * Constructor - can specify a year and month for single month import
     */
    public function __construct(?int $year = null, ?int $month = null)
    {
        $this->specifiedYear = $year;
        $this->specifiedMonth = $month;
    }

    /**
     * Define which sheets to process
     * Automatically detects sheets named "Rev YYYY" or uses specified year
     */
    public function sheets(): array
    {
        // If a specific year is provided, only import that sheet
        if ($this->specifiedYear) {
            $sheetImporter = new RevenueSheetImport($this->specifiedYear, $this->specifiedMonth);
            $this->sheetImporters[$this->specifiedYear] = $sheetImporter;
            
            return [
                "Rev {$this->specifiedYear}" => $sheetImporter
            ];
        }

        // Otherwise, try to import sheets for multiple years (dynamic range)
        $sheets = [];
        $currentYear = (int) date('Y');
        $startYear = 2020; // Earliest year in historical data
        $years = range($startYear, $currentYear + 5); // Current year + 5 years future
        
        foreach ($years as $year) {
            $sheetImporter = new RevenueSheetImport($year, null);
            $this->sheetImporters[$year] = $sheetImporter;
            $sheets["Rev {$year}"] = $sheetImporter;
        }
        
        return $sheets;
    }

    /**
     * Called when a sheet is not found - this is normal, we try all years
     */
    public function onUnknownSheet($sheetName)
    {
        // Silently skip - this is expected for years that don't exist
        Log::debug("Skipping unknown sheet: {$sheetName}");
    }

    /**
     * Get combined statistics from all imported sheets
     */
    public function getStats(): array
    {
        $totalSuccess = 0;
        $totalErrors = 0;
        $totalSkipped = 0;
        $totalRecords = 0;
        $yearStats = [];

        foreach ($this->sheetImporters as $year => $importer) {
            $stats = $importer->getStats();
            
            // Only include years that had data AND months_imported
            // This prevents updating revenue_uploads for years that don't exist in the file
            if ($stats['total'] > 0 && !empty($stats['months_imported'])) {
                $yearStats[$year] = $stats;
                $totalSuccess += $stats['success'];
                $totalErrors += $stats['errors'];
                $totalSkipped += $stats['skipped'];
                $totalRecords += $stats['total'];
            }
        }

        return [
            'total_success' => $totalSuccess,
            'total_errors' => $totalErrors,
            'total_skipped' => $totalSkipped,
            'total_records' => $totalRecords,
            'years_imported' => array_keys($yearStats),
            'year_stats' => $yearStats
        ];
    }

    /**
     * Get all errors from all sheets
     */
    public function getErrors(): array
    {
        $allErrors = [];

        foreach ($this->sheetImporters as $year => $importer) {
            $errors = $importer->getErrors();
            
            if (!empty($errors)) {
                $allErrors[$year] = $errors;
            }
        }

        return $allErrors;
    }

    /**
     * Check if any sheet has errors
     */
    public function hasErrors(): bool
    {
        foreach ($this->sheetImporters as $importer) {
            if ($importer->hasErrors()) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get detailed error report formatted for display
     */
    public function getErrorReport(): array
    {
        $errors = $this->getErrors();
        $report = [];

        foreach ($errors as $year => $yearErrors) {
            foreach ($yearErrors as $error) {
                $report[] = [
                    'year' => $year,
                    'row' => $error['row'],
                    'nip_nas' => $error['nip_nas'],
                    'company' => $error['company'],
                    'message' => $error['error']
                ];
            }
        }

        return $report;
    }
}
