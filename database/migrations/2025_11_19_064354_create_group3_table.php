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
        Schema::create('group3', function (Blueprint $table) {
            $table->id('idGroup3');
            $table->string('nama_group3', 45);
            $table->unsignedBigInteger('group2_id');
            $table->timestamps();

            // Add index first
            $table->index('group2_id');
        });
        
        // Add foreign key constraint in separate statement
        Schema::table('group3', function (Blueprint $table) {
            $table->foreign('group2_id')
                  ->references('idGroup2')
                  ->on('group2')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group3');
    }
};
