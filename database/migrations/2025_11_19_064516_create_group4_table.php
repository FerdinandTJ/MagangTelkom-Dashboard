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
        Schema::create('group4', function (Blueprint $table) {
            $table->id('idGroup4');
            $table->string('nama_group4', 45);
            $table->decimal('revenue_realisasi', 15, 2)->default(0);
            $table->year('tahun');
            $table->tinyInteger('bulan')->unsigned();
            $table->unsignedBigInteger('group3_id');
            $table->timestamps();

            // Add indexes first
            $table->index(['tahun', 'bulan']);
            $table->index('group3_id');
        });
        
        // Add foreign key constraint in separate statement
        Schema::table('group4', function (Blueprint $table) {
            $table->foreign('group3_id')
                  ->references('idGroup3')
                  ->on('group3')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group4');
    }
};
