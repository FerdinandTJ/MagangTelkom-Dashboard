<?php

namespace App\Imports\Sheets;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Region;
use App\Models\Witel;
use Illuminate\Support\Facades\Log;

class RegionWitelSheet implements ToModel, WithStartRow
{
    protected $sharedData;
    protected $lastRegionId = null;
    protected $currentRow = 0;

    public function __construct(&$sharedData)
    {
        $this->sharedData = &$sharedData;
    }

    public function startRow(): int
    {
        return 2; // Start from row 2 (row 1 is header)
    }

    public function model(array $row)
    {
        $this->currentRow++;

        try {
            // Extract values from columns A to E (index 0-4)
            $kodeRegion = $row[0] ?? null;      // A: Kode Region (untuk lookup)
            $namaRegion = $row[1] ?? null;      // B: Nama Region
            $descRegion = $row[2] ?? null;      // C: Description Region
            $kodeWitel = $row[3] ?? null;       // D: Kode Witel
            $namaWitel = $row[4] ?? null;       // E: Nama Witel

            // Process Region (Columns A, B, C)
            if (!empty($kodeRegion)) {
                // Check if region exists by code
                $existingRegion = Region::where('code', $kodeRegion)->first();
                
                if ($existingRegion) {
                    // Region exists - check if update needed
                    $needsUpdate = false;
                    $updates = [];
                    
                    if (!empty($namaRegion) && $existingRegion->name !== $namaRegion) {
                        $updates['name'] = $namaRegion;
                        $needsUpdate = true;
                    }
                    
                    if (!empty($descRegion) && $existingRegion->description !== $descRegion) {
                        $updates['description'] = $descRegion;
                        $needsUpdate = true;
                    }
                    
                    if ($needsUpdate) {
                        $existingRegion->update($updates);
                        Log::info("Region updated: Code={$kodeRegion}, Changes=" . json_encode($updates));
                    }
                    
                    $region = $existingRegion;
                } else {
                    // Region doesn't exist - create new
                    $region = Region::create([
                        'code' => $kodeRegion,
                        'name' => $namaRegion ?? $kodeRegion,
                        'description' => $descRegion ?? '',
                    ]);
                    
                    Log::info("Region created: Code={$kodeRegion}, Name={$namaRegion}");
                }

                // Store last region ID for subsequent rows
                $this->lastRegionId = $region->id;
            }

            // Process Witel (Columns D, E)
            if (!empty($kodeWitel)) {
                if ($this->lastRegionId === null) {
                    Log::warning("No region context for Witel at row {$this->currentRow}");
                    return null;
                }

                // Check if witel exists by idwitels (kolom D adalah ID witel)
                $existingWitel = Witel::where('idwitels', $kodeWitel)->first();
                
                if ($existingWitel) {
                    // Witel exists - check if update needed
                    $needsUpdate = false;
                    $updates = [];
                    
                    // Check nama_witels (kolom E)
                    if (!empty($namaWitel) && $existingWitel->nama_witels !== $namaWitel) {
                        $updates['nama_witels'] = $namaWitel;
                        $needsUpdate = true;
                    }
                    
                    // Check region_id (dari kolom A dengan cascading)
                    if ($existingWitel->region_id !== $this->lastRegionId) {
                        $updates['region_id'] = $this->lastRegionId;
                        $needsUpdate = true;
                        Log::info("Witel region changed: ID={$kodeWitel}, OldRegion={$existingWitel->region_id}, NewRegion={$this->lastRegionId}");
                    }
                    
                    if ($needsUpdate) {
                        $existingWitel->update($updates);
                        Log::info("Witel updated: ID={$kodeWitel}, Changes=" . json_encode($updates));
                    } else {
                        Log::info("Witel unchanged: ID={$kodeWitel}, Name={$namaWitel}");
                    }
                    
                    $witel = $existingWitel;
                } else {
                    // Witel doesn't exist - create new
                    $witel = Witel::create([
                        'idwitels' => $kodeWitel,
                        'nama_witels' => $namaWitel ?? 'WITEL ' . $kodeWitel,
                        'region_id' => $this->lastRegionId,
                    ]);
                    
                    Log::info("Witel created: ID={$kodeWitel}, Name={$namaWitel}, Region={$this->lastRegionId}");
                }
            }

            return null; // We handle models directly, don't return model

        } catch (\Exception $e) {
            Log::error("Error processing region_and_witel row {$this->currentRow}: " . $e->getMessage());
            throw $e;
        }
    }
}
