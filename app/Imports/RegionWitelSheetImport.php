<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RegionWitelSheetImport implements ToCollection, WithHeadingRow
{
    protected int $regionsUpdated = 0;
    protected int $witelsUpdated = 0;
    protected int $regionsCreated = 0;
    protected int $witelsCreated = 0;
    protected array $errors = [];

    /**
     * Process the Region_and_Witel sheet
     */
    public function collection(Collection $rows)
    {
        Log::info("Starting Region_and_Witel sheet import", ['total_rows' => $rows->count()]);

        DB::beginTransaction();
        
        try {
            $currentRegion = null;
            
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // +2 because of 0-index and header row
                
                try {
                    // Skip empty rows
                    if ($this->isEmptyRow($row)) {
                        continue;
                    }

                    // Check if this row has region data (not merged/empty)
                    if (!empty($row['region_id'])) {
                        $regionId = (int) $row['region_id'];
                        $regionName = trim($row['nama_region'] ?? '');
                        $regionDescription = trim($row['description'] ?? '');
                        
                        // Validate region data
                        if (!$regionId || empty($regionName)) {
                            $this->errors[] = "Row {$rowNum}: Region ID dan Nama Region wajib diisi";
                            continue;
                        }

                        // Update or create region
                        $region = DB::table('regions')->where('id', $regionId)->first();
                        
                        if ($region) {
                            // Update existing region
                            DB::table('regions')
                                ->where('id', $regionId)
                                ->update([
                                    'name' => $regionName,
                                    'description' => $regionDescription,
                                    'updated_at' => now()
                                ]);
                            $this->regionsUpdated++;
                            Log::debug("Updated region", ['id' => $regionId, 'name' => $regionName]);
                        } else {
                            // Create new region
                            // Need to determine the 'code' field - we'll use a default pattern
                            $code = $this->generateRegionCode($regionName);
                            
                            DB::table('regions')->insert([
                                'id' => $regionId,
                                'code' => $code,
                                'name' => $regionName,
                                'description' => $regionDescription,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $this->regionsCreated++;
                            Log::debug("Created region", ['id' => $regionId, 'name' => $regionName, 'code' => $code]);
                        }
                        
                        $currentRegion = $regionId;
                    }

                    // Process witel data (always present in each row)
                    $witelId = !empty($row['id_witel']) ? (int) $row['id_witel'] : null;
                    $witelName = trim($row['nama_witel'] ?? '');
                    
                    // Validate witel data
                    if (!$witelId || empty($witelName)) {
                        $this->errors[] = "Row {$rowNum}: ID WITEL dan Nama Witel wajib diisi";
                        continue;
                    }
                    
                    if (!$currentRegion) {
                        $this->errors[] = "Row {$rowNum}: Tidak ada Region ID untuk witel {$witelId}";
                        continue;
                    }

                    // Update or create witel
                    $witel = DB::table('witels')->where('idwitels', $witelId)->first();
                    
                    if ($witel) {
                        // Update existing witel
                        DB::table('witels')
                            ->where('idwitels', $witelId)
                            ->update([
                                'nama_witels' => $witelName,
                                'region_id' => $currentRegion,
                                'updated_at' => now()
                            ]);
                        $this->witelsUpdated++;
                        Log::debug("Updated witel", ['id' => $witelId, 'name' => $witelName, 'region_id' => $currentRegion]);
                    } else {
                        // Create new witel
                        DB::table('witels')->insert([
                            'idwitels' => $witelId,
                            'nama_witels' => $witelName,
                            'region_id' => $currentRegion,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $this->witelsCreated++;
                        Log::debug("Created witel", ['id' => $witelId, 'name' => $witelName, 'region_id' => $currentRegion]);
                    }
                    
                } catch (\Exception $e) {
                    $this->errors[] = "Row {$rowNum}: {$e->getMessage()}";
                    Log::error("Error processing Region_and_Witel row", [
                        'row' => $rowNum,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();
            
            Log::info("Region_and_Witel import completed", [
                'regions_updated' => $this->regionsUpdated,
                'regions_created' => $this->regionsCreated,
                'witels_updated' => $this->witelsUpdated,
                'witels_created' => $this->witelsCreated,
                'errors' => count($this->errors)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Fatal error in Region_and_Witel import", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check if row is empty
     */
    protected function isEmptyRow($row): bool
    {
        return empty($row['region_id']) && 
               empty($row['nama_region']) && 
               empty($row['id_witel']) && 
               empty($row['nama_witel']);
    }

    /**
     * Generate region code from name
     * This is a fallback for new regions
     */
    protected function generateRegionCode(string $name): string
    {
        // Try to extract TREG number from name
        if (preg_match('/TREG\s*(\d+)/i', $name, $matches)) {
            return 'TREG ' . $matches[1];
        }
        
        if (preg_match('/TREG\s*HQ\s*(\d+)/i', $name, $matches)) {
            return 'TREG HQ ' . $matches[1];
        }
        
        // Default: use first 10 characters of name
        return strtoupper(substr($name, 0, 10));
    }

    /**
     * Get import statistics
     */
    public function getStats(): array
    {
        return [
            'regions_updated' => $this->regionsUpdated,
            'regions_created' => $this->regionsCreated,
            'witels_updated' => $this->witelsUpdated,
            'witels_created' => $this->witelsCreated,
            'total_regions' => $this->regionsUpdated + $this->regionsCreated,
            'total_witels' => $this->witelsUpdated + $this->witelsCreated,
            'errors' => count($this->errors)
        ];
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if there are errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
