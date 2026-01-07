<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Pivot Table lini_waktu_target (Realisasi)
 * 
 * Pivot table untuk relasi Many-to-Many antara lini_waktu dan target_account_m
 * Table ini menyimpan REALISASI (actual achievement) dari setiap target per periode
 * 
 * Struktur:
 * - id (auto increment, primary key)
 * - lini_waktu_id (FK to lini_waktu.id) - Periode waktu
 * - target_id (FK to target_account_m.id) - Target yang direalisasikan
 * - r_revenue (realisasi revenue)
 * - r_scalling (realisasi scalling)
 * - r_datin (realisasi datin)
 * - r_hsi (realisasi HSI)
 * - r_wireline (realisasi wireline)
 * - r_wifi (realisasi wifi)
 * - r_cyc (realisasi CYC)
 * - r_cr (realisasi CR)
 * - r_profit (realisasi profit)
 * - r_nps (realisasi NPS)
 * - r_maps (realisasi MAPS)
 * - r_lop (realisasi LOP)
 * - r_capability (realisasi capability)
 * - r_cc (realisasi CC)
 * 
 * Konsep:
 * - target_account_m = Target KPI yang ditetapkan
 * - lini_waktu_target (pivot) = Realisasi actual per periode (quartal)
 * 
 * Contoh:
 * Target Revenue Q1 2024 = 1,000,000,000 (di table target_account_m)
 * Realisasi Q1 2024 = 950,000,000 (di pivot: r_revenue)
 * Achievement = 95%
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lini_waktu_target', function (Blueprint $table) {
            // PRIMARY KEY: id (auto increment)
            $table->id()->comment('Primary Key - Auto Increment');
            
            // FOREIGN KEY: lini_waktu_id
            $table->foreignId('lini_waktu_id')->comment('FK to lini_waktu.id - Periode waktu');
            
            // FOREIGN KEY: target_id
            $table->foreignId('target_id')->comment('FK to target_account_m.id - Target KPI');
            
            // ===== REALISASI (ACTUAL ACHIEVEMENT) =====
            
            // REALISASI REVENUE: Actual revenue yang tercapai
            $table->decimal('r_revenue', 15, 2)->default(0)->comment('Realisasi Revenue (Rp)');
            
            // REALISASI SCALLING: Actual scalling yang tercapai
            $table->decimal('r_scalling', 15, 2)->default(0)->comment('Realisasi Scalling');
            
            // REALISASI DATIN: Actual Data Internet
            $table->decimal('r_datin', 7, 2)->default(0)->comment('Realisasi Data Internet');
            
            // REALISASI HSI: Actual High Speed Internet
            $table->decimal('r_hsi', 5, 2)->default(0)->comment('Realisasi HSI');
            
            // REALISASI WIRELINE: Actual layanan wireline
            $table->decimal('r_wireline', 5, 2)->default(0)->comment('Realisasi Wireline');
            
            // REALISASI WIFI: Actual layanan WiFi
            $table->decimal('r_wifi', 7, 2)->default(0)->comment('Realisasi WiFi');
            
            // REALISASI CYC: Actual Customer Yield per Customer
            $table->decimal('r_cyc', 10, 2)->default(0)->comment('Realisasi CYC');
            
            // REALISASI CR: Actual Churn Rate
            $table->decimal('r_cr', 10, 2)->default(0)->comment('Realisasi CR (Churn Rate) %');
            
            // REALISASI PROFIT: Actual profit margin
            $table->decimal('r_profit', 5, 2)->default(0)->comment('Realisasi Profit %');
            
            // REALISASI NPS: Actual Net Promoter Score
            $table->decimal('r_nps', 5, 2)->default(0)->comment('Realisasi NPS');
            
            // REALISASI MAPS: Actual MAPS
            $table->decimal('r_maps', 5, 2)->default(0)->comment('Realisasi MAPS');
            
            // REALISASI LOP: Actual Length of Payment
            $table->decimal('r_lop', 15, 2)->default(0)->comment('Realisasi LOP');
            
            // REALISASI SUSTAIN: Actual Sustain
            $table->decimal('r_sustain', 15, 2)->default(0)->comment('Realisasi Sustain');
            
            // REALISASI NGTMA: Actual NGTMA (Next Generation TMA)
            $table->decimal('r_ngtma', 15, 2)->default(0)->comment('Realisasi NGTMA');
            
            // REALISASI CAPABILITY: Actual capability score
            $table->decimal('r_capability', 5, 2)->default(0)->comment('Realisasi Capability');
            
            // REALISASI CC: Actual Customer Count
            $table->decimal('r_cc', 5, 2)->default(0)->comment('Realisasi CC');
            
            // TIMESTAMPS
            $table->timestamps();
            
            // UNIQUE CONSTRAINT: Satu target hanya boleh punya 1 realisasi per periode
            $table->unique(['lini_waktu_id', 'target_id'], 'unique_period_target');
            
            // INDEX: untuk performa query
            $table->index('lini_waktu_id', 'idx_pivot_lini_waktu');
            $table->index('target_id', 'idx_pivot_target');
            
            // FOREIGN KEY CONSTRAINTS
            $table->foreign('lini_waktu_id')
                  ->references('id')
                  ->on('lini_waktu')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->foreign('target_id')
                  ->references('id')
                  ->on('target_account_m')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop pivot table lini_waktu_target
        Schema::dropIfExists('lini_waktu_target');
    }
};
