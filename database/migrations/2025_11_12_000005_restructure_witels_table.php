<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Restructure Witels Table
 * 
 * PERUBAHAN BESAR:
 * 1. Ubah PRIMARY KEY dari 'id' (auto increment) menjadi 'idwitels' (int custom)
 * 2. Ubah nama column dari 'name' menjadi 'nama_witels'
 * 3. Tambah FOREIGN KEY 'region_id' untuk relasi ke regions
 * 
 * Struktur Baru:
 * - idwitels (INT, PRIMARY KEY) - ID WITEL custom (bukan auto increment)
 * - nama_witels (VARCHAR 25, NOT NULL) - Nama WITEL
 * - region_id (FK to regions) - Region tempat WITEL berada
 * 
 * Relasi:
 * - witels → regions (Many-to-One via region_id FK)
 * - witels ← account_managers (One-to-One, FK di account_managers)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Backup data witels yang sudah ada
        Schema::create('witels_backup_temp', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedBigInteger('region_id')->nullable();
            $table->timestamps();
        });
        
        // Copy data existing ke temporary
        DB::statement('INSERT INTO witels_backup_temp (id, name, region_id, created_at, updated_at)
                      SELECT id, name, region_id, created_at, updated_at
                      FROM witels');
        
        // STEP 2: Drop table witels lama
        Schema::dropIfExists('witels');
        
        // STEP 3: Create table witels dengan struktur baru
        Schema::create('witels', function (Blueprint $table) {
            // PRIMARY KEY: idwitels (INT, custom ID bukan auto increment)
            // ID ini akan di-define manual sesuai kode WITEL Telkom
            $table->unsignedBigInteger('idwitels')->primary()->comment('ID WITEL (Primary Key, custom)');
            
            // NAMA WITEL: Nama lengkap WITEL
            $table->string('nama_witels', 50)->comment('Nama WITEL');
            
            // FOREIGN KEY: Relasi Many-to-One dengan regions
            // Setiap WITEL harus berada di salah satu region
            $table->unsignedBigInteger('region_id')->comment('FK to regions - Region tempat WITEL berada');
            
            // TIMESTAMPS
            $table->timestamps();
            
            // INDEX: untuk performa query berdasarkan region
            $table->index('region_id', 'idx_witel_region');
            
            // FOREIGN KEY CONSTRAINT: region_id → regions.id
            $table->foreign('region_id')
                  ->references('id')
                  ->on('regions')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
        
        // STEP 4: Insert data witels sesuai struktur Telkom
        // Data ini adalah master data standar yang sudah final
        // ID menggunakan nomor urut 1-31 sesuai screenshot
        
        // Get region IDs
        $regions = DB::table('regions')->get()->keyBy('code');
        
        DB::table('witels')->insert([
            // TREG 1 - SUMATERA (6 witels: 1-6)
            ['idwitels' => 1, 'nama_witels' => 'WITEL ACEH', 'region_id' => $regions['TREG 1']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 2, 'nama_witels' => 'WITEL RIAU', 'region_id' => $regions['TREG 1']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 3, 'nama_witels' => 'WITEL SUMBAGSEL', 'region_id' => $regions['TREG 1']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 4, 'nama_witels' => 'WITEL SUMBAR JAMBI', 'region_id' => $regions['TREG 1']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 5, 'nama_witels' => 'WITEL LAMPUNG BENGKULU', 'region_id' => $regions['TREG 1']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 6, 'nama_witels' => 'WITEL SUMUT', 'region_id' => $regions['TREG 1']->id, 'created_at' => now(), 'updated_at' => now()],
            
            // TREG HQ 2 - JAKARTA HQ (5 witels: 7-11)
            ['idwitels' => 7, 'nama_witels' => 'WITEL JAKARTA INNER', 'region_id' => $regions['TREG HQ 2']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 8, 'nama_witels' => 'WITEL JAKARTA CENTRUM', 'region_id' => $regions['TREG HQ 2']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 9, 'nama_witels' => 'WITEL JAKARTA OUTER', 'region_id' => $regions['TREG HQ 2']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 10, 'nama_witels' => 'WITEL BANTEN', 'region_id' => $regions['TREG HQ 2']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 11, 'nama_witels' => 'WITEL PRIANGAN BRT', 'region_id' => $regions['TREG HQ 2']->id, 'created_at' => now(), 'updated_at' => now()],
            
            // TREG 2 - JAKARTA & JABAR (3 witels: 12-14)
            ['idwitels' => 12, 'nama_witels' => 'WITEL BEKASI KARAWANG', 'region_id' => $regions['TREG 2']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 13, 'nama_witels' => 'WITEL BANDUNG+D20', 'region_id' => $regions['TREG 2']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 14, 'nama_witels' => 'WITEL PRIANGAN TIMUR', 'region_id' => $regions['TREG 2']->id, 'created_at' => now(), 'updated_at' => now()],
            
            // TREG 3 - JATENG, JATIM & BALNUS (8 witels: 15-22)
            ['idwitels' => 15, 'nama_witels' => 'WITEL SEMARANG JATENG UTARA', 'region_id' => $regions['TREG 3']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 16, 'nama_witels' => 'WITEL YOGYAKARTA JATENG SELATAN', 'region_id' => $regions['TREG 3']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 17, 'nama_witels' => 'WITEL SOLO JATENG TIMUR', 'region_id' => $regions['TREG 3']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 18, 'nama_witels' => 'WITEL BALI', 'region_id' => $regions['TREG 3']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 19, 'nama_witels' => 'WITEL JATIM BARAT', 'region_id' => $regions['TREG 3']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 20, 'nama_witels' => 'WITEL NUSA TENGGARA', 'region_id' => $regions['TREG 3']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 21, 'nama_witels' => 'WITEL JATIM TIMUR', 'region_id' => $regions['TREG 3']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 22, 'nama_witels' => 'WITEL SURAMADU', 'region_id' => $regions['TREG 3']->id, 'created_at' => now(), 'updated_at' => now()],
            
            // TREG 4 - KALIMANTAN (4 witels: 23-26)
            ['idwitels' => 23, 'nama_witels' => 'WITEL KALITIMTARA', 'region_id' => $regions['TREG 4']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 24, 'nama_witels' => 'WITEL BALIKPAPAN', 'region_id' => $regions['TREG 4']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 25, 'nama_witels' => 'WITEL KALSELTENG', 'region_id' => $regions['TREG 4']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 26, 'nama_witels' => 'WITEL KALBAR', 'region_id' => $regions['TREG 4']->id, 'created_at' => now(), 'updated_at' => now()],
            
            // TREG 5 - KTI (5 witels: 27-31)
            ['idwitels' => 27, 'nama_witels' => 'WITEL SULBANGSEL', 'region_id' => $regions['TREG 5']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 28, 'nama_witels' => 'WITEL SULBANGTENG', 'region_id' => $regions['TREG 5']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 29, 'nama_witels' => 'WITEL SUMALUT', 'region_id' => $regions['TREG 5']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 30, 'nama_witels' => 'WITEL PAPUA', 'region_id' => $regions['TREG 5']->id, 'created_at' => now(), 'updated_at' => now()],
            ['idwitels' => 31, 'nama_witels' => 'WITEL PAPUA BARAT', 'region_id' => $regions['TREG 5']->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Drop temporary table
        Schema::dropIfExists('witels_backup_temp');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ROLLBACK: Kembalikan ke struktur lama
        
        // Drop table witels
        Schema::dropIfExists('witels');
        
        // Recreate dengan struktur lama (id auto increment)
        Schema::create('witels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
        
        // Data akan hilang saat rollback, harus di-seed ulang manual
    }
};
