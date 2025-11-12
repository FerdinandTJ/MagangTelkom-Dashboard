<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Lini Waktu Table
 * 
 * Table lini_waktu berisi periode waktu quartal untuk tracking target dan realisasi
 * 
 * Struktur:
 * - id (auto increment, primary key)
 * - quartal (ENUM: Q1, Q2, Q3, Q4) - Quartal dalam tahun
 * - bulan_awal (datetime) - Bulan awal quartal
 * - bulan_akhir (datetime) - Bulan akhir quartal
 * - tahun (year) - Tahun periode
 * - nik_am (FK to account_managers) - Account Manager yang terkait dengan periode ini
 * 
 * Relasi:
 * - lini_waktu → account_managers (Many-to-One via nik_am FK)
 * - lini_waktu ↔ target_account_m (Many-to-Many via pivot: lini_waktu_target)
 * 
 * Contoh Data:
 * Q1 2024: Januari - Maret 2024
 * Q2 2024: April - Juni 2024
 * Q3 2024: Juli - September 2024
 * Q4 2024: Oktober - Desember 2024
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lini_waktu', function (Blueprint $table) {
            // PRIMARY KEY: id (auto increment)
            $table->id()->comment('Primary Key - Auto Increment');
            
            // QUARTAL: Quarter dalam tahun (Q1, Q2, Q3, Q4)
            $table->enum('quartal', ['Q1', 'Q2', 'Q3', 'Q4'])
                  ->comment('Quartal periode (Q1: Jan-Mar, Q2: Apr-Jun, Q3: Jul-Sep, Q4: Okt-Des)');
            
            // BULAN AWAL: Tanggal awal periode quartal
            $table->dateTime('bulan_awal')->comment('Tanggal awal periode quartal');
            
            // BULAN AKHIR: Tanggal akhir periode quartal
            $table->dateTime('bulan_akhir')->comment('Tanggal akhir periode quartal');
            
            // TAHUN: Tahun periode
            $table->year('tahun')->comment('Tahun periode');
            
            // FOREIGN KEY: Relasi Many-to-One dengan account_managers
            // Setiap lini waktu terkait dengan satu Account Manager
            $table->string('nik_am', 10)->comment('FK to account_managers - NIK Account Manager');
            
            // TIMESTAMPS
            $table->timestamps();
            
            // UNIQUE CONSTRAINT: Satu AM tidak boleh punya duplicate quartal+tahun yang sama
            $table->unique(['nik_am', 'tahun', 'quartal'], 'unique_am_period');
            
            // INDEX: untuk performa query berdasarkan tahun, quartal, dan AM
            $table->index('tahun', 'idx_lini_waktu_tahun');
            $table->index('quartal', 'idx_lini_waktu_quartal');
            $table->index('nik_am', 'idx_lini_waktu_am');
            
            // FOREIGN KEY CONSTRAINT: nik_am → account_managers.nik
            $table->foreign('nik_am')
                  ->references('nik')
                  ->on('account_managers')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop table lini_waktu
        Schema::dropIfExists('lini_waktu');
    }
};
