<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Restructure Companies Table
 * 
 * PERUBAHAN BESAR:
 * 1. Ubah PRIMARY KEY dari 'id' (auto increment) menjadi 'nip_nas' (varchar)
 * 2. Hapus columns: primary_region_id, primary_witel_id (tidak digunakan lagi)
 * 3. Pertahankan: nip_nas, nama_perusahaan, subsegment, source_data
 * 4. Relasi baru: companies ↔ account_managers (Many-to-Many via pivot)
 * 5. Relasi baru: companies → revenues (One-to-Many)
 * 
 * NOTE: Data existing akan di-preserve, hanya struktur yang berubah
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Buat table temporary untuk backup data companies
        Schema::create('companies_backup_temp', function (Blueprint $table) {
            $table->id();
            $table->string('nip_nas', 25);
            $table->string('nama_perusahaan', 55);
            $table->string('subsegment', 25);
            $table->string('source_data', 15);
            $table->timestamps();
        });
        
        // STEP 2: Copy semua data ke table temporary
        DB::statement('INSERT INTO companies_backup_temp (id, nip_nas, nama_perusahaan, subsegment, source_data, created_at, updated_at) 
                      SELECT id, nip_nas, nama_perusahaan, subsegment, source_data, created_at, updated_at 
                      FROM companies');
        
        // STEP 3: Drop table companies lama
        Schema::dropIfExists('companies');
        
        // STEP 4: Buat table companies dengan struktur baru
        Schema::create('companies', function (Blueprint $table) {
            // PRIMARY KEY: nip_nas (bukan auto increment lagi)
            // nip_nas adalah identifier unik dari perusahaan (Nomor Induk Perusahaan Nasional)
            $table->string('nip_nas', 25)->primary()->comment('Nomor Induk Perusahaan (Primary Key)');
            
            // BASIC INFO: Informasi dasar perusahaan
            $table->string('nama_perusahaan', 55)->comment('Nama lengkap perusahaan');
            
            // SUBSEGMENT: Kategori perusahaan
            // Contoh: PTN, PTS, Hospital, Airport, Bank, Government, dll
            $table->string('subsegment', 25)->nullable()->comment('Sub-segment perusahaan (PTN, PTS, Hospital, dll)');
            
            // SOURCE DATA: Sumber data perusahaan
            // Contoh: TIBS-NP, SISKA, NGTMA
            $table->string('source_data', 15)->comment('Sumber data perusahaan (TIBS-NP, SISKA, NGTMA)');
            
            // TIMESTAMPS: created_at, updated_at
            $table->timestamps();
            
            // INDEX: untuk performa query berdasarkan subsegment dan source_data
            $table->index('subsegment', 'idx_company_subsegment');
            $table->index('source_data', 'idx_company_source');
        });
        
        // STEP 5: Restore data dari table temporary ke table baru
        // Hanya copy data yang nip_nas nya unique (skip duplicate jika ada)
        // Menggunakan MAX() untuk aggregasi fields lain agar compatible dengan ONLY_FULL_GROUP_BY
        DB::statement('INSERT INTO companies (nip_nas, nama_perusahaan, subsegment, source_data, created_at, updated_at)
                      SELECT nip_nas, 
                             MAX(nama_perusahaan) as nama_perusahaan, 
                             MAX(subsegment) as subsegment, 
                             MAX(source_data) as source_data, 
                             MAX(created_at) as created_at, 
                             MAX(updated_at) as updated_at
                      FROM companies_backup_temp
                      GROUP BY nip_nas');
        
        // STEP 6: Create mapping table untuk migrasi revenues (digunakan di migration step 8)
        // Table ini menyimpan mapping company_id (old) → nip_nas (new)
        Schema::create('company_id_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('old_company_id')->primary();
            $table->string('nip_nas', 25);
            $table->index('nip_nas');
        });
        
        // Insert mapping dari backup
        DB::statement('INSERT INTO company_id_mapping (old_company_id, nip_nas)
                      SELECT id, nip_nas FROM companies_backup_temp');
        
        // STEP 7: Drop table temporary backup
        Schema::dropIfExists('companies_backup_temp');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ROLLBACK: Kembalikan struktur lama dengan id sebagai primary key
        
        // STEP 1: Buat table temporary untuk backup data
        Schema::create('companies_rollback_temp', function (Blueprint $table) {
            $table->string('nip_nas', 25);
            $table->string('nama_perusahaan', 55);
            $table->string('subsegment', 25);
            $table->string('source_data', 15);
            $table->timestamps();
        });
        
        // STEP 2: Copy data ke temporary
        DB::statement('INSERT INTO companies_rollback_temp (nip_nas, nama_perusahaan, subsegment, source_data, created_at, updated_at)
                      SELECT nip_nas, nama_perusahaan, subsegment, source_data, created_at, updated_at
                      FROM companies');
        
        // STEP 3: Drop table companies
        Schema::dropIfExists('companies');
        
        // STEP 4: Recreate dengan struktur lama (id auto increment)
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('nip_nas', 25)->unique();
            $table->string('nama_perusahaan', 55);
            $table->string('subsegment', 25);
            $table->string('source_data', 15);
            $table->foreignId('primary_region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('primary_witel_id')->nullable()->constrained('witels')->onDelete('set null');
            $table->timestamps();
        });
        
        // STEP 5: Restore data
        DB::statement('INSERT INTO companies (nip_nas, nama_perusahaan, subsegment, source_data, created_at, updated_at)
                      SELECT nip_nas, nama_perusahaan, subsegment, source_data, created_at, updated_at
                      FROM companies_rollback_temp');
        
        // STEP 6: Drop temporary
        Schema::dropIfExists('companies_rollback_temp');
    }
};
