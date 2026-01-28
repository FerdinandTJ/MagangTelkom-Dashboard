<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Log;

class BulananImport implements WithMultipleSheets
{
    private $sheetNames = [];

    public function __construct(array $sheetNames = [])
    {
        $this->sheetNames = $sheetNames;
    }

    public function sheets(): array
    {
        Log::info("BulananImport: Registering sheet importers", ['sheets' => $this->sheetNames]);
        
        $sheets = [];
        
        foreach ($this->sheetNames as $sheetName) {
            // Check if it matches "Target YYYY" pattern
            if (preg_match('/^Target\s+\d{4}$/i', $sheetName)) {
                Log::info("BulananImport: Registering Target sheet: {$sheetName}");
                $sheets[$sheetName] = new BulanSheetImport($sheetName);
            }
            // Check if it matches "List LOP YYYY" pattern
            elseif (preg_match('/^List\s+LOP\s+\d{4}$/i', $sheetName)) {
                Log::info("BulananImport: Registering List LOP sheet: {$sheetName}");
                $sheets[$sheetName] = new LopBulanSheetImport($sheetName);
            }
        }
        
        return $sheets;
    }
}
