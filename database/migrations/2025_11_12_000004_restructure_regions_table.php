<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Restructure Regions Table
 * 
 * PERUBAHAN:
 * 1. Tambah column 'code' dengan ENUM untuk kode region standar
 * 2. Ubah 'name' menjadi lebih deskriptif
 * 3. Tambah 'description' untuk penjelasan tambahan
 * 
 * Struktur Baru:
 * - id (auto increment, primary key) - tetap
 * - code (ENUM) - Kode region: HQ TREG2, TREG1, TREG2, TREG3, TREG4, TREG5
 * - name (VARCHAR 25) - Nama region
 * - description (VARCHAR 25) - Deskripsi wilayah
 * 
 * Region List:
 * - HQ TREG2: Headquarters Telkom Regional 2
 * - TREG1: Telkom Regional 1 (Sumatera)
 * - TREG2: Telkom Regional 2 (Jakarta, Banten, Jabar)
 * - TREG3: Telkom Regional 3 (Jateng & DIY)
 * - TREG4: Telkom Regional 4 (Jatim)
 * - TREG5: Telkom Regional 5 (Bali, NTT, Kalimantan, Sulawesi, Maluku, Papua)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Backup data regions yang sudah ada
        $existingRegions = DB::table('regions')->get();
        
        // STEP 2: Drop table regions lama
        Schema::dropIfExists('regions');
        
        // STEP 3: Create table regions dengan struktur baru
        Schema::create('regions', function (Blueprint $table) {
            // PRIMARY KEY: id (auto increment)
            $table->id()->comment('Primary Key - Auto Increment');
            
            // CODE: Kode region dengan ENUM
            $table->enum('code', [
                'TREG 1',      // Regional 1 - Sumatera
                'TREG HQ 2',   // Regional HQ 2 - Jakarta HQ
                'TREG 2',      // Regional 2 - Jakarta & Jabar
                'TREG 3',      // Regional 3 - Jateng, Jatim & Balnus
                'TREG 4',      // Regional 4 - Kalimantan
                'TREG 5'       // Regional 5 - KTI
            ])->unique()->comment('Kode region (unique identifier)');
            
            // NAME: Nama lengkap region
            $table->string('name', 25)->comment('Nama region');
            
            // DESCRIPTION: Deskripsi wilayah cakupan
            $table->string('description', 25)->comment('Deskripsi wilayah cakupan region');
            
            // TIMESTAMPS
            $table->timestamps();
            
            // INDEX: untuk performa query berdasarkan code
            $table->index('code', 'idx_region_code');
        });
        
        // STEP 4: Seed data regions sesuai struktur Telkom
        // Data ini adalah master data standar yang sudah final
        DB::table('regions')->insert([
            [
                'code' => 'TREG 1',
                'name' => 'TREG 1',
                'description' => 'SUMATERA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG HQ 2',
                'name' => 'TREG HQ 2',
                'description' => 'JAKARTA HQ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG 2',
                'name' => 'TREG 2',
                'description' => 'JAKARTA & JABAR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG 3',
                'name' => 'TREG 3',
                'description' => 'JATENG, JATIM & BALNUS',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG 4',
                'name' => 'TREG 4',
                'description' => 'KALIMANTAN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG 5',
                'name' => 'TREG 5',
                'description' => 'KTI',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ROLLBACK: Kembalikan ke struktur lama
        Schema::dropIfExists('regions');
        
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->timestamps();
        });
        
        // Restore data lama jika perlu
        // Data akan hilang saat rollback, harus di-seed ulang manual
    }
};
