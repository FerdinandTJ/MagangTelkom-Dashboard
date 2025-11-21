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
        Schema::create('group1', function (Blueprint $table) {
            $table->id('idGroup1');
            $table->string('nama_group1', 45);
            $table->string('company_id', 25);  // Changed to string to match nip_nas
            $table->timestamps();

            // Add index first
            $table->index('company_id');
        });
        
        // Add foreign key constraint in separate statement
        Schema::table('group1', function (Blueprint $table) {
            $table->foreign('company_id')
                  ->references('nip_nas')  // Reference to nip_nas, not id
                  ->on('companies')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group1');
    }
};
