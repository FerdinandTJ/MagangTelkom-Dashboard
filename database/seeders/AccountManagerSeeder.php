<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountManagers = [
            // Sample Account Managers for TREG1
            ['nik' => '810001', 'nama' => 'Ahmad Rizki', 'posisi' => 'SAM', 'no_gsm' => '081234567001', 'idwitels' => 1001],
            ['nik' => '810002', 'nama' => 'Siti Rahma', 'posisi' => 'AM1', 'no_gsm' => '081234567002', 'idwitels' => 1002],
            ['nik' => '810003', 'nama' => 'Budi Santoso', 'posisi' => 'AM2', 'no_gsm' => '081234567003', 'idwitels' => 1003],
            ['nik' => '810004', 'nama' => 'Rina Wijaya', 'posisi' => 'AM', 'no_gsm' => '081234567004', 'idwitels' => 1004],
            ['nik' => '810005', 'nama' => 'Agus Setiawan', 'posisi' => 'EAM', 'no_gsm' => '081234567005', 'idwitels' => 1005],

            // Sample Account Managers for TREG2
            ['nik' => '820001', 'nama' => 'Dewi Lestari', 'posisi' => 'SAM', 'no_gsm' => '081234567011', 'idwitels' => 2001],
            ['nik' => '820002', 'nama' => 'Hendra Kusuma', 'posisi' => 'AM1PRO', 'no_gsm' => '081234567012', 'idwitels' => 2002],
            ['nik' => '820003', 'nama' => 'Maya Sari', 'posisi' => 'AM2PRO', 'no_gsm' => '081234567013', 'idwitels' => 2003],
            ['nik' => '820004', 'nama' => 'Andi Pratama', 'posisi' => 'AM3', 'no_gsm' => '081234567014', 'idwitels' => 2004],
            ['nik' => '820005', 'nama' => 'Lina Marlina', 'posisi' => 'AM1', 'no_gsm' => '081234567015', 'idwitels' => 2005],
            ['nik' => '820006', 'nama' => 'Rudi Hartono', 'posisi' => 'AM', 'no_gsm' => '081234567016', 'idwitels' => 2006],
            ['nik' => '820007', 'nama' => 'Fitri Handayani', 'posisi' => 'EAM', 'no_gsm' => '081234567017', 'idwitels' => 2007],

            // Sample Account Managers for TREG3
            ['nik' => '830001', 'nama' => 'Joko Widodo', 'posisi' => 'SAM', 'no_gsm' => '081234567021', 'idwitels' => 3001],
            ['nik' => '830002', 'nama' => 'Sri Mulyani', 'posisi' => 'AM2', 'no_gsm' => '081234567022', 'idwitels' => 3002],
            ['nik' => '830003', 'nama' => 'Bambang Susilo', 'posisi' => 'AM1', 'no_gsm' => '081234567023', 'idwitels' => 3003],

            // Sample Account Managers for TREG4
            ['nik' => '840001', 'nama' => 'Kartika Putri', 'posisi' => 'SAM', 'no_gsm' => '081234567031', 'idwitels' => 4001],
            ['nik' => '840002', 'nama' => 'Wahyu Nugroho', 'posisi' => 'AM1PRO', 'no_gsm' => '081234567032', 'idwitels' => 4002],
            ['nik' => '840003', 'nama' => 'Dian Sastro', 'posisi' => 'AM', 'no_gsm' => '081234567033', 'idwitels' => 4003],

            // Sample Account Managers for TREG5
            ['nik' => '850001', 'nama' => 'Made Wirawan', 'posisi' => 'SAM', 'no_gsm' => '081234567041', 'idwitels' => 5001],
            ['nik' => '850002', 'nama' => 'Ketut Suryani', 'posisi' => 'AM2', 'no_gsm' => '081234567042', 'idwitels' => 5002],
            ['nik' => '850003', 'nama' => 'Wayan Sudirman', 'posisi' => 'AM1', 'no_gsm' => '081234567043', 'idwitels' => 5003],
            ['nik' => '850004', 'nama' => 'Nyoman Ariani', 'posisi' => 'EAM', 'no_gsm' => '081234567044', 'idwitels' => 5004],
        ];

        // Insert account managers data
        foreach ($accountManagers as $am) {
            DB::table('account_managers')->insert([
                'nik' => $am['nik'],
                'nama' => $am['nama'],
                'posisi' => $am['posisi'],
                'no_gsm' => $am['no_gsm'],
                'idwitels' => $am['idwitels'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Account Managers seeded successfully! Total: ' . count($accountManagers) . ' account managers');
    }
}
