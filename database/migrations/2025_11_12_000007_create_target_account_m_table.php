<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Target Account M Table
 * 
 * Table target_account_m berisi data target KPI untuk Account Manager
 * Table ini akan berelasi Many-to-Many dengan lini_waktu via pivot table
 * 
 * Struktur:
 * - id (auto increment, primary key)
 * - t_revenue (target revenue)
 * - t_scalling (target scalling)
 * - t_datin (target datin)
 * - t_hsi (target HSI)
 * - t_wireline (target wireline)
 * - t_wifi (target wifi)
 * - t_cyc (target CYC)
 * - t_cr (target CR)
 * - t_profit (target profit)
 * - t_nps (target NPS)
 * - t_maps (target MAPS)
 * - t_lop (target LOP)
 * - t_capability (target capability)
 * - t_cc (target CC)
 * - t_ngtma (target NGTMA)
 * - t_sustain (target sustain)
 * 
 * Relasi:
 * - target_account_m ↔ lini_waktu (Many-to-Many via pivot: lini_waktu_target)
 *   Pivot table akan berisi realisasi (r_*) untuk setiap target
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('target_account_m', function (Blueprint $table) {
            // PRIMARY KEY: id (auto increment)
            $table->id()->comment('Primary Key - Auto Increment');
            
            // TARGET REVENUE: Target pendapatan (dalam Rupiah)
            $table->decimal('t_revenue', 15, 2)->default(0)->comment('Target Revenue (Rp)');
            
            // TARGET SCALLING: Target scalling bisnis
            $table->decimal('t_scalling', 15, 2)->default(0)->comment('Target Scalling');
            
            // TARGET DATIN: Target Data Internet
            $table->decimal('t_datin', 7, 2)->default(0)->comment('Target Data Internet');
            
            // TARGET HSI: Target High Speed Internet
            $table->decimal('t_hsi', 5, 2)->default(0)->comment('Target HSI (High Speed Internet)');
            
            // TARGET WIRELINE: Target layanan wireline
            $table->decimal('t_wireline', 5, 2)->default(0)->comment('Target Wireline');
            
            // TARGET WIFI: Target layanan WiFi
            $table->decimal('t_wifi', 7, 2)->default(0)->comment('Target WiFi');
            
            // TARGET CYC: Target Customer Yield per Customer
            $table->decimal('t_cyc', 10, 2)->default(0)->comment('Target CYC (Customer Yield per Customer)');
            
            // TARGET CR: Target Churn Rate
            $table->decimal('t_cr', 10, 2)->default(0)->comment('Target CR (Churn Rate) %');
            
            // TARGET PROFIT: Target profit margin
            $table->decimal('t_profit', 5, 2)->default(0)->comment('Target Profit %');
            
            // TARGET NPS: Target Net Promoter Score
            $table->decimal('t_nps', 5, 2)->default(0)->comment('Target NPS (Net Promoter Score)');
            
            // TARGET MAPS: Target MAPS
            $table->decimal('t_maps', 5, 2)->default(0)->comment('Target MAPS');
            
            // TARGET LOP: Target Length of Payment
            $table->decimal('t_lop', 15, 2)->default(0)->comment('Target LOP (Length of Payment)');
            
            // TARGET CAPABILITY: Target capability score
            $table->decimal('t_capability', 5, 2)->default(0)->comment('Target Capability Score');
            
            // TARGET CC: Target Customer Count
            $table->decimal('t_cc', 5, 2)->default(0)->comment('Target CC (Customer Count)');
            
            // TARGET NGTMA: Target NGTMA (Next Generation TMA)
            $table->decimal('t_ngtma', 15, 2)->default(0)->comment('Target NGTMA');
            
            // TARGET SUSTAIN: Target sustainability
            $table->decimal('t_sustain', 15, 2)->default(0)->comment('Target Sustain');
            
            // TIMESTAMPS
            $table->timestamps();
            
            // INDEX: untuk performa query
            $table->index('t_revenue', 'idx_target_revenue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop table target_account_m
        Schema::dropIfExists('target_account_m');
    }
};
