<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambah kolom achievement di lini_waktu_target
 * 
 * PENAMBAHAN KOLOM BARU:
 * Semua kolom achievement (ach_*) untuk tracking persentase pencapaian
 * Format: decimal(6,3) - menyimpan persentase 0-100%
 * Contoh: 95.5% disimpan sebagai 95.500
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lini_waktu_target', function (Blueprint $table) {
            // Achievement Revenue Plan (%) - Persentase pencapaian target revenue
            $table->decimal('ach_revenue_plan', 6, 3)->default(0)->comment('Achievement revenue plan (%)');
            
            // Achievement Scaling (%) - Persentase pencapaian scaling target
            $table->decimal('ach_scaling', 6, 3)->default(0)->comment('Achievement scaling (%)');
            
            // Achievement Sales Datin (%) - Persentase pencapaian sales datin target
            $table->decimal('ach_sales_datin', 6, 3)->default(0)->comment('Achievement sales datin (%)');
            
            // Achievement HSI (%) - Persentase pencapaian HSI target
            $table->decimal('ach_hsi', 6, 3)->default(0)->comment('Achievement HSI (%)');
            
            // Achievement Wireline (%) - Persentase pencapaian wireline target
            $table->decimal('ach_wireline', 6, 3)->default(0)->comment('Achievement wireline (%)');
            
            // Achievement WiFi (%) - Persentase pencapaian WiFi target
            $table->decimal('ach_wifi', 6, 3)->default(0)->comment('Achievement WiFi (%)');
            
            // Achievement CYC (%) - Persentase pencapaian CYC target
            $table->decimal('ach_cyc', 6, 3)->default(0)->comment('Achievement CYC (%)');
            
            // Achievement CR (%) - Persentase pencapaian customer retention target
            $table->decimal('ach_cr', 6, 3)->default(0)->comment('Achievement CR (%)');
            
            // Achievement Profit (%) - Persentase pencapaian profit target
            $table->decimal('ach_profit', 6, 3)->default(0)->comment('Achievement profit (%)');
            
            // Achievement NPS (%) - Persentase pencapaian NPS target
            $table->decimal('ach_nps', 6, 3)->default(0)->comment('Achievement NPS (%)');
            
            // Achievement MAPS (%) - Persentase pencapaian MAPS target
            $table->decimal('ach_maps', 6, 3)->default(0)->comment('Achievement MAPS (%)');
            
            // Achievement LOP (%) - Persentase pencapaian LOP target
            $table->decimal('ach_lop', 6, 3)->default(0)->comment('Achievement LOP (%)');
            
            // Achievement Capability (%) - Persentase pencapaian capability target
            $table->decimal('ach_capability', 6, 3)->default(0)->comment('Achievement capability (%)');
            
            // Achievement CC (%) - Persentase pencapaian CC target
            $table->decimal('ach_cc', 6, 3)->default(0)->comment('Achievement CC (%)');
            
            // Achievement Result (%) - Persentase pencapaian result overall
            $table->decimal('ach_result', 6, 3)->default(0)->comment('Achievement result (%)');
            
            // Achievement Process (%) - Persentase pencapaian process
            $table->decimal('ach_proses', 6, 3)->default(0)->comment('Achievement process (%)');
            
            // NKI Adjustment (%) - Faktor adjustment NKI (Nilai Kinerja Individu)
            $table->decimal('nki_adjustment', 6, 3)->default(0)->comment('NKI adjustment factor (%)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lini_waktu_target', function (Blueprint $table) {
            // Drop semua kolom achievement yang ditambahkan
            $table->dropColumn([
                'ach_revenue_plan',
                'ach_scaling',
                'ach_sales_datin',
                'ach_hsi',
                'ach_wireline',
                'ach_wifi',
                'ach_cyc',
                'ach_cr',
                'ach_profit',
                'ach_nps',
                'ach_maps',
                'ach_lop',
                'ach_capability',
                'ach_cc',
                'ach_result',
                'ach_proses',
                'nki_adjustment'
            ]);
        });
    }
};
