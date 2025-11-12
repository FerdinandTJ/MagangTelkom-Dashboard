<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Region;

/**
 * Seeder: Witels (Wilayah Telekomunikasi)
 * 
 * UPDATED untuk struktur baru:
 * - idwitels: Custom INT (bukan auto-increment)
 * - nama_witels: VARCHAR(25)
 * - region_id: FK to regions
 * 
 * Hapus field: code, province, description
 */
class WitelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get regions by new code structure
        $regions = Region::all()->keyBy('code');

        $witels = [
            // TREG1 - Sumatera
            ['idwitels' => 1001, 'nama_witels' => 'Aceh', 'region_id' => $regions['TREG1']->id],
            ['idwitels' => 1002, 'nama_witels' => 'Medan', 'region_id' => $regions['TREG1']->id],
            ['idwitels' => 1003, 'nama_witels' => 'Padang', 'region_id' => $regions['TREG1']->id],
            ['idwitels' => 1004, 'nama_witels' => 'Pekanbaru', 'region_id' => $regions['TREG1']->id],
            ['idwitels' => 1005, 'nama_witels' => 'Jambi', 'region_id' => $regions['TREG1']->id],
            ['idwitels' => 1006, 'nama_witels' => 'Palembang', 'region_id' => $regions['TREG1']->id],
            ['idwitels' => 1007, 'nama_witels' => 'Lampung', 'region_id' => $regions['TREG1']->id],
            ['idwitels' => 1008, 'nama_witels' => 'Babel', 'region_id' => $regions['TREG1']->id],

            // TREG2 - Jakarta, Banten, Jabar
            ['idwitels' => 2001, 'nama_witels' => 'Jakarta', 'region_id' => $regions['TREG2']->id],
            ['idwitels' => 2002, 'nama_witels' => 'Jakarta Selatan', 'region_id' => $regions['TREG2']->id],
            ['idwitels' => 2003, 'nama_witels' => 'Jakarta Barat', 'region_id' => $regions['TREG2']->id],
            ['idwitels' => 2004, 'nama_witels' => 'Jakarta Utara', 'region_id' => $regions['TREG2']->id],
            ['idwitels' => 2005, 'nama_witels' => 'Jakarta Timur', 'region_id' => $regions['TREG2']->id],
            ['idwitels' => 2006, 'nama_witels' => 'Banten', 'region_id' => $regions['TREG2']->id],
            ['idwitels' => 2007, 'nama_witels' => 'Tangerang', 'region_id' => $regions['TREG2']->id],
            ['idwitels' => 2008, 'nama_witels' => 'Bandung', 'region_id' => $regions['TREG2']->id],
            ['idwitels' => 2009, 'nama_witels' => 'Bekasi', 'region_id' => $regions['TREG2']->id],
            ['idwitels' => 2010, 'nama_witels' => 'Bogor', 'region_id' => $regions['TREG2']->id],

            // TREG3 - Jateng & DIY
            ['idwitels' => 3001, 'nama_witels' => 'Semarang', 'region_id' => $regions['TREG3']->id],
            ['idwitels' => 3002, 'nama_witels' => 'Solo', 'region_id' => $regions['TREG3']->id],
            ['idwitels' => 3003, 'nama_witels' => 'Purwokerto', 'region_id' => $regions['TREG3']->id],
            ['idwitels' => 3004, 'nama_witels' => 'Pekalongan', 'region_id' => $regions['TREG3']->id],
            ['idwitels' => 3005, 'nama_witels' => 'Yogyakarta', 'region_id' => $regions['TREG3']->id],

            // TREG4 - Jawa Timur
            ['idwitels' => 4001, 'nama_witels' => 'Surabaya', 'region_id' => $regions['TREG4']->id],
            ['idwitels' => 4002, 'nama_witels' => 'Malang', 'region_id' => $regions['TREG4']->id],
            ['idwitels' => 4003, 'nama_witels' => 'Kediri', 'region_id' => $regions['TREG4']->id],
            ['idwitels' => 4004, 'nama_witels' => 'Madiun', 'region_id' => $regions['TREG4']->id],
            ['idwitels' => 4005, 'nama_witels' => 'Jember', 'region_id' => $regions['TREG4']->id],

            // TREG5 - Bali, NTT, Kalimantan, Sulawesi, Maluku, Papua
            ['idwitels' => 5001, 'nama_witels' => 'Bali', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5002, 'nama_witels' => 'NTB', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5003, 'nama_witels' => 'NTT', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5004, 'nama_witels' => 'Kalbar', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5005, 'nama_witels' => 'Kaltim', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5006, 'nama_witels' => 'Kalsel', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5007, 'nama_witels' => 'Kalteng', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5008, 'nama_witels' => 'Sulut', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5009, 'nama_witels' => 'Sulsel', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5010, 'nama_witels' => 'Sulteng', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5011, 'nama_witels' => 'Maluku', 'region_id' => $regions['TREG5']->id],
            ['idwitels' => 5012, 'nama_witels' => 'Papua', 'region_id' => $regions['TREG5']->id],
        ];

        // Insert witels data with custom idwitels
        foreach ($witels as $witel) {
            DB::table('witels')->insert([
                'idwitels' => $witel['idwitels'],
                'nama_witels' => $witel['nama_witels'],
                'region_id' => $witel['region_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Witels seeded successfully! Total: ' . count($witels) . ' witels');
    }
}
