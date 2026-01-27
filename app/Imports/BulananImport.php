<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Illuminate\Support\Facades\Log;

class BulananImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public function sheets(): array
    {
        Log::info("BulananImport: Registering sheet importers");
        
        $sheets = [
            'Target 2026' => new BulanSheetImport('Target 2026'),
            'List LOP 2026' => new LopBulanSheetImport('List LOP 2026'),
        ];
        
        Log::info("BulananImport: Registered sheets", ['sheets' => array_keys($sheets)]);
        
        return $sheets;
    }

    public function onUnknownSheet($sheetName)
    {
        Log::warning("BulananImport: Skipping unknown sheet: {$sheetName}");
        // Skip sheet yang tidak dikenal
    }
}
