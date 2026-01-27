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
        Schema::create('hari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bulan_id'); // Foreign key to bulan
            $table->tinyInteger('tanggal')->comment('1-31'); // tanggal 1-31
            $table->year('tahun');
            $table->decimal('progress_scaling', 20, 2)->nullable();
            $table->decimal('sodomoro', 20, 2)->nullable();
            $table->decimal('adjustment', 20, 2)->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('bulan_id')->references('id')->on('bulan')->onDelete('cascade');
            
            // Composite unique index untuk bulan_id, tanggal, dan tahun
            $table->unique(['bulan_id', 'tanggal', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari');
    }
};
