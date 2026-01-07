<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Insert dummy data untuk testing tanpa perlu seeder terpisah
     */
    public function run(): void
    {
        // 1. INSERT COMPANIES DATA (if empty)
        if (DB::table('companies')->count() == 0) {
            $companies = [
            // ========== HQ TREG2 - Headquarters (Corporate/Enterprise/Government) ==========
            ['nip_nas' => 'NIP016', 'nama_perusahaan' => 'PT Bank Mandiri Tbk', 'subsegment' => 'Corporate', 'source_data' => 'TIBS-NP', 'idwitels' => 2101],
            ['nip_nas' => 'NIP017', 'nama_perusahaan' => 'PT Pertamina (Persero)', 'subsegment' => 'Corporate', 'source_data' => 'TIBS-NP', 'idwitels' => 2101],
            ['nip_nas' => 'NIP018', 'nama_perusahaan' => 'PT Astra International Tbk', 'subsegment' => 'Enterprise', 'source_data' => 'TIBS-NP', 'idwitels' => 2102],
            ['nip_nas' => 'NIP019', 'nama_perusahaan' => 'PT Unilever Indonesia Tbk', 'subsegment' => 'Enterprise', 'source_data' => 'TIBS-NP', 'idwitels' => 2102],
            ['nip_nas' => 'NIP020', 'nama_perusahaan' => 'Kementerian Keuangan RI', 'subsegment' => 'Government', 'source_data' => 'TIBS-NP', 'idwitels' => 2103],
            ['nip_nas' => 'NIP021', 'nama_perusahaan' => 'Kementerian BUMN RI', 'subsegment' => 'Government', 'source_data' => 'TIBS-NP', 'idwitels' => 2103],
            
            // ========== TREG1 - SUMATERA (Aceh, Medan, Padang, Palembang) ==========
            // PTN - Perguruan Tinggi Negeri
            ['nip_nas' => 'NIP001', 'nama_perusahaan' => 'Universitas Syiah Kuala', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 1001], // Aceh
            ['nip_nas' => 'NIP101', 'nama_perusahaan' => 'Universitas Sumatera Utara', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 1002], // Medan
            ['nip_nas' => 'NIP102', 'nama_perusahaan' => 'Universitas Andalas', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 1003], // Padang
            ['nip_nas' => 'NIP103', 'nama_perusahaan' => 'Universitas Sriwijaya', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 1004], // Palembang
            // Hospital
            ['nip_nas' => 'NIP104', 'nama_perusahaan' => 'RSUP H. Adam Malik Medan', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 1002], // Medan
            ['nip_nas' => 'NIP105', 'nama_perusahaan' => 'RSUP Dr. M. Djamil Padang', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 1003], // Padang
            ['nip_nas' => 'NIP106', 'nama_perusahaan' => 'RSUP Dr. Moh. Hoesin Palembang', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 1004], // Palembang
            // Airport
            ['nip_nas' => 'NIP107', 'nama_perusahaan' => 'Bandara Kualanamu Medan', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 1002], // Medan
            ['nip_nas' => 'NIP108', 'nama_perusahaan' => 'Bandara Minangkabau Padang', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 1003], // Padang
            
            // ========== TREG2 - JAKARTA & JABAR ==========
            // PTN
            ['nip_nas' => 'NIP003', 'nama_perusahaan' => 'Universitas Indonesia', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 2002], // Jakarta Selatan
            ['nip_nas' => 'NIP007', 'nama_perusahaan' => 'Institut Teknologi Bandung', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 2008], // Bandung
            ['nip_nas' => 'NIP201', 'nama_perusahaan' => 'Universitas Pendidikan Indonesia', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 2008], // Bandung
            ['nip_nas' => 'NIP202', 'nama_perusahaan' => 'Universitas Padjajaran', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 2008], // Bandung
            // PTS
            ['nip_nas' => 'NIP011', 'nama_perusahaan' => 'Universitas Bina Nusantara', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 2003], // Jakarta Barat
            ['nip_nas' => 'NIP203', 'nama_perusahaan' => 'Universitas Trisakti', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP204', 'nama_perusahaan' => 'Universitas Tarumanagara', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 2003], // Jakarta Barat
            ['nip_nas' => 'NIP205', 'nama_perusahaan' => 'Telkom University', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 2008], // Bandung
            // Hospital
            ['nip_nas' => 'NIP002', 'nama_perusahaan' => 'RS Cipto Mangunkusumo', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP010', 'nama_perusahaan' => 'RS Hasan Sadikin Bandung', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 2008], // Bandung
            ['nip_nas' => 'NIP206', 'nama_perusahaan' => 'RS Fatmawati Jakarta', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 2002], // Jakarta Selatan
            ['nip_nas' => 'NIP207', 'nama_perusahaan' => 'RS Persahabatan Jakarta', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 2004], // Jakarta Timur
            ['nip_nas' => 'NIP208', 'nama_perusahaan' => 'RS Pantai Indah Kapuk', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 2005], // Jakarta Utara
            // Airport
            ['nip_nas' => 'NIP004', 'nama_perusahaan' => 'Bandara Soekarno-Hatta', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 2007], // Tangerang
            ['nip_nas' => 'NIP209', 'nama_perusahaan' => 'Bandara Halim Perdanakusuma', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 2004], // Jakarta Timur
            ['nip_nas' => 'NIP210', 'nama_perusahaan' => 'Bandara Husein Sastranegara', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 2008], // Bandung
            // Media
            ['nip_nas' => 'NIP013', 'nama_perusahaan' => 'Media Group Indonesia', 'subsegment' => 'Media', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP014', 'nama_perusahaan' => 'Trans Media Corporation', 'subsegment' => 'Media', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP211', 'nama_perusahaan' => 'PT MNC Media', 'subsegment' => 'Media', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP212', 'nama_perusahaan' => 'PT Kompas Gramedia', 'subsegment' => 'Media', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            // Airlines
            ['nip_nas' => 'NIP213', 'nama_perusahaan' => 'PT Garuda Indonesia', 'subsegment' => 'Airlines', 'source_data' => 'TIBS-NP', 'idwitels' => 2007], // Tangerang
            ['nip_nas' => 'NIP214', 'nama_perusahaan' => 'PT Lion Air', 'subsegment' => 'Airlines', 'source_data' => 'TIBS-NP', 'idwitels' => 2007], // Tangerang
            ['nip_nas' => 'NIP215', 'nama_perusahaan' => 'PT AirAsia Indonesia', 'subsegment' => 'Airlines', 'source_data' => 'TIBS-NP', 'idwitels' => 2007], // Tangerang
            // OLO (Other Licensed Operator)
            ['nip_nas' => 'NIP216', 'nama_perusahaan' => 'PT XL Axiata', 'subsegment' => 'OLO', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP217', 'nama_perusahaan' => 'PT Indosat Ooredoo', 'subsegment' => 'OLO', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            // Professional Service
            ['nip_nas' => 'NIP218', 'nama_perusahaan' => 'PT Deloitte Indonesia', 'subsegment' => 'Professional Service', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP219', 'nama_perusahaan' => 'PT KPMG Indonesia', 'subsegment' => 'Professional Service', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP220', 'nama_perusahaan' => 'PT McKinsey Indonesia', 'subsegment' => 'Professional Service', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            // Tourism and MICE
            ['nip_nas' => 'NIP221', 'nama_perusahaan' => 'Hotel Mulia Senayan', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP222', 'nama_perusahaan' => 'Hotel Grand Hyatt Jakarta', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 2001], // Jakarta
            ['nip_nas' => 'NIP223', 'nama_perusahaan' => 'Trans Luxury Hotel Bandung', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 2008], // Bandung
            
            // ========== TREG3 - JATENG & DIY (Semarang, Yogyakarta, Solo) ==========
            // PTN
            ['nip_nas' => 'NIP005', 'nama_perusahaan' => 'Universitas Gadjah Mada', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 3005], // Yogyakarta
            ['nip_nas' => 'NIP301', 'nama_perusahaan' => 'Universitas Diponegoro', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 3001], // Semarang
            ['nip_nas' => 'NIP302', 'nama_perusahaan' => 'Universitas Sebelas Maret', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 3002], // Solo
            ['nip_nas' => 'NIP303', 'nama_perusahaan' => 'Universitas Negeri Yogyakarta', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 3005], // Yogyakarta
            // PTS
            ['nip_nas' => 'NIP012', 'nama_perusahaan' => 'Universitas Islam Indonesia', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 3005], // Yogyakarta
            ['nip_nas' => 'NIP304', 'nama_perusahaan' => 'Universitas Muhammadiyah Yogyakarta', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 3005], // Yogyakarta
            ['nip_nas' => 'NIP305', 'nama_perusahaan' => 'Universitas Dian Nuswantoro', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 3001], // Semarang
            // Hospital
            ['nip_nas' => 'NIP006', 'nama_perusahaan' => 'RS Sardjito Yogyakarta', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 3005], // Yogyakarta
            ['nip_nas' => 'NIP306', 'nama_perusahaan' => 'RS Dr. Kariadi Semarang', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 3001], // Semarang
            ['nip_nas' => 'NIP307', 'nama_perusahaan' => 'RS Dr. Moewardi Solo', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 3002], // Solo
            // Airport
            ['nip_nas' => 'NIP308', 'nama_perusahaan' => 'Bandara Ahmad Yani Semarang', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 3001], // Semarang
            ['nip_nas' => 'NIP309', 'nama_perusahaan' => 'Bandara Adisucipto Yogyakarta', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 3005], // Yogyakarta
            ['nip_nas' => 'NIP310', 'nama_perusahaan' => 'Bandara Adi Soemarmo Solo', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 3002], // Solo
            // Tourism and MICE
            ['nip_nas' => 'NIP311', 'nama_perusahaan' => 'Hotel Phoenix Yogyakarta', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 3005], // Yogyakarta
            ['nip_nas' => 'NIP312', 'nama_perusahaan' => 'Borobudur Convention Center', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 3005], // Yogyakarta
            
            // ========== TREG4 - JAWA TIMUR (Surabaya, Malang) ==========
            // PTN
            ['nip_nas' => 'NIP008', 'nama_perusahaan' => 'Universitas Airlangga', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 4001], // Surabaya
            ['nip_nas' => 'NIP401', 'nama_perusahaan' => 'Institut Teknologi Sepuluh Nopember', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 4001], // Surabaya
            ['nip_nas' => 'NIP402', 'nama_perusahaan' => 'Universitas Brawijaya', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 4002], // Malang
            ['nip_nas' => 'NIP403', 'nama_perusahaan' => 'Universitas Negeri Surabaya', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 4001], // Surabaya
            // PTS
            ['nip_nas' => 'NIP404', 'nama_perusahaan' => 'Universitas Surabaya', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 4001], // Surabaya
            ['nip_nas' => 'NIP405', 'nama_perusahaan' => 'Universitas Petra', 'subsegment' => 'PTS', 'source_data' => 'NGTMA', 'idwitels' => 4001], // Surabaya
            // Hospital
            ['nip_nas' => 'NIP406', 'nama_perusahaan' => 'RS Dr. Soetomo Surabaya', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 4001], // Surabaya
            ['nip_nas' => 'NIP407', 'nama_perusahaan' => 'RS Dr. Saiful Anwar Malang', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 4002], // Malang
            ['nip_nas' => 'NIP408', 'nama_perusahaan' => 'RS Siloam Surabaya', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 4001], // Surabaya
            // Airport
            ['nip_nas' => 'NIP009', 'nama_perusahaan' => 'Bandara Juanda Surabaya', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 4001], // Surabaya
            ['nip_nas' => 'NIP409', 'nama_perusahaan' => 'Bandara Abdul Rachman Saleh', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 4002], // Malang
            // Tourism and MICE
            ['nip_nas' => 'NIP410', 'nama_perusahaan' => 'Hotel JW Marriott Surabaya', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 4001], // Surabaya
            ['nip_nas' => 'NIP411', 'nama_perusahaan' => 'Grand City Convention Surabaya', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 4001], // Surabaya
            
            // ========== TREG5 - BALI & NUSA TENGGARA (Denpasar, Mataram) ==========
            // PTN
            ['nip_nas' => 'NIP501', 'nama_perusahaan' => 'Universitas Udayana', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 5001], // Bali
            ['nip_nas' => 'NIP502', 'nama_perusahaan' => 'Universitas Pendidikan Ganesha', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 5001], // Bali
            ['nip_nas' => 'NIP503', 'nama_perusahaan' => 'Universitas Mataram', 'subsegment' => 'PTN', 'source_data' => 'NGTMA', 'idwitels' => 5002], // NTB
            // Hospital
            ['nip_nas' => 'NIP504', 'nama_perusahaan' => 'RS Sanglah Denpasar', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 5001], // Bali
            ['nip_nas' => 'NIP505', 'nama_perusahaan' => 'RSUD Provinsi NTB', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 5002], // NTB
            ['nip_nas' => 'NIP506', 'nama_perusahaan' => 'BIMC Hospital Bali', 'subsegment' => 'Hospital', 'source_data' => 'SISKA', 'idwitels' => 5001], // Bali
            // Airport
            ['nip_nas' => 'NIP015', 'nama_perusahaan' => 'Bandara Ngurah Rai Bali', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 5001], // Bali
            ['nip_nas' => 'NIP507', 'nama_perusahaan' => 'Bandara Lombok Internasional', 'subsegment' => 'Airport', 'source_data' => 'TIBS-NP', 'idwitels' => 5002], // NTB
            // Tourism and MICE
            ['nip_nas' => 'NIP508', 'nama_perusahaan' => 'Bali International Convention Centre', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 5001], // Bali
            ['nip_nas' => 'NIP509', 'nama_perusahaan' => 'The Mulia Resort Bali', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 5001], // Bali
            ['nip_nas' => 'NIP510', 'nama_perusahaan' => 'Hotel Ritz Carlton Bali', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 5001], // Bali
            ['nip_nas' => 'NIP511', 'nama_perusahaan' => 'Nusa Dua Convention Center', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 5001], // Bali
            ['nip_nas' => 'NIP512', 'nama_perusahaan' => 'Sheraton Senggigi Resort Lombok', 'subsegment' => 'Tourism and MICE', 'source_data' => 'TIBS-NP', 'idwitels' => 5002], // NTB
        ];

            foreach ($companies as $company) {
                DB::table('companies')->insert(array_merge($company, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->command->info('✅ Companies inserted: ' . count($companies) . ' companies with witel assignments');
        } else {
            $this->command->info('⚠️  Companies already exist, skipped.');
        }

        // 2. INSERT ACCOUNT MANAGER - COMPANY ASSIGNMENTS (if empty)
        // STRATEGI: Pastikan SETIAP REGION (HQ TREG2 + TREG1-5) punya minimal 2 AM dengan berbagai segment
        // Ini penting untuk Performance AM Dashboard yang grouping by region
        if (DB::table('account_manager_company')->count() == 0) {
            $assignments = [
                // ========== HQ TREG2 (HEADQUARTERS) - 6 assignments dengan 3 AM ==========
                // HQ Corporate (witel 2101) - NIK 210001
                ['nik_am' => '210001', 'nip_nas' => 'NIP016', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'HQ-TWS'],
                ['nik_am' => '210001', 'nip_nas' => 'NIP017', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'HQ-TWS'],
                // HQ Enterprise (witel 2102) - NIK 210002
                ['nik_am' => '210002', 'nip_nas' => 'NIP018', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'HQ-TWS'],
                ['nik_am' => '210002', 'nip_nas' => 'NIP019', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'HQ-TWS'],
                // HQ Government (witel 2103) - NIK 210003
                ['nik_am' => '210003', 'nip_nas' => 'NIP020', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'HQ-TWS'],
                ['nik_am' => '210003', 'nip_nas' => 'NIP021', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'HQ-TWS'],
                
                // ========== TREG1 (SUMATERA) - 3 AM dengan 3 segment ==========
                ['nik_am' => '810001', 'nip_nas' => 'NIP001', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                ['nik_am' => '810002', 'nip_nas' => 'NIP001', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'Hospital'], // Shared
                ['nik_am' => '810003', 'nip_nas' => 'NIP001', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'Airport'], // Shared
                
                // ========== TREG2 (JAKARTA & JABAR) - 9 AM dengan berbagai segment ==========
                // Jakarta AMs
                ['nik_am' => '820001', 'nip_nas' => 'NIP002', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Hospital'],
                ['nik_am' => '820002', 'nip_nas' => 'NIP003', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                ['nik_am' => '820003', 'nip_nas' => 'NIP013', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Media'],
                ['nik_am' => '820004', 'nip_nas' => 'NIP014', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Media'],
                // Jakarta Barat AM
                ['nik_am' => '820005', 'nip_nas' => 'NIP011', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'PTS'],
                // Tangerang AM
                ['nik_am' => '820006', 'nip_nas' => 'NIP004', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Airport'],
                // Bandung AMs
                ['nik_am' => '820007', 'nip_nas' => 'NIP007', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                ['nik_am' => '820008', 'nip_nas' => 'NIP010', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Hospital'],
                ['nik_am' => '820008', 'nip_nas' => 'NIP011', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'PTS'], // Shared

                // OLO (Jakarta) - NIK 820009
                ['nik_am' => '820009', 'nip_nas' => 'NIP216', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'OLO'],
                ['nik_am' => '820009', 'nip_nas' => 'NIP217', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'OLO'],

                // Airlines (Tangerang) - NIK 820010
                ['nik_am' => '820010', 'nip_nas' => 'NIP213', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Airlines'],
                ['nik_am' => '820010', 'nip_nas' => 'NIP214', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Airlines'],
                ['nik_am' => '820010', 'nip_nas' => 'NIP215', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Airlines'],

                // Professional Service (Jakarta) - NIK 820001 (Dewi Lestari - SAM Jakarta)
                ['nik_am' => '820001', 'nip_nas' => 'NIP218', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Professional Service'],
                ['nik_am' => '820001', 'nip_nas' => 'NIP219', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Professional Service'],
                ['nik_am' => '820001', 'nip_nas' => 'NIP220', 'proporsi' => 100.00, 'pembagian' => 'MULTI', 'segment' => 'Professional Service'],
                
                // ========== TREG3 (JATENG & DIY) - 4 AM dengan berbagai segment ==========
                // Semarang AM
                ['nik_am' => '830001', 'nip_nas' => 'NIP012', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTS'],
                // Yogyakarta AMs
                ['nik_am' => '830002', 'nip_nas' => 'NIP005', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                ['nik_am' => '830003', 'nip_nas' => 'NIP006', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Hospital'],
                ['nik_am' => '830004', 'nip_nas' => 'NIP005', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'Airport'], // Shared
                
                // ========== TREG4 (JAWA TIMUR) - 3 AM dengan berbagai segment ==========
                ['nik_am' => '840001', 'nip_nas' => 'NIP008', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'PTN'],
                ['nik_am' => '840002', 'nip_nas' => 'NIP009', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Airport'],
                ['nik_am' => '840003', 'nip_nas' => 'NIP008', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'Hospital'], // Shared
                
                // ========== TREG5 (BALI & NUSA TENGGARA) - 3 AM dengan berbagai segment ==========
                ['nik_am' => '850001', 'nip_nas' => 'NIP015', 'proporsi' => 100.00, 'pembagian' => 'SINGLE', 'segment' => 'Airport'],
                ['nik_am' => '850002', 'nip_nas' => 'NIP015', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'PTN'], // Shared
                ['nik_am' => '850003', 'nip_nas' => 'NIP015', 'proporsi' => 50.00, 'pembagian' => 'MULTI', 'segment' => 'Hospital'], // Shared
            ];

            foreach ($assignments as $assignment) {
                DB::table('account_manager_company')->insert(array_merge($assignment, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->command->info('✅ AM-Company assignments inserted: ' . count($assignments) . ' assignments');
            $this->command->info('   📊 Coverage: HQ TREG2(6 assignments, 3 AM), TREG1(3 AM), TREG2(9 AM), TREG3(4 AM), TREG4(3 AM), TREG5(3 AM)');
        } else {
            $this->command->info('⚠️  AM-Company assignments already exist, skipped.');
        }

        // 3. INSERT GROUP1-4 HIERARCHY (Revenue Breakdown Structure)
        // NOTE: Revenue realisasi now stored in group4 (leaf nodes), not in revenues table
        // This will create hierarchical breakdown: Companies -> Group1 -> Group2 -> Group3 -> Group4
        $this->command->info('🔄 Seeding Group1-4 hierarchical revenue breakdown...');
        $this->call(GroupBreakdownSeeder::class);

        // 4. INSERT LINI WAKTU (Quarterly periods for 2024-2025)
        $liniWaktu = [];
        $quarters = [
            'Q1' => ['bulan_awal' => '-01-01', 'bulan_akhir' => '-03-31'],
            'Q2' => ['bulan_awal' => '-04-01', 'bulan_akhir' => '-06-30'],
            'Q3' => ['bulan_awal' => '-07-01', 'bulan_akhir' => '-09-30'],
            'Q4' => ['bulan_awal' => '-10-01', 'bulan_akhir' => '-12-31'],
        ];

        // Get all Account Manager NIKs from database untuk generate lini_waktu
        // NOTE: Generate untuk semua AM agar semua region punya data
        $sampleAMs = DB::table('account_managers')->pluck('nik')->toArray();
        $years = [2023, 2024, 2025];

        foreach ($sampleAMs as $nikAm) {
            foreach ($years as $year) {
                foreach ($quarters as $quartal => $dates) {
                    $liniWaktu[] = [
                        'nik_am' => $nikAm,
                        'tahun' => $year,
                        'quartal' => $quartal,
                        'bulan_awal' => $year . $dates['bulan_awal'],
                        'bulan_akhir' => $year . $dates['bulan_akhir'],
                        
                        /**
                         * PERCENTAGE CONSTRAINTS (Auto-validated by LiniWaktu Model)
                         * 
                         * RULE 1: percentage_result + percentage_proses = 100%
                         * RULE 2: Sum of result sub-percentages = percentage_result
                         * RULE 3: Sum of process sub-percentages = percentage_proses
                         * 
                         * See: LINI_WAKTU_PERCENTAGE_CONSTRAINTS.md for details
                         */
                        
                        // CONSTRAINT: percentage_result + percentage_proses = 100%
                        'percentage_result' => 70.000,        // Bobot result dalam total score (70%)
                        'percentage_proses' => 30.000,        // Bobot process dalam total score (30%)
                        
                        // Breakdown Result KPI (CONSTRAINT: total harus = 70%)
                        'percentage_revenue' => 20.000,       // Revenue: 20%
                        'percentage_scaling' => 15.000,       // Scaling: 15%
                        'percentage_datin' => 10.000,         // Datin: 10%
                        'percentage_hsi' => 5.000,            // HSI: 5%
                        'percentage_wireline' => 5.000,       // Wireline: 5%
                        'percentage_wifi' => 5.000,           // WiFi: 5%
                        'percentage_cyc' => 3.000,            // CYC: 3%
                        'percentage_cr' => 3.000,             // CR: 3%
                        'percentage_profit' => 2.000,         // Profit: 2%
                        'percentage_customer' => 2.000,       // Customer: 2%
                        // Total Result = 70% ✅
                        
                        // Breakdown Process KPI (CONSTRAINT: total harus = 30%)
                        'percentage_maps' => 15.000,          // MAPS: 15%
                        'percentage_lop' => 10.000,           // LOP: 10%
                        'percentage_capability' => 3.000,     // Capability: 3%
                        'percentage_cc' => 2.000,             // CC: 2%
                        // Total Process = 30% ✅
                        
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('lini_waktu')->insert($liniWaktu);
        $this->command->info('✅ Lini Waktu inserted: ' . count($liniWaktu) . ' quarterly periods');

        // 5. INSERT TARGET ACCOUNT M (KPI Targets)
        // NOTE: PERUBAHAN PENTING - Targets sekarang dikaitkan dengan account_manager_company_id
        // Setiap assignment AM-Company akan punya target per year dan quarter
        // Struktur: account_manager_company_id → target (One-to-One per period)
        // t_revenue, t_scalling, t_lop, t_ngtma, t_sustain: decimal(15,2) - nilai absolut
        // t_cyc, t_cr, t_profit, t_maps: decimal(7,3) - persentase (0-100%)
        // t_datin, t_wifi: decimal(7,2) - nilai absolut
        // t_hsi, t_wireline, t_nps, t_capability, t_cc: decimal(5,2) - nilai absolut atau persentase
        
        $targets = [];
        
        // Get all AM-Company assignments dengan ID-nya
        $amCompanyAssignments = DB::table('account_manager_company')
            ->select('id', 'nik_am', 'nip_nas')
            ->orderBy('id')
            ->get();
        
        // Generate base targets untuk setiap assignment
        $baseRevenueRange = [40000000000, 50000000000, 60000000000, 70000000000, 80000000000, 90000000000, 100000000000];
        $years = [2023, 2024, 2025];
        $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
        
        foreach ($amCompanyAssignments as $index => $assignment) {
            // Base revenue varies by assignment index
            $baseRevenue = $baseRevenueRange[$index % count($baseRevenueRange)];
            $baseScaling = $baseRevenue * 0.5; // Scaling = 50% of revenue
            
            // Generate targets for each year and quarter for this AM-Company assignment
            foreach ($years as $year) {
                // Yearly increase factor (2023: 0.88, 2024: 1.0, 2025: 1.12)
                $yearFactor = match($year) {
                    2023 => 0.88,
                    2024 => 1.0,
                    2025 => 1.12,
                    default => 1.0
                };
                
                foreach ($quarters as $quartal) {
                    // Quarterly variation (Q1: 90%, Q2: 100%, Q3: 105%, Q4: 110%)
                    $quarterFactor = match($quartal) {
                        'Q1' => 0.90,
                        'Q2' => 1.00,
                        'Q3' => 1.05,
                        'Q4' => 1.10,
                    };
                    
                    // Calculate final target with year and quarter factors
                    $finalRevenue = $baseRevenue * $yearFactor * $quarterFactor;
                    $finalScaling = $baseScaling * $yearFactor * $quarterFactor;
                    
                    $targets[] = [
                        'account_manager_company_id' => $assignment->id, // NEW: FK to account_manager_company
                        't_revenue' => round($finalRevenue, 2),
                        't_scalling' => round($finalScaling, 2),
                        // NOTE: Adjusted values untuk decimal limits
                        't_datin' => round((10000.00 + ($index * 1200)) * $quarterFactor, 2),   // decimal(7,2) - max 99999.99
                        't_hsi' => round((100.00 + ($index * 15)) * $quarterFactor, 2),         // decimal(5,2) - max 999.99
                        't_wireline' => round((50.00 + ($index * 10)) * $quarterFactor, 2),     // decimal(5,2) - max 999.99
                        't_wifi' => round((15000.00 + ($index * 1500)) * $quarterFactor, 2),    // decimal(7,2) - max 99999.99
                        't_cyc' => 85.000 + ($index * 2),
                        't_cr' => 90.000 + ($index * 1.5),
                        't_profit' => 75.000 + ($index * 2),
                        't_nps' => 75.00,
                        't_maps' => 80.000 + ($index * 1),
                        't_lop' => round(10000000000.00 * $quarterFactor, 2),
                        't_capability' => 90.00,
                        't_cc' => 88.00,
                        't_ngtma' => round(15000000000.00 * $quarterFactor, 2),
                        't_sustain' => round(12000000000.00 * $quarterFactor, 2),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('target_account_m')->insert($targets);
        $this->command->info('✅ Targets inserted: ' . count($targets) . ' target records (per AM-Company x Year x Quarter)');

        // 6. INSERT LINI WAKTU - TARGET PIVOT (with realisasi dan achievement)
        // NOTE: PERUBAHAN LOGIKA - Sekarang mapping berdasarkan AM-Company + Year + Quarter
        // Struktur: 
        // - lini_waktu: berdasarkan nik_am + year + quarter (43 AMs × 2 years × 4 quarters = 344)
        // - target: berdasarkan account_manager_company_id (16 assignments × 2 years × 4 quarters = 128)
        // - Satu lini_waktu bisa punya multiple targets (karena 1 AM bisa handle multiple companies)
        
        $liniWaktuTarget = [];
        
        // Get all lini_waktu records ordered by AM, year, quarter
        $liniWaktuRecords = DB::table('lini_waktu')
            ->orderBy('nik_am')
            ->orderBy('tahun')
            ->orderBy('quartal')
            ->get();
        
        // Get all targets with their AM-Company info, ordered by assignment then period
        $targetRecords = DB::table('target_account_m as t')
            ->join('account_manager_company as amc', 't.account_manager_company_id', '=', 'amc.id')
            ->select('t.*', 'amc.nik_am', 'amc.nip_nas', 'amc.id as am_company_id')
            ->orderBy('amc.id')  // Order by AM-Company assignment
            ->orderBy('t.id')     // Then by target ID (which follows year-quarter sequence)
            ->get();
        
        // Create mapping: Group targets by AM NIK, Year, and Quarter
        // Structure: $targetsByAm[nik_am][year][quarter] = [target1, target2, ...]
        $targetsByAm = [];
        $years = [2023, 2024, 2025];
        $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
        
        // Group targets by AM-Company assignment
        $targetsByAssignment = [];
        foreach ($targetRecords as $target) {
            $assignmentId = $target->am_company_id;
            if (!isset($targetsByAssignment[$assignmentId])) {
                $targetsByAssignment[$assignmentId] = [];
            }
            $targetsByAssignment[$assignmentId][] = $target;
        }
        
        // Now map each assignment's targets to year-quarter periods
        foreach ($targetsByAssignment as $assignmentId => $assignmentTargets) {
            $targetIndex = 0;
            
            // Each assignment has 12 targets (3 years × 4 quarters)
            foreach ($years as $year) {
                foreach ($quarters as $quarter) {
                    if ($targetIndex < count($assignmentTargets)) {
                        $target = $assignmentTargets[$targetIndex];
                        $amNik = $target->nik_am;
                        
                        // Initialize nested arrays if not exists
                        if (!isset($targetsByAm[$amNik])) {
                            $targetsByAm[$amNik] = [];
                        }
                        if (!isset($targetsByAm[$amNik][$year])) {
                            $targetsByAm[$amNik][$year] = [];
                        }
                        if (!isset($targetsByAm[$amNik][$year][$quarter])) {
                            $targetsByAm[$amNik][$year][$quarter] = [];
                        }
                        
                        // Add target to this AM's period
                        $targetsByAm[$amNik][$year][$quarter][] = $target;
                        $targetIndex++;
                    }
                }
            }
        }
        
        // Now match lini_waktu with targets
        foreach ($liniWaktuRecords as $liniWaktu) {
            $amNik = $liniWaktu->nik_am;
            $year = $liniWaktu->tahun;
            $quarter = $liniWaktu->quartal;
            
            // Get all targets for this AM in this period
            if (isset($targetsByAm[$amNik][$year][$quarter])) {
                $targets = $targetsByAm[$amNik][$year][$quarter];
                
                // Create realisasi for each target (AM might have multiple companies)
                foreach ($targets as $target) {
                    // Calculate realisasi (achievement 85-110%)
                    $achievementRate = (rand(85, 110) / 100);
                    
                    /**
                     * ACHIEVEMENT CONSTRAINTS:
                     * 1. ach_result MUST equal sum of 10 result achievement fields
                     * 2. ach_proses MUST equal sum of 4 process achievement fields
                     * 
                     * Important: decimal(6,3) max = 999.999%
                     * - Result: 10 fields, total must be < 1000% → each field 7-10% (total 700-1000%)
                     * - Process: 4 fields, total must be < 1000% → each field 20-25% (total 800-1000%)
                     * 
                     * See: LINI_WAKTU_TARGET_ACHIEVEMENT_CONSTRAINTS.md for details
                     */
                    
                    // Generate individual achievement percentages (scaled for database limit)
                    // Result fields: 10 fields * 8.5% avg = 850% total (safe under 999.999)
                    $achRevenuePlan = round(rand(700, 1000) / 100, 3);  // 7.000 - 10.000%
                    $achScaling = round(rand(700, 1000) / 100, 3);
                    $achSalesDatin = round(rand(700, 1000) / 100, 3);
                    $achHsi = round(rand(700, 1000) / 100, 3);
                    $achWireline = round(rand(700, 1000) / 100, 3);
                    $achWifi = round(rand(700, 1000) / 100, 3);
                    $achCyc = round(rand(700, 1000) / 100, 3);
                    $achCr = round(rand(700, 1000) / 100, 3);
                    $achProfit = round(rand(700, 1000) / 100, 3);
                    $achNps = round(rand(700, 1000) / 100, 3);
                    
                    // Process fields: 4 fields * 22.5% avg = 900% total (safe under 999.999)
                    $achMaps = round(rand(2000, 2500) / 100, 3);  // 20.000 - 25.000%
                    $achLop = round(rand(2000, 2500) / 100, 3);
                    $achCapability = round(rand(2000, 2500) / 100, 3);
                    $achCc = round(rand(2000, 2500) / 100, 3);
                    
                    // Calculate ach_result (sum of 10 result fields)
                    $achResult = round(
                        $achRevenuePlan + $achScaling + $achSalesDatin + $achHsi + 
                        $achWireline + $achWifi + $achCyc + $achCr + $achProfit + $achNps,
                        3
                    );
                    
                    // Calculate ach_proses (sum of 4 process fields)
                    $achProses = round(
                        $achMaps + $achLop + $achCapability + $achCc,
                        3
                    );
                    
                    $liniWaktuTarget[] = [
                        'lini_waktu_id' => $liniWaktu->id,
                        'target_id' => $target->id,
                        
                        // REALISASI - Nilai Absolut (dikalikan dengan achievement rate)
                        'r_revenue' => round($target->t_revenue * $achievementRate, 2),
                        'r_scalling' => round($target->t_scalling * $achievementRate, 2),
                        'r_sustain' => round($target->t_sustain * $achievementRate, 2),
                        'r_ngtma' => round($target->t_ngtma * $achievementRate, 2),
                        'r_datin' => round($target->t_datin * $achievementRate, 2),
                        'r_hsi' => round($target->t_hsi * $achievementRate, 2),
                        'r_wireline' => round($target->t_wireline * $achievementRate, 2),
                        'r_wifi' => round($target->t_wifi * $achievementRate, 2),
                        'r_nps' => round($target->t_nps * $achievementRate, 2),
                        'r_lop' => round($target->t_lop * $achievementRate, 2),
                        'r_capability' => round($target->t_capability * $achievementRate, 2),
                        'r_cc' => round($target->t_cc * $achievementRate, 2),
                        
                        // REALISASI - Persentase (target + variasi)
                        'r_cyc' => min(100, max(0, $target->t_cyc + rand(-5, 10))),
                        'r_cr' => min(100, max(0, $target->t_cr + rand(-5, 10))),
                        'r_profit' => min(100, max(0, $target->t_profit + rand(-5, 10))),
                        'r_maps' => min(100, max(0, $target->t_maps + rand(-5, 10))),
                        
                        // ACHIEVEMENT PERCENTAGE - Result (10 fields)
                        'ach_revenue_plan' => $achRevenuePlan,
                        'ach_scaling' => $achScaling,
                        'ach_sales_datin' => $achSalesDatin,
                        'ach_hsi' => $achHsi,
                        'ach_wireline' => $achWireline,
                        'ach_wifi' => $achWifi,
                        'ach_cyc' => $achCyc,
                        'ach_cr' => $achCr,
                        'ach_profit' => $achProfit,
                        'ach_nps' => $achNps,
                        
                        // ACHIEVEMENT PERCENTAGE - Process (4 fields)
                        'ach_maps' => $achMaps,
                        'ach_lop' => $achLop,
                        'ach_capability' => $achCapability,
                        'ach_cc' => $achCc,
                        
                        // OVERALL ACHIEVEMENT (calculated from sums above)
                        'ach_result' => $achResult,
                        'ach_proses' => $achProses,
                        
                        // NKI ADJUSTMENT (realistic range: 70-130% with more variation)
                        'nki_adjustment' => round(rand(70, 130) + (rand(0, 999) / 1000), 3),
                        
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('lini_waktu_target')->insert($liniWaktuTarget);
        $this->command->info('✅ Lini Waktu-Target pivot inserted: ' . count($liniWaktuTarget) . ' realisasi records');

        // ========== DATA VALIDATION & SUMMARY ==========
        $this->command->info('');
        $this->command->info('📊 ========== DATA DISTRIBUTION SUMMARY ==========');
        
        // Validasi 1: Coverage per Region
        $regionCoverage = DB::table('account_managers as am')
            ->join('witels as w', 'am.idwitels', '=', 'w.idwitels')
            ->join('regions as r', 'w.region_id', '=', 'r.id')
            ->select('r.name as region', DB::raw('COUNT(DISTINCT am.nik) as am_count'))
            ->groupBy('r.id', 'r.name')
            ->orderBy('r.name')
            ->get();
        
        $this->command->info('');
        $this->command->info('✅ Region Coverage (Account Managers):');
        foreach ($regionCoverage as $region) {
            $this->command->info("   • {$region->region}: {$region->am_count} AM");
        }
        
        // Validasi 2: Data per Year & Quarter
        $periodCoverage = DB::table('lini_waktu')
            ->select('tahun', 'quartal', DB::raw('COUNT(*) as count'))
            ->groupBy('tahun', 'quartal')
            ->orderBy('tahun')
            ->orderBy('quartal')
            ->get();
        
        $this->command->info('');
        $this->command->info('✅ Period Coverage (Lini Waktu):');
        foreach ($periodCoverage as $period) {
            $this->command->info("   • {$period->tahun} {$period->quartal}: {$period->count} records");
        }
        
        // Validasi 3: Target & Realisasi per Region
        $regionTargets = DB::table('lini_waktu_target as lwt')
            ->join('lini_waktu as lw', 'lwt.lini_waktu_id', '=', 'lw.id')
            ->join('account_managers as am', 'lw.nik_am', '=', 'am.nik')
            ->join('witels as w', 'am.idwitels', '=', 'w.idwitels')
            ->join('regions as r', 'w.region_id', '=', 'r.id')
            ->select(
                'r.name as region',
                'lw.tahun',
                'lw.quartal',
                DB::raw('COUNT(*) as target_count'),
                DB::raw('AVG(lwt.ach_result) as avg_result'),
                DB::raw('AVG(lwt.ach_proses) as avg_process')
            )
            ->groupBy('r.id', 'r.name', 'lw.tahun', 'lw.quartal')
            ->orderBy('r.name')
            ->orderBy('lw.tahun')
            ->orderBy('lw.quartal')
            ->get();
        
        $this->command->info('');
        $this->command->info('✅ Target & Achievement Distribution:');
        $currentRegion = '';
        foreach ($regionTargets as $data) {
            if ($currentRegion !== $data->region) {
                $currentRegion = $data->region;
                $this->command->info("   📍 {$data->region}:");
            }
            $avgResult = number_format($data->avg_result, 2);
            $avgProcess = number_format($data->avg_process, 2);
            $this->command->info("      {$data->tahun} {$data->quartal}: {$data->target_count} targets | Avg Result: {$avgResult}% | Avg Process: {$avgProcess}%");
        }
        
        // Validasi 4: Segment Distribution
        $segmentDistribution = DB::table('account_manager_company')
            ->select('segment', DB::raw('COUNT(*) as count'))
            ->groupBy('segment')
            ->orderBy('segment')
            ->get();
        
        $this->command->info('');
        $this->command->info('✅ Segment Distribution:');
        foreach ($segmentDistribution as $segment) {
            $this->command->info("   • {$segment->segment}: {$segment->count} assignments");
        }
        
        // Final Summary
        $totalCompanies = DB::table('companies')->count();
        $totalAMs = DB::table('account_managers')->count();
        $totalAssignments = DB::table('account_manager_company')->count();
        $totalTargets = DB::table('target_account_m')->count();
        $totalRealisasi = DB::table('lini_waktu_target')->count();
        
        $this->command->info('');
        $this->command->info('🎉 ========== SEEDING COMPLETED SUCCESSFULLY! ==========');
        $this->command->info('   📊 Total Companies: ' . $totalCompanies);
        $this->command->info('   👥 Total Account Managers: ' . $totalAMs);
        $this->command->info('   🔗 Total AM-Company Assignments: ' . $totalAssignments);
        $this->command->info('   🎯 Total Targets: ' . $totalTargets);
        $this->command->info('   📈 Total Realisasi Records: ' . $totalRealisasi);
        $this->command->info('');
        $this->command->info('✅ Semua region memiliki data lengkap untuk Performance AM Dashboard!');
        $this->command->info('');
    }
}