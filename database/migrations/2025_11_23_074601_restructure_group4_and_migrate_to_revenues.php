<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Migrate data from group4 to revenues table
        $this->migrateDataToRevenues();

        // Step 2: Remove revenue-related fields from group4
        Schema::table('group4', function (Blueprint $table) {
            $table->dropColumn([
                'revenue_realisasi',
                'revenue_target',
                'tahun',
                'bulan'
            ]);
        });
    }

    /**
     * Migrate existing group4 data to normalized structure
     */
    private function migrateDataToRevenues(): void
    {
        // Get all existing group4 records
        $existingData = DB::table('group4')
            ->select(
                'idGroup4 as id',
                'group3_id',
                'nama_group4',
                'revenue_realisasi',
                'revenue_target',
                'tahun',
                'bulan'
            )
            ->get();

        // Group by product identity (group3_id + nama_group4)
        $productGroups = [];
        foreach ($existingData as $record) {
            $productKey = $record->group3_id . '|' . strtolower(trim($record->nama_group4));
            
            if (!isset($productGroups[$productKey])) {
                $productGroups[$productKey] = [
                    'master' => [
                        'group3_id' => $record->group3_id,
                        'nama_group4' => $record->nama_group4,
                        'first_id' => $record->id, // Use first occurrence ID as master
                    ],
                    'revenues' => []
                ];
            }
            
            // Collect all revenue records for this product
            $productGroups[$productKey]['revenues'][] = [
                'old_id' => $record->id,
                'tahun' => $record->tahun,
                'bulan' => $record->bulan,
                'revenue_realisasi' => $record->revenue_realisasi,
                'revenue_target' => $record->revenue_target,
            ];
        }

        // Now process each product group
        $processedIds = [];
        $idMapping = []; // old_id => master_id

        foreach ($productGroups as $productKey => $productData) {
            $masterId = $productData['master']['first_id'];
            $processedIds[] = $masterId;

            // Insert all revenue records for this product
            foreach ($productData['revenues'] as $revenueData) {
                $idMapping[$revenueData['old_id']] = $masterId;
                
                // Insert into revenues table
                DB::table('revenues')->insert([
                    'group4_id' => $masterId,
                    'tahun' => $revenueData['tahun'],
                    'bulan' => $revenueData['bulan'],
                    'revenue_realisasi' => $revenueData['revenue_realisasi'],
                    'revenue_target' => $revenueData['revenue_target'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Delete duplicate group4 records (keep only master records)
        $duplicateIds = DB::table('group4')
            ->whereNotIn('idGroup4', $processedIds)
            ->pluck('idGroup4');

        if ($duplicateIds->isNotEmpty()) {
            DB::table('group4')->whereIn('idGroup4', $duplicateIds)->delete();
        }

        // Log migration results
        $masterCount = count($processedIds);
        $revenueCount = DB::table('revenues')->count();
        $deletedCount = $duplicateIds->count();

        \Log::info("Migration completed:", [
            'master_products' => $masterCount,
            'revenue_records' => $revenueCount,
            'duplicates_removed' => $deletedCount,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Warning: This down migration is destructive and complex
        // It attempts to restore the denormalized structure
        
        // Step 1: Re-add revenue columns to group4
        Schema::table('group4', function (Blueprint $table) {
            $table->decimal('revenue_realisasi', 15, 2)->default(0);
            $table->decimal('revenue_target', 15, 2)->default(0);
            $table->year('tahun')->nullable();
            $table->tinyInteger('bulan')->nullable();
        });

        // Step 2: Migrate data back from revenues to group4 (creates duplicates)
        $masterProducts = DB::table('group4')->get();
        
        foreach ($masterProducts as $product) {
            $revenues = DB::table('revenues')
                ->where('group4_id', $product->idGroup4)
                ->get();

            $firstRevenue = true;
            foreach ($revenues as $revenue) {
                if ($firstRevenue) {
                    // Update the master record with first revenue data
                    DB::table('group4')
                        ->where('idGroup4', $product->idGroup4)
                        ->update([
                            'revenue_realisasi' => $revenue->revenue_realisasi,
                            'revenue_target' => $revenue->revenue_target,
                            'tahun' => $revenue->tahun,
                            'bulan' => $revenue->bulan,
                        ]);
                    $firstRevenue = false;
                } else {
                    // Create duplicate group4 records for additional months
                    DB::table('group4')->insert([
                        'group3_id' => $product->group3_id,
                        'nama_group4' => $product->nama_group4,
                        'revenue_realisasi' => $revenue->revenue_realisasi,
                        'revenue_target' => $revenue->revenue_target,
                        'tahun' => $revenue->tahun,
                        'bulan' => $revenue->bulan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Note: revenues table will be dropped by the previous migration's down()
    }
};
