<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lini_waktu_target', function (Blueprint $table) {
            // Change r_datin from decimal(7,2) to decimal(15,2) to match r_revenue and r_lop
            $table->decimal('r_datin', 15, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lini_waktu_target', function (Blueprint $table) {
            $table->decimal('r_datin', 7, 2)->default(0)->change();
        });
    }
};
