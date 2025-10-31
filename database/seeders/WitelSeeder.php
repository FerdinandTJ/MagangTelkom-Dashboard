<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Witel;

class WitelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get regions
        $hq = Region::where('code', 'HQ')->first();
        $reg1 = Region::where('code', 'REG1')->first();
        $reg2 = Region::where('code', 'REG2')->first();
        $reg3 = Region::where('code', 'REG3')->first();
        $reg4 = Region::where('code', 'REG4')->first();
        $reg5 = Region::where('code', 'REG5')->first();

        $witels = [
            // Region 1 - Sumatera
            ['region_id' => $reg1->id, 'code' => 'WITEL-ACEH', 'name' => 'WITEL Aceh', 'province' => 'Aceh'],
            ['region_id' => $reg1->id, 'code' => 'WITEL-SUMUT', 'name' => 'WITEL Sumatera Utara', 'province' => 'Sumatera Utara'],
            ['region_id' => $reg1->id, 'code' => 'WITEL-SUMBAR', 'name' => 'WITEL Sumatera Barat', 'province' => 'Sumatera Barat'],
            ['region_id' => $reg1->id, 'code' => 'WITEL-RIAU', 'name' => 'WITEL Riau', 'province' => 'Riau'],
            ['region_id' => $reg1->id, 'code' => 'WITEL-JAMBI', 'name' => 'WITEL Jambi', 'province' => 'Jambi'],
            ['region_id' => $reg1->id, 'code' => 'WITEL-SUMSEL', 'name' => 'WITEL Sumatera Selatan', 'province' => 'Sumatera Selatan'],
            ['region_id' => $reg1->id, 'code' => 'WITEL-BABEL', 'name' => 'WITEL Bangka Belitung', 'province' => 'Kepulauan Bangka Belitung'],
            ['region_id' => $reg1->id, 'code' => 'WITEL-LAMPUNG', 'name' => 'WITEL Lampung', 'province' => 'Lampung'],

            // Region 2 - Jakarta, Banten, Jawa Barat
            ['region_id' => $reg2->id, 'code' => 'WITEL-JKT', 'name' => 'WITEL Jakarta', 'province' => 'DKI Jakarta'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-JKTSEL', 'name' => 'WITEL Jakarta Selatan', 'province' => 'DKI Jakarta'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-JKTBAR', 'name' => 'WITEL Jakarta Barat', 'province' => 'DKI Jakarta'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-JKTUT', 'name' => 'WITEL Jakarta Utara', 'province' => 'DKI Jakarta'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-JKTTIM', 'name' => 'WITEL Jakarta Timur', 'province' => 'DKI Jakarta'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-BANTEN', 'name' => 'WITEL Banten', 'province' => 'Banten'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-TANGERANG', 'name' => 'WITEL Tangerang', 'province' => 'Banten'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-BDG', 'name' => 'WITEL Bandung', 'province' => 'Jawa Barat'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-BKS', 'name' => 'WITEL Bekasi', 'province' => 'Jawa Barat'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-BOGOR', 'name' => 'WITEL Bogor', 'province' => 'Jawa Barat'],
            ['region_id' => $reg2->id, 'code' => 'WITEL-JABAR', 'name' => 'WITEL Jawa Barat', 'province' => 'Jawa Barat'],

            // Region 3 - Jawa Tengah & DIY
            ['region_id' => $reg3->id, 'code' => 'WITEL-SMG', 'name' => 'WITEL Semarang', 'province' => 'Jawa Tengah'],
            ['region_id' => $reg3->id, 'code' => 'WITEL-SOLO', 'name' => 'WITEL Solo', 'province' => 'Jawa Tengah'],
            ['region_id' => $reg3->id, 'code' => 'WITEL-PURWOKERTO', 'name' => 'WITEL Purwokerto', 'province' => 'Jawa Tengah'],
            ['region_id' => $reg3->id, 'code' => 'WITEL-PEKALONGAN', 'name' => 'WITEL Pekalongan', 'province' => 'Jawa Tengah'],
            ['region_id' => $reg3->id, 'code' => 'WITEL-YOG', 'name' => 'WITEL Yogyakarta', 'province' => 'DI Yogyakarta'],

            // Region 4 - Jawa Timur
            ['region_id' => $reg4->id, 'code' => 'WITEL-SBY', 'name' => 'WITEL Surabaya', 'province' => 'Jawa Timur'],
            ['region_id' => $reg4->id, 'code' => 'WITEL-MLG', 'name' => 'WITEL Malang', 'province' => 'Jawa Timur'],
            ['region_id' => $reg4->id, 'code' => 'WITEL-KEDIRI', 'name' => 'WITEL Kediri', 'province' => 'Jawa Timur'],
            ['region_id' => $reg4->id, 'code' => 'WITEL-MADIUN', 'name' => 'WITEL Madiun', 'province' => 'Jawa Timur'],
            ['region_id' => $reg4->id, 'code' => 'WITEL-JEMBER', 'name' => 'WITEL Jember', 'province' => 'Jawa Timur'],

            // Region 5 - Bali, Nusa Tenggara, Kalimantan, Sulawesi, Maluku, Papua
            ['region_id' => $reg5->id, 'code' => 'WITEL-BALI', 'name' => 'WITEL Bali', 'province' => 'Bali'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-NTB', 'name' => 'WITEL Nusa Tenggara Barat', 'province' => 'Nusa Tenggara Barat'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-NTT', 'name' => 'WITEL Nusa Tenggara Timur', 'province' => 'Nusa Tenggara Timur'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-KALBAR', 'name' => 'WITEL Kalimantan Barat', 'province' => 'Kalimantan Barat'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-KALTIM', 'name' => 'WITEL Kalimantan Timur', 'province' => 'Kalimantan Timur'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-KALSEL', 'name' => 'WITEL Kalimantan Selatan', 'province' => 'Kalimantan Selatan'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-KALTENG', 'name' => 'WITEL Kalimantan Tengah', 'province' => 'Kalimantan Tengah'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-SULUT', 'name' => 'WITEL Sulawesi Utara', 'province' => 'Sulawesi Utara'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-SULSEL', 'name' => 'WITEL Sulawesi Selatan', 'province' => 'Sulawesi Selatan'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-SULTENG', 'name' => 'WITEL Sulawesi Tengah', 'province' => 'Sulawesi Tengah'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-MALUKU', 'name' => 'WITEL Maluku', 'province' => 'Maluku'],
            ['region_id' => $reg5->id, 'code' => 'WITEL-PAPUA', 'name' => 'WITEL Papua', 'province' => 'Papua'],
        ];

        foreach ($witels as $witel) {
            Witel::create($witel);
        }
    }
}
