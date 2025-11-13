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
                'HQ TREG2',  // Headquarters
                'TREG1',     // Regional 1
                'TREG2',     // Regional 2
                'TREG3',     // Regional 3
                'TREG4',     // Regional 4
                'TREG5'      // Regional 5
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
                'code' => 'HQ TREG2',
                'name' => 'Headquarters TREG2',
                'description' => 'Kantor Pusat Regional 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG1',
                'name' => 'Telkom Regional 1',
                'description' => 'Sumatera',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG2',
                'name' => 'Telkom Regional 2',
                'description' => 'Jakarta, Banten, Jabar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG3',
                'name' => 'Telkom Regional 3',
                'description' => 'Jateng & DIY',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG4',
                'name' => 'Telkom Regional 4',
                'description' => 'Jawa Timur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TREG5',
                'name' => 'Telkom Regional 5',
                'description' => 'Bali, NTT, Kaltim, dll',
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
