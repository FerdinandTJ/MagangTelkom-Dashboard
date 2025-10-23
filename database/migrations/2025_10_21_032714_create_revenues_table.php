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
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->year('tahun');
            $table->tinyInteger('bulan')->comment('1-12 untuk bulan Januari-Desember');
            $table->decimal('revenue', 15, 2)->comment('Revenue dalam Rupiah');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplikat data per company per bulan
            $table->unique(['company_id', 'tahun', 'bulan']);
            
            // Indexes for better performance
            $table->index(['tahun', 'bulan']);
            $table->index(['tahun']);
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
