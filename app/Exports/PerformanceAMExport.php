<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\Sheets\TWSExportSheet;
use App\Exports\Sheets\NKIExportSheet;

class PerformanceAMExport
{
    protected $quarter;
    protected $year;
    protected $region;
    protected $isYtd;
    protected $quartalsToInclude;

    public function __construct($quarter, $year, $region = null, $isYtd = false, $quartalsToInclude = null)
    {
        $this->quarter = $quarter;
        $this->year = $year;
        $this->region = $region;
        $this->isYtd = $isYtd;
        $this->quartalsToInclude = $quartalsToInclude ?? ["Q{$quarter}"];
    }

    public function export()
    {
        // Load template file
        $templatePath = storage_path('app/templates/template_upload.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        
        // Fill TWS sheet data
        $this->fillTWSSheet($spreadsheet);
        
        // Fill NKI sheet data
        $this->fillNKISheet($spreadsheet);
        
        // Region and Witel sheet stays as is from template
        
        return $spreadsheet;
    }
    
    protected function fillTWSSheet($spreadsheet)
    {
        $sheet = $spreadsheet->getSheetByName("TWS {$this->year}");
        if (!$sheet) {
            return;
        }
        
        // Update period info in row 1
        $sheet->setCellValue('A1', 'PERIODE');
        if ($this->isYtd && $this->quarter > 1) {
            $sheet->setCellValue('B1', "Q1-Q{$this->quarter} (YTD)");
        } else {
            $sheet->setCellValue('B1', "Q{$this->quarter}");
        }
        $sheet->setCellValue('C1', $this->year);
        
        // Get data
        $twsExport = new TWSExportSheet($this->quarter, $this->year, $this->region, $this->quartalsToInclude);
        $data = $twsExport->collection();
        
        // Fill data starting from row 3 (row 1 = Q/Year, row 2 = headers)
        $row = 3;
        foreach ($data as $rowData) {
            $col = 'A';
            foreach ($rowData as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
    }
    
    protected function fillNKISheet($spreadsheet)
    {
        $sheet = $spreadsheet->getSheetByName("NKI {$this->year}");
        if (!$sheet) {
            return;
        }
        
        // Update Q and Year in cell A1
        if ($this->isYtd && $this->quarter > 1) {
            $sheet->setCellValue('A1', "Q1-Q{$this->quarter} (YTD) {$this->year}");
        } else {
            $sheet->setCellValue('A1', "Q{$this->quarter} {$this->year}");
        }
        
        // Get data
        $nkiExport = new NKIExportSheet($this->quarter, $this->year, $this->region, $this->quartalsToInclude);
        $allData = $nkiExport->array();
        
        // $allData has: [0] = row1 (percentages), [1] = row2 (percentages), [2] = headers, [3+] = data rows
        
        // Fill row 1: percentage_result (G) and percentage_proses (AK)
        if (isset($allData[0])) {
            $col = 'A';
            foreach ($allData[0] as $value) {
                if ($value !== '') {
                    $sheet->setCellValue($col . '1', $value);
                }
                $col++;
            }
        }
        
        // Fill row 2: other percentages
        if (isset($allData[1])) {
            $col = 'A';
            foreach ($allData[1] as $value) {
                if ($value !== '') {
                    $sheet->setCellValue($col . '2', $value);
                }
                $col++;
            }
        }
        
        // Skip row 3 (headers already in template)
        
        // Fill row 3 with updated headers from NKI export
        if (isset($allData[2])) {
            $col = 'A';
            foreach ($allData[2] as $value) {
                $sheet->setCellValue($col . '3', $value);
                $col++;
            }
        }
        
        // Clear existing data from row 4 onwards
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 4) {
            $sheet->removeRow(4, $highestRow - 3);
        }
        
        // Fill data rows starting from row 4
        for ($i = 3; $i < count($allData); $i++) {
            $rowData = $allData[$i];
            $col = 'A';
            foreach ($rowData as $value) {
                $sheet->setCellValue($col . ($i + 1), $value); // $i=3 -> row 4, $i=4 -> row 5, etc
                $col++;
            }
        }
        
        // Apply formatting to data rows
        $lastRow = count($allData); // Total rows including headers
        
        // Format percentage in row 1 and 2 (percentage meta - values are already in number format like 80, not 0.80)
        $percentageMetaCols = ['G', 'J', 'M', 'P', 'S', 'V', 'Y', 'AB', 'AE', 'AH', 'AK', 'AN', 'AQ', 'AT'];
        foreach ($percentageMetaCols as $col) {
            $sheet->getStyle("{$col}1")->getNumberFormat()->setFormatCode('0.00"%"');
            $sheet->getStyle("{$col}2")->getNumberFormat()->setFormatCode('0.00"%"');
        }
        
        // Format currency columns (Rp) - F, G, I, J, AM, AN
        $currencyCols = ['F', 'G', 'I', 'J', 'AM', 'AN'];
        foreach ($currencyCols as $col) {
            $sheet->getStyle("{$col}4:{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('"Rp "#,##0');
        }
        
        // Format percentage columns - H,K,N,Q,T,W,Z,AC,AF,AI,AJ,AK,AL,AO,AR,AU,AV,AW,AX
        // Using 0.00"%" format to avoid multiplying by 100
        $percentageCols = ['H', 'K', 'N', 'Q', 'T', 'W', 'Z', 'AC', 'AF', 'AI', 'AJ', 'AK', 'AL', 'AO', 'AR', 'AU', 'AV', 'AW', 'AX'];
        foreach ($percentageCols as $col) {
            $sheet->getStyle("{$col}4:{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('0.00"%"');
        }
        
        // Format regular number columns
        $numberCols = ['L', 'M', 'O', 'P', 'R', 'S', 'U', 'V', 'X', 'Y', 'AA', 'AB', 'AD', 'AE', 'AG', 'AH', 'AP', 'AQ', 'AS', 'AT'];
        foreach ($numberCols as $col) {
            $sheet->getStyle("{$col}4:{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }
        
        // Make header row bold
        $sheet->getStyle('A3:AX3')->getFont()->setBold(true);
    }
}
