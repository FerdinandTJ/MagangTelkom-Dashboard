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
        Schema::create('company_targets', function (Blueprint $table) {
            $table->id();
            $table->string('nip_nas')->index(); // Company ID
            $table->integer('tahun')->index(); // Year
            $table->integer('bulan'); // Month (1-12)
            $table->decimal('target_revenue', 15, 2)->default(0); // Target per company per month
            $table->timestamps();
            
            // Unique constraint: 1 target per company per month per year
            $table->unique(['nip_nas', 'tahun', 'bulan']);
            
            // Foreign key to companies table
            $table->foreign('nip_nas')->references('nip_nas')->on('companies')->onDelete('cascade');
            
            // Composite index for queries
            $table->index(['tahun', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_targets');
    }
};
