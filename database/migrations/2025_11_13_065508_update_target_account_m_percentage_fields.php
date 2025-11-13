<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Update target_account_m - Ubah field ke persentase
 * 
 * PERUBAHAN:
 * - t_cyc: decimal(10,2) → decimal(7,3) (ubah ke persentase)
 * - t_cr: decimal(10,2) → decimal(7,3) (ubah ke persentase)
 * - t_profit: decimal(5,2) → decimal(7,3) (ubah ke persentase)
 * - t_maps: decimal(5,2) → decimal(7,3) (ubah ke persentase)
 * 
 * NOTE: Field ini sekarang menyimpan nilai persentase (0-100%)
 * Contoh: 85.5% disimpan sebagai 85.500
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Update existing data - Convert nilai besar ke persentase
        // NOTE: Data lama menyimpan nilai absolut (contoh: 5000000)
        // Data baru akan menyimpan persentase (contoh: 85.5 untuk 85.5%)
        DB::statement('UPDATE target_account_m SET t_cyc = 85.000 WHERE t_cyc > 1000');
        DB::statement('UPDATE target_account_m SET t_cr = 85.000 WHERE t_cr > 1000');
        
        // STEP 2: Ubah tipe data kolom
        Schema::table('target_account_m', function (Blueprint $table) {
            // Ubah t_cyc dari decimal(10,2) ke decimal(7,3) untuk persentase
            // NOTE: Field ini sekarang untuk persentase CYC achievement target
            $table->decimal('t_cyc', 7, 3)->default(0)->change();
            
            // Ubah t_cr dari decimal(10,2) ke decimal(7,3) untuk persentase
            // NOTE: Field ini sekarang untuk persentase CR (Customer Retention) target
            $table->decimal('t_cr', 7, 3)->default(0)->change();
            
            // Ubah t_profit dari decimal(5,2) ke decimal(7,3) untuk persentase
            // NOTE: Field ini sudah persentase, hanya ditambah precision
            $table->decimal('t_profit', 7, 3)->default(0)->change();
            
            // Ubah t_maps dari decimal(5,2) ke decimal(7,3) untuk persentase
            // NOTE: Field ini sudah persentase, hanya ditambah precision
            $table->decimal('t_maps', 7, 3)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_account_m', function (Blueprint $table) {
            // Kembalikan ke tipe data semula
            $table->decimal('t_cyc', 10, 2)->default(0)->change();
            $table->decimal('t_cr', 10, 2)->default(0)->change();
            $table->decimal('t_profit', 5, 2)->default(0)->change();
            $table->decimal('t_maps', 5, 2)->default(0)->change();
        });
    }
};
