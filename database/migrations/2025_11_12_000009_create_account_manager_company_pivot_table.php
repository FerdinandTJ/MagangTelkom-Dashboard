<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Pivot Table account_manager_company
 * 
 * Pivot table untuk relasi Many-to-Many antara account_managers dan companies
 * Satu Account Manager bisa handle multiple companies
 * Satu Company bisa dihandle oleh multiple Account Managers
 * 
 * Struktur:
 * - id (auto increment, primary key)
 * - nik_am (FK to account_managers.nik) - Account Manager
 * - nip_nas (FK to companies.nip_nas) - Company
 * - proporsi (decimal 5,2) - Proporsi pembagian (dalam %)
 * - pembagian (ENUM: SINGLE, MULTI) - Jenis pembagian
 * - segment (varchar 20) - Segment khusus
 * 
 * Contoh Kasus:
 * 1. SINGLE: 1 AM handle 1 company (proporsi 100%)
 * 2. MULTI: 2+ AM handle 1 company (proporsi dibagi, misal: 50%-50% atau 60%-40%)
 * 
 * Relasi:
 * - pivot → account_managers (via nik_am FK)
 * - pivot → companies (via nip_nas FK)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_manager_company', function (Blueprint $table) {
            // PRIMARY KEY: id (auto increment)
            $table->id()->comment('Primary Key - Auto Increment');
            
            // FOREIGN KEY: nik_am (NIK Account Manager)
            $table->string('nik_am', 10)->comment('FK to account_managers.nik');
            
            // FOREIGN KEY: nip_nas (NIP Perusahaan)
            $table->string('nip_nas', 25)->comment('FK to companies.nip_nas');
            
            // PROPORSI: Persentase pembagian tanggung jawab (0.00 - 100.00)
            // Contoh: 
            // - Single AM = 100.00
            // - Multi AM (2 orang) = 50.00 & 50.00
            // - Multi AM (3 orang) = 40.00 & 30.00 & 30.00
            $table->decimal('proporsi', 5, 2)->default(100.00)
                  ->comment('Proporsi pembagian tanggung jawab (dalam %, total per company harus 100%)');
            
            // PEMBAGIAN: Tipe pembagian
            $table->enum('pembagian', ['SINGLE', 'MULTI'])
                  ->default('SINGLE')
                  ->comment('Jenis pembagian: SINGLE (1 AM), MULTI (2+ AM)');
            
            // SEGMENT: Segment khusus untuk AM ini di company
            // Contoh: "Enterprise", "Corporate", "Government", dll
            $table->string('segment', 20)->nullable()
                  ->comment('Segment khusus AM di company ini');
            
            // TIMESTAMPS
            $table->timestamps();
            
            // UNIQUE CONSTRAINT: Satu AM tidak boleh duplicate di 1 company
            $table->unique(['nik_am', 'nip_nas'], 'unique_am_company');
            
            // INDEX: untuk performa query
            $table->index('nik_am', 'idx_pivot_am');
            $table->index('nip_nas', 'idx_pivot_company');
            $table->index('pembagian', 'idx_pivot_pembagian');
            
            // FOREIGN KEY CONSTRAINTS
            $table->foreign('nik_am')
                  ->references('nik')
                  ->on('account_managers')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->foreign('nip_nas')
                  ->references('nip_nas')
                  ->on('companies')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop pivot table account_manager_company
        Schema::dropIfExists('account_manager_company');
    }
};
