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
        Schema::create('group2', function (Blueprint $table) {
            $table->id('idGroup2');
            $table->string('nama_group2', 45);
            $table->unsignedBigInteger('group1_id');
            $table->timestamps();

            // Add index first
            $table->index('group1_id');
        });
        
        // Add foreign key constraint in separate statement
        Schema::table('group2', function (Blueprint $table) {
            $table->foreign('group1_id')
                  ->references('idGroup1')
                  ->on('group1')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group2');
    }
};
