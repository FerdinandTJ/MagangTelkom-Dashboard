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
        Schema::create('bulan', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('bulan')->comment('1-12'); // bulan 1-12
            $table->year('tahun');
            $table->decimal('t_sustain', 20, 2)->nullable();
            $table->decimal('kebutuhan_scaling', 20, 2)->nullable();
            $table->decimal('r_scaling', 20, 2)->nullable();
            $table->decimal('sodomoro', 20, 2)->nullable();
            $table->decimal('adjustment', 20, 2)->nullable();
            $table->decimal('target_cm', 20, 2)->nullable();
            $table->decimal('target_ytd', 20, 2)->nullable();
            $table->decimal('rev_cm', 20, 2)->nullable();
            $table->decimal('rev_ytd', 20, 2)->nullable();
            $table->decimal('ach_cm', 5, 2)->nullable()->comment('Achievement CM percentage');
            $table->decimal('ach_ytd', 5, 2)->nullable()->comment('Achievement YTD percentage');
            $table->timestamps();
            
            // Composite unique index untuk bulan dan tahun
            $table->unique(['bulan', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulan');
    }
};
