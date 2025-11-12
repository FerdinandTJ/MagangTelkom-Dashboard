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
            $table->string('nama_witels', 25)->comment('Nama WITEL');
            
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
        
        // STEP 4: Migrate data dari backup ke struktur baru
        // Mapping old ID ke new idwitels (custom)
        // NOTE: Data akan di-seed ulang dengan ID yang benar di seeder
        // Untuk sementara, kita skip restore data lama karena struktur berubah drastis
        
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
