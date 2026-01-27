<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lop_bulan', function (Blueprint $table) {
            // Drop foreign keys yang depend on unique constraint
            $table->dropForeign(['ID_LOP']);
            $table->dropForeign(['bulan_id']);
            
            // Drop unique constraint
            $table->dropUnique(['ID_LOP', 'bulan_id']);
            
            // Recreate foreign keys tanpa unique constraint
            $table->foreign('ID_LOP')->references('ID_LOP')->on('lop')->onDelete('cascade');
            $table->foreign('bulan_id')->references('id')->on('bulan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lop_bulan', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['ID_LOP']);
            $table->dropForeign(['bulan_id']);
            
            // Restore unique constraint
            $table->unique(['ID_LOP', 'bulan_id']);
            
            // Recreate foreign keys
            $table->foreign('ID_LOP')->references('ID_LOP')->on('lop')->onDelete('cascade');
            $table->foreign('bulan_id')->references('id')->on('bulan')->onDelete('cascade');
        });
    }
};
