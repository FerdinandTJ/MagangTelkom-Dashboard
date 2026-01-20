<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Imports\Sheets\RegionWitelSheet;
use App\Imports\Sheets\TWSSheet;
use App\Imports\NkiSheetImport;

class PerformanceAMImport implements WithMultipleSheets
{
    protected $quarter;
    protected $year;
    protected $conflicts = [];
    protected $sharedData = [];
    protected $rowCount = 0;
    protected $nkiErrors = [];

    public function __construct($quarter, $year)
    {
        $this->quarter = $quarter;
        $this->year = $year;
    }

    public function sheets(): array
    {
        $nkiSheet = new NkiSheetImport($this->year);
        
        return [
            'region_and_witel' => new RegionWitelSheet($this->sharedData),
            "TWS {$this->year}" => new TWSSheet(
                $this->quarter,
                $this->year,
                $this->sharedData,
                $this->conflicts,
                $this->rowCount
            ),
            "NKI {$this->year}" => $nkiSheet,
        ];
    }

    public function getConflicts()
    {
        return $this->conflicts;
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }
    
    public function getNkiErrors()
    {
        return $this->nkiErrors;
    }
}
