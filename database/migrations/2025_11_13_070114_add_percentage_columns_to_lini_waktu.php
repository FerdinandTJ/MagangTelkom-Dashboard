<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom percentage di lini_waktu
 * 
 * PENAMBAHAN KOLOM BARU:
 * Semua kolom percentage (percentage_*) untuk tracking bobot persentase per KPI
 * Format: decimal(6,3) - menyimpan persentase 0-100%
 * Contoh: 15.5% disimpan sebagai 15.500
 * 
 * NOTE: Kolom ini menyimpan bobot/weight masing-masing KPI dalam perhitungan total
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lini_waktu', function (Blueprint $table) {
            // Percentage Result (%) - Bobot result dalam total score
            $table->decimal('percentage_result', 6, 3)->default(0)->comment('Weight result in total (%)');
            
            // Percentage Revenue (%) - Bobot revenue dalam perhitungan
            $table->decimal('percentage_revenue', 6, 3)->default(0)->comment('Weight revenue (%)');
            
            // Percentage Scaling (%) - Bobot scaling dalam perhitungan
            $table->decimal('percentage_scaling', 6, 3)->default(0)->comment('Weight scaling (%)');
            
            // Percentage Datin (%) - Bobot datin dalam perhitungan
            $table->decimal('percentage_datin', 6, 3)->default(0)->comment('Weight datin (%)');
            
            // Percentage HSI (%) - Bobot HSI dalam perhitungan
            $table->decimal('percentage_hsi', 6, 3)->default(0)->comment('Weight HSI (%)');
            
            // Percentage Wireline (%) - Bobot wireline dalam perhitungan
            $table->decimal('percentage_wireline', 6, 3)->default(0)->comment('Weight wireline (%)');
            
            // Percentage WiFi (%) - Bobot WiFi dalam perhitungan
            $table->decimal('percentage_wifi', 6, 3)->default(0)->comment('Weight WiFi (%)');
            
            // Percentage CYC (%) - Bobot CYC dalam perhitungan
            $table->decimal('percentage_cyc', 6, 3)->default(0)->comment('Weight CYC (%)');
            
            // Percentage CR (%) - Bobot customer retention dalam perhitungan
            $table->decimal('percentage_cr', 6, 3)->default(0)->comment('Weight CR (%)');
            
            // Percentage Profit (%) - Bobot profit dalam perhitungan
            $table->decimal('percentage_profit', 6, 3)->default(0)->comment('Weight profit (%)');
            
            // Percentage Customer (%) - Bobot customer dalam perhitungan
            $table->decimal('percentage_customer', 6, 3)->default(0)->comment('Weight customer (%)');
            
            // Percentage Process (%) - Bobot process dalam total score
            $table->decimal('percentage_proses', 6, 3)->default(0)->comment('Weight process in total (%)');
            
            // Percentage MAPS (%) - Bobot MAPS dalam perhitungan process
            $table->decimal('percentage_maps', 6, 3)->default(0)->comment('Weight MAPS (%)');
            
            // Percentage LOP (%) - Bobot LOP dalam perhitungan process
            $table->decimal('percentage_lop', 6, 3)->default(0)->comment('Weight LOP (%)');
            
            // Percentage Capability (%) - Bobot capability dalam perhitungan process
            $table->decimal('percentage_capability', 6, 3)->default(0)->comment('Weight capability (%)');
            
            // Percentage CC (%) - Bobot CC dalam perhitungan process
            $table->decimal('percentage_cc', 6, 3)->default(0)->comment('Weight CC (%)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lini_waktu', function (Blueprint $table) {
            // Drop semua kolom percentage yang ditambahkan
            $table->dropColumn([
                'percentage_result',
                'percentage_revenue',
                'percentage_scaling',
                'percentage_datin',
                'percentage_hsi',
                'percentage_wireline',
                'percentage_wifi',
                'percentage_cyc',
                'percentage_cr',
                'percentage_profit',
                'percentage_customer',
                'percentage_proses',
                'percentage_maps',
                'percentage_lop',
                'percentage_capability',
                'percentage_cc'
            ]);
        });
    }
};
