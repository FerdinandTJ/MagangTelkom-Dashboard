<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Restructure Revenues Table
 * 
 * PERUBAHAN:
 * 1. Hapus columns: region_id, witel_id (tidak digunakan lagi)
 * 2. Ubah FK company_id untuk merujuk ke companies.nip_nas (bukan companies.id)
 * 3. Tambah column 'note' dan 'target'
 * 4. Pertahankan: tahun, bulan, total_revenue
 * 
 * Struktur Baru:
 * - id (auto increment, primary key)
 * - nip_nas (FK to companies.nip_nas) - Company yang menghasilkan revenue
 * - tahun (year) - Tahun revenue
 * - bulan (tinyint) - Bulan revenue (1-12)
 * - total_revenue (decimal) - Total pendapatan
 * - note (varchar 45) - Catatan tambahan
 * - target (decimal) - Target revenue bulan ini
 * 
 * Relasi:
 * - revenues → companies (Many-to-One via nip_nas FK)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Backup data revenues yang sudah ada
        Schema::create('revenues_backup_temp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->year('tahun');
            $table->tinyInteger('bulan');
            $table->decimal('revenue', 15, 2)->default(0);
            $table->timestamps();
        });
        
        // Copy data existing ke temporary
        DB::statement('INSERT INTO revenues_backup_temp (id, company_id, tahun, bulan, revenue, created_at, updated_at)
                      SELECT id, company_id, tahun, bulan, revenue, created_at, updated_at
                      FROM revenues');
        
        // STEP 2: Drop table revenues lama
        Schema::dropIfExists('revenues');
        
        // STEP 3: Create table revenues dengan struktur baru
        Schema::create('revenues', function (Blueprint $table) {
            // PRIMARY KEY: id (auto increment)
            $table->id()->comment('Primary Key - Auto Increment');
            
            // FOREIGN KEY: nip_nas (merujuk ke companies.nip_nas, bukan id)
            // Relasi Many-to-One: Banyak revenue records untuk 1 company
            $table->string('nip_nas', 25)->comment('FK to companies.nip_nas - Company yang menghasilkan revenue');
            
            // TAHUN: Tahun revenue (YEAR type)
            $table->year('tahun')->comment('Tahun revenue');
            
            // BULAN: Bulan revenue (1-12)
            $table->tinyInteger('bulan')->unsigned()->comment('Bulan revenue (1-12)');
            
            // TOTAL REVENUE: Total pendapatan dalam rupiah
            $table->decimal('total_revenue', 16, 2)->default(0)->comment('Total revenue (Rp)');
            
            // NOTE: Catatan atau keterangan tambahan
            $table->string('note', 45)->nullable()->comment('Catatan tambahan');
            
            // TARGET: Target revenue untuk periode ini
            $table->decimal('target', 15, 2)->default(0)->comment('Target revenue periode ini (Rp)');
            
            // TIMESTAMPS
            $table->timestamps();
            
            // UNIQUE CONSTRAINT: Satu company hanya boleh punya 1 revenue per bulan/tahun
            $table->unique(['nip_nas', 'tahun', 'bulan'], 'unique_company_period');
            
            // INDEX: untuk performa query berdasarkan periode
            $table->index('tahun', 'idx_revenue_tahun');
            $table->index('bulan', 'idx_revenue_bulan');
            $table->index('nip_nas', 'idx_revenue_company');
            
            // FOREIGN KEY CONSTRAINT: nip_nas → companies.nip_nas
            $table->foreign('nip_nas')
                  ->references('nip_nas')
                  ->on('companies')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
        
        // STEP 4: Migrate data dari backup
        // Join dengan company_id_mapping untuk mendapatkan nip_nas dari company_id
        // NOTE: Ini akan skip data jika company_id tidak valid atau sudah tidak ada
        DB::statement('
            INSERT INTO revenues (nip_nas, tahun, bulan, total_revenue, target, created_at, updated_at)
            SELECT 
                m.nip_nas,
                r.tahun,
                r.bulan,
                SUM(r.revenue) as total_revenue,
                0 as target,
                MAX(r.created_at) as created_at,
                MAX(r.updated_at) as updated_at
            FROM revenues_backup_temp r
            INNER JOIN company_id_mapping m ON m.old_company_id = r.company_id
            WHERE m.nip_nas IS NOT NULL
            GROUP BY m.nip_nas, r.tahun, r.bulan
        ');
        
        // STEP 5: Drop temporary tables
        Schema::dropIfExists('revenues_backup_temp');
        Schema::dropIfExists('company_id_mapping');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ROLLBACK: Kembalikan ke struktur lama
        
        // Drop table revenues
        Schema::dropIfExists('revenues');
        
        // Recreate dengan struktur lama
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->year('tahun');
            $table->tinyInteger('bulan')->unsigned();
            $table->decimal('revenue', 15, 2)->default(0);
            $table->foreignId('region_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('witel_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['company_id', 'tahun', 'bulan']);
        });
        
        // Data akan hilang saat rollback, harus di-restore dari backup manual
    }
};
