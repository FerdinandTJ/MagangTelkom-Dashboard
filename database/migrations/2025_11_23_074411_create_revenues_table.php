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
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group4_id');
            $table->year('tahun');
            $table->tinyInteger('bulan')->comment('1-12 representing months');
            $table->decimal('revenue_realisasi', 15, 2)->default(0);
            $table->decimal('revenue_target', 15, 2)->default(0);
            $table->timestamps();

            // Foreign key constraint pointing to group4.idGroup4
            $table->foreign('group4_id')
                  ->references('idGroup4')
                  ->on('group4')
                  ->onDelete('cascade');

            // Ensure unique revenue record per product per month
            $table->unique(['group4_id', 'tahun', 'bulan'], 'unique_product_month');

            // Index for faster queries
            $table->index(['tahun', 'bulan'], 'idx_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
