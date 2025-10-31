<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Region;
use App\Models\Witel;
use Illuminate\Support\Facades\DB;

class AssignCompanyRegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder randomly assigns regions and WITELs to existing companies
     * for demonstration purposes.
     */
    public function run(): void
    {
        $this->command->info('Assigning regions and WITELs to companies...');

        $companies = Company::all();
        $regions = Region::with('witels')->get();

        if ($companies->isEmpty()) {
            $this->command->warn('No companies found to assign regions.');
            return;
        }

        $progressBar = $this->command->getOutput()->createProgressBar($companies->count());
        $progressBar->start();

        foreach ($companies as $company) {
            // Randomly select a region (weighted: REG2 40%, others 15% each)
            $random = rand(1, 100);
            if ($random <= 40) {
                $region = $regions->where('code', 'REG2')->first(); // Jakarta area (most common)
            } elseif ($random <= 55) {
                $region = $regions->where('code', 'REG3')->first(); // Jawa Tengah
            } elseif ($random <= 70) {
                $region = $regions->where('code', 'REG4')->first(); // Jawa Timur
            } elseif ($random <= 85) {
                $region = $regions->where('code', 'REG1')->first(); // Sumatera
            } else {
                $region = $regions->where('code', 'REG5')->first(); // Eastern Indonesia
            }

            // Select random WITEL from the region (if available)
            $witel = null;
            if ($region && $region->witels->isNotEmpty()) {
                $witel = $region->witels->random();
            }

            // Update company
            $company->primary_region_id = $region->id;
            $company->primary_witel_id = $witel ? $witel->id : null;
            $company->save();

            // Add to pivot table as primary location
            DB::table('company_regions')->insert([
                'company_id' => $company->id,
                'region_id' => $region->id,
                'witel_id' => $witel ? $witel->id : null,
                'is_primary' => true,
                'notes' => 'Primary location',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 20% chance to add secondary region for larger companies
            if (rand(1, 100) <= 20 && $regions->count() > 1) {
                $secondaryRegion = $regions->where('id', '!=', $region->id)->random();
                $secondaryWitel = $secondaryRegion->witels->isNotEmpty() ? $secondaryRegion->witels->random() : null;

                DB::table('company_regions')->insert([
                    'company_id' => $company->id,
                    'region_id' => $secondaryRegion->id,
                    'witel_id' => $secondaryWitel ? $secondaryWitel->id : null,
                    'is_primary' => false,
                    'notes' => 'Secondary location',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('Successfully assigned regions and WITELs to ' . $companies->count() . ' companies.');
        
        // Update revenues with region/witel info based on company's primary location
        $this->command->info('Updating revenue records with regional data...');
        
        DB::statement('
            UPDATE revenues r
            INNER JOIN companies c ON r.company_id = c.id
            SET r.region_id = c.primary_region_id,
                r.witel_id = c.primary_witel_id
            WHERE r.region_id IS NULL
        ');
        
        $this->command->info('Revenue records updated successfully!');
    }
}
