<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add Witel Foreign Key to Companies Table
 * 
 * Purpose: Menambahkan relasi One-to-One antara companies dan witels
 * 
 * STRUKTUR RELASI:
 * - companies → witels (One-to-One via idwitels FK)
 * - Setiap company berada di satu witel tertentu
 * - FK: companies.idwitels → witels.idwitels
 * 
 * CATATAN:
 * - idwitels nullable karena mungkin ada company yang belum di-assign ke witel
 * - ON DELETE SET NULL: Jika witel dihapus, company tetap ada tapi idwitels jadi NULL
 * - ON UPDATE CASCADE: Jika idwitels di witels berubah, FK di companies ikut update
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Add idwitels column (FK to witels.idwitels)
            // Nullable karena mungkin ada company yang belum di-assign
            $table->unsignedBigInteger('idwitels')
                  ->nullable()
                  ->after('source_data')
                  ->comment('FK to witels.idwitels - Witel tempat company berada');
            
            // Add index untuk performa query join dengan witels
            $table->index('idwitels', 'idx_company_witel');
            
            // Add foreign key constraint
            $table->foreign('idwitels', 'fk_companies_witels')
                  ->references('idwitels')
                  ->on('witels')
                  ->onDelete('set null')  // Jika witel dihapus, set NULL
                  ->onUpdate('cascade');   // Jika idwitels di witel berubah, ikut update
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign('fk_companies_witels');
            
            // Drop index
            $table->dropIndex('idx_company_witel');
            
            // Drop column
            $table->dropColumn('idwitels');
        });
    }
};
