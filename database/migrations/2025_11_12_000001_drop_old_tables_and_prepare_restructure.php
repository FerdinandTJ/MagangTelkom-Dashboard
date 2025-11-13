<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Drop Old Tables and Prepare Database Restructure
 * 
 * PENTING: BACKUP DATABASE SEBELUM MENJALANKAN MIGRATION INI!
 * Jalankan command berikut untuk backup:
 * mysqldump -u root -p telkomtws > backup_before_restructure_$(date +%Y%m%d_%H%M%S).sql
 * 
 * Tables yang akan di-drop:
 * - company_regions (akan diganti dengan pivot table baru: account_manager_company)
 * 
 * Tables yang akan di-restructure (di migration berikutnya):
 * - companies (ubah primary key ke nip_nas, hapus region/witel fields)
 * - regions (tambah ENUM code, description)
 * - witels (ubah structure, tambah region_id FK)
 * - revenues (hapus region_id/witel_id, tambah FK ke companies)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Drop table company_regions (tidak digunakan lagi dalam struktur baru)
        // Struktur baru menggunakan pivot table account_manager_company
        Schema::dropIfExists('company_regions');
        
        // STEP 2: Drop foreign key constraints dari tables yang akan di-restructure
        // Ini diperlukan agar kita bisa mengubah struktur primary key
        
        // Drop foreign keys di table revenues yang merujuk ke companies, regions dan witels
        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                // Drop foreign key company_id jika ada
                if (Schema::hasColumn('revenues', 'company_id')) {
                    $table->dropForeign(['company_id']);
                }
                // Drop foreign key region_id jika ada
                if (Schema::hasColumn('revenues', 'region_id')) {
                    $table->dropForeign(['region_id']);
                }
                // Drop foreign key witel_id jika ada
                if (Schema::hasColumn('revenues', 'witel_id')) {
                    $table->dropForeign(['witel_id']);
                }
            });
        }
        
        // Drop foreign keys di table companies yang merujuk ke regions dan witels
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                // Drop foreign key primary_region_id jika ada
                if (Schema::hasColumn('companies', 'primary_region_id')) {
                    $table->dropForeign(['primary_region_id']);
                }
                // Drop foreign key primary_witel_id jika ada
                if (Schema::hasColumn('companies', 'primary_witel_id')) {
                    $table->dropForeign(['primary_witel_id']);
                }
            });
        }
        
        // Drop foreign keys di table witels yang merujuk ke regions
        if (Schema::hasTable('witels')) {
            Schema::table('witels', function (Blueprint $table) {
                // Drop foreign key region_id jika ada
                if (Schema::hasColumn('witels', 'region_id')) {
                    $table->dropForeign(['region_id']);
                }
            });
        }
        
        // NOTE: Data di tables utama (companies, revenues, regions, witels) masih ada
        // dan akan di-restructure di migration berikutnya
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ROLLBACK: Recreate company_regions table dengan struktur lama
        Schema::create('company_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->foreignId('witel_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['company_id', 'region_id', 'witel_id']);
        });
        
        // ROLLBACK: Recreate foreign keys di revenues
        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                if (Schema::hasColumn('revenues', 'region_id') && !Schema::hasColumn('revenues', 'region_id')) {
                    $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
                }
                if (Schema::hasColumn('revenues', 'witel_id')) {
                    $table->foreign('witel_id')->references('id')->on('witels')->onDelete('set null');
                }
            });
        }
        
        // ROLLBACK: Recreate foreign keys di companies
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (Schema::hasColumn('companies', 'primary_region_id')) {
                    $table->foreign('primary_region_id')->references('id')->on('regions')->onDelete('set null');
                }
                if (Schema::hasColumn('companies', 'primary_witel_id')) {
                    $table->foreign('primary_witel_id')->references('id')->on('witels')->onDelete('set null');
                }
            });
        }
        
        // ROLLBACK: Recreate foreign keys di witels
        if (Schema::hasTable('witels')) {
            Schema::table('witels', function (Blueprint $table) {
                if (Schema::hasColumn('witels', 'region_id')) {
                    $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
                }
            });
        }
    }
};
