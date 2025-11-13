<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Update lini_waktu_target - Ubah field realisasi ke persentase
 * 
 * PERUBAHAN:
 * - r_cyc: decimal(10,2) → decimal(7,3) (ubah ke persentase)
 * - r_cr: decimal(10,2) → decimal(7,3) (ubah ke persentase)
 * - r_profit: decimal(5,2) → decimal(7,3) (ubah ke persentase)
 * - r_maps: decimal(5,2) → decimal(7,3) (ubah ke persentase)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Update existing data - Convert nilai besar ke persentase
        DB::statement('UPDATE lini_waktu_target SET r_cyc = 85.000 WHERE r_cyc > 1000');
        DB::statement('UPDATE lini_waktu_target SET r_cr = 85.000 WHERE r_cr > 1000');
        
        // STEP 2: Ubah tipe data kolom realisasi
        Schema::table('lini_waktu_target', function (Blueprint $table) {
            // Ubah r_cyc ke decimal(7,3) untuk persentase
            $table->decimal('r_cyc', 7, 3)->default(0)->change();
            
            // Ubah r_cr ke decimal(7,3) untuk persentase
            $table->decimal('r_cr', 7, 3)->default(0)->change();
            
            // Ubah r_profit ke decimal(7,3) untuk persentase
            $table->decimal('r_profit', 7, 3)->default(0)->change();
            
            // Ubah r_maps ke decimal(7,3) untuk persentase
            $table->decimal('r_maps', 7, 3)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lini_waktu_target', function (Blueprint $table) {
            $table->decimal('r_cyc', 10, 2)->default(0)->change();
            $table->decimal('r_cr', 10, 2)->default(0)->change();
            $table->decimal('r_profit', 5, 2)->default(0)->change();
            $table->decimal('r_maps', 5, 2)->default(0)->change();
        });
    }
};
