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
        Schema::create('lop_bulan', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->string('ID_LOP', 55);
            $table->unsignedBigInteger('bulan_id');
            
            // Additional attributes
            $table->string('AM', 100)->nullable();
            $table->string('Nama_CC', 100)->nullable();
            $table->unsignedBigInteger('ID_Region')->nullable();
            $table->string('Project', 500)->nullable();
            $table->decimal('Scaling', 20, 2)->nullable();
            $table->string('Progress', 50)->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('ID_LOP')->references('ID_LOP')->on('lop')->onDelete('cascade');
            $table->foreign('bulan_id')->references('id')->on('bulan')->onDelete('cascade');
            $table->foreign('ID_Region')->references('id')->on('regions')->onDelete('set null');
            
            // Composite unique index
            $table->unique(['ID_LOP', 'bulan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lop_bulan');
    }
};
