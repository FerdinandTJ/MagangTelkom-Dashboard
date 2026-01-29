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
        // Generate Account Managers untuk SETIAP REGION
        // Memastikan semua 5 TREG (TREG1-TREG5) memiliki AM yang terhubung
        
        $accountManagers = [
            // HQ TREG2 - Headquarters (3 witels: HQ Corporate, HQ Enterprise, HQ Government)
            ['nik' => '210001', 'nama' => 'Ir. Bambang Suryanto', 'posisi' => 'EAM', 'no_gsm' => '081234567101', 'idwitels' => 2101], // HQ Corporate
            ['nik' => '210002', 'nama' => 'Dr. Siti Nurhaliza', 'posisi' => 'EAM', 'no_gsm' => '081234567102', 'idwitels' => 2102], // HQ Enterprise
            ['nik' => '210003', 'nama' => 'Drs. Ahmad Fauzi', 'posisi' => 'EAM', 'no_gsm' => '081234567103', 'idwitels' => 2103], // HQ Government

            // TREG1 - Sumatera (8 witels: Aceh, Medan, Padang, Pekanbaru, Jambi, Palembang, Lampung, Babel)
            ['nik' => '810001', 'nama' => 'Ahmad Rizki', 'posisi' => 'SAM', 'no_gsm' => '081234567001', 'idwitels' => 1001], // Aceh
            ['nik' => '810002', 'nama' => 'Siti Rahma', 'posisi' => 'AM 1', 'no_gsm' => '081234567002', 'idwitels' => 1002], // Medan
            ['nik' => '810003', 'nama' => 'Budi Santoso', 'posisi' => 'AM 2', 'no_gsm' => '081234567003', 'idwitels' => 1003], // Padang
            ['nik' => '810004', 'nama' => 'Rina Wijaya', 'posisi' => 'AM', 'no_gsm' => '081234567004', 'idwitels' => 1004], // Pekanbaru
            ['nik' => '810005', 'nama' => 'Agus Setiawan', 'posisi' => 'EAM', 'no_gsm' => '081234567005', 'idwitels' => 1005], // Jambi
            ['nik' => '810006', 'nama' => 'Putri Ayu', 'posisi' => 'AM 1', 'no_gsm' => '081234567006', 'idwitels' => 1006], // Palembang
            ['nik' => '810007', 'nama' => 'Rizal Fauzi', 'posisi' => 'AM', 'no_gsm' => '081234567007', 'idwitels' => 1007], // Lampung
            ['nik' => '810008', 'nama' => 'Lia Maulida', 'posisi' => 'AM 2', 'no_gsm' => '081234567008', 'idwitels' => 1008], // Babel

            // TREG2 - Jakarta, Banten, Jabar (10 witels)
            ['nik' => '820001', 'nama' => 'Dewi Lestari', 'posisi' => 'SAM', 'no_gsm' => '081234567011', 'idwitels' => 2001], // Jakarta
            ['nik' => '820002', 'nama' => 'Hendra Kusuma', 'posisi' => 'AM 1 PRO', 'no_gsm' => '081234567012', 'idwitels' => 2002], // Jakarta Selatan
            ['nik' => '820003', 'nama' => 'Maya Sari', 'posisi' => 'AM 2 PRO', 'no_gsm' => '081234567013', 'idwitels' => 2003], // Jakarta Barat
            ['nik' => '820004', 'nama' => 'Andi Pratama', 'posisi' => 'AM 3', 'no_gsm' => '081234567014', 'idwitels' => 2004], // Jakarta Utara
            ['nik' => '820005', 'nama' => 'Lina Marlina', 'posisi' => 'AM 1', 'no_gsm' => '081234567015', 'idwitels' => 2005], // Jakarta Timur
            ['nik' => '820006', 'nama' => 'Rudi Hartono', 'posisi' => 'AM', 'no_gsm' => '081234567016', 'idwitels' => 2006], // Banten
            ['nik' => '820007', 'nama' => 'Fitri Handayani', 'posisi' => 'EAM', 'no_gsm' => '081234567017', 'idwitels' => 2007], // Tangerang
            ['nik' => '820008', 'nama' => 'Dedi Mulyadi', 'posisi' => 'SAM', 'no_gsm' => '081234567018', 'idwitels' => 2008], // Bandung
            ['nik' => '820009', 'nama' => 'Nina Septiani', 'posisi' => 'AM 1', 'no_gsm' => '081234567019', 'idwitels' => 2009], // Bekasi
            ['nik' => '820010', 'nama' => 'Toni Kurniawan', 'posisi' => 'AM 2', 'no_gsm' => '081234567020', 'idwitels' => 2010], // Bogor

            // TREG3 - Jateng & DIY (5 witels: Semarang, Solo, Purwokerto, Pekalongan, Yogyakarta)
            ['nik' => '830001', 'nama' => 'Joko Widodo', 'posisi' => 'SAM', 'no_gsm' => '081234567021', 'idwitels' => 3001], // Semarang
            ['nik' => '830002', 'nama' => 'Sri Mulyani', 'posisi' => 'AM 2', 'no_gsm' => '081234567022', 'idwitels' => 3002], // Solo
            ['nik' => '830003', 'nama' => 'Bambang Susilo', 'posisi' => 'AM 1', 'no_gsm' => '081234567023', 'idwitels' => 3003], // Purwokerto
            ['nik' => '830004', 'nama' => 'Tri Wahyuni', 'posisi' => 'AM', 'no_gsm' => '081234567024', 'idwitels' => 3004], // Pekalongan
            ['nik' => '830005', 'nama' => 'Agung Prabowo', 'posisi' => 'EAM', 'no_gsm' => '081234567025', 'idwitels' => 3005], // Yogyakarta

            // TREG4 - Jawa Timur (5 witels: Surabaya, Malang, Kediri, Madiun, Jember)
            ['nik' => '840001', 'nama' => 'Kartika Putri', 'posisi' => 'SAM', 'no_gsm' => '081234567031', 'idwitels' => 4001], // Surabaya
            ['nik' => '840002', 'nama' => 'Wahyu Nugroho', 'posisi' => 'AM 1 PRO', 'no_gsm' => '081234567032', 'idwitels' => 4002], // Malang
            ['nik' => '840003', 'nama' => 'Dian Sastro', 'posisi' => 'AM', 'no_gsm' => '081234567033', 'idwitels' => 4003], // Kediri
            ['nik' => '840004', 'nama' => 'Eko Prasetyo', 'posisi' => 'AM 1', 'no_gsm' => '081234567034', 'idwitels' => 4004], // Madiun
            ['nik' => '840005', 'nama' => 'Sari Indah', 'posisi' => 'AM 2', 'no_gsm' => '081234567035', 'idwitels' => 4005], // Jember

            // TREG5 - Bali, NTT, Kalimantan, Sulawesi, Maluku, Papua (12 witels)
            ['nik' => '850001', 'nama' => 'Made Wirawan', 'posisi' => 'SAM', 'no_gsm' => '081234567041', 'idwitels' => 5001], // Bali
            ['nik' => '850002', 'nama' => 'Ketut Suryani', 'posisi' => 'AM 2', 'no_gsm' => '081234567042', 'idwitels' => 5002], // NTB
            ['nik' => '850003', 'nama' => 'Wayan Sudirman', 'posisi' => 'AM 1', 'no_gsm' => '081234567043', 'idwitels' => 5003], // NTT
            ['nik' => '850004', 'nama' => 'Nyoman Ariani', 'posisi' => 'EAM', 'no_gsm' => '081234567044', 'idwitels' => 5004], // Kalbar
            ['nik' => '850005', 'nama' => 'Surya Wijaya', 'posisi' => 'AM', 'no_gsm' => '081234567045', 'idwitels' => 5005], // Kaltim
            ['nik' => '850006', 'nama' => 'Andi Saputra', 'posisi' => 'AM 1', 'no_gsm' => '081234567046', 'idwitels' => 5006], // Kalsel
            ['nik' => '850007', 'nama' => 'Rina Kusuma', 'posisi' => 'AM 2', 'no_gsm' => '081234567047', 'idwitels' => 5007], // Kalteng
            ['nik' => '850008', 'nama' => 'David Manalu', 'posisi' => 'SAM', 'no_gsm' => '081234567048', 'idwitels' => 5008], // Sulut
            ['nik' => '850009', 'nama' => 'Fatimah Zahra', 'posisi' => 'AM', 'no_gsm' => '081234567049', 'idwitels' => 5009], // Sulsel
            ['nik' => '850010', 'nama' => 'Rizki Pratama', 'posisi' => 'AM 1', 'no_gsm' => '081234567050', 'idwitels' => 5010], // Sulteng
            ['nik' => '850011', 'nama' => 'Siti Aminah', 'posisi' => 'AM 2', 'no_gsm' => '081234567051', 'idwitels' => 5011], // Maluku
            ['nik' => '850012', 'nama' => 'Budi Setiawan', 'posisi' => 'EAM', 'no_gsm' => '081234567052', 'idwitels' => 5012], // Papua
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
