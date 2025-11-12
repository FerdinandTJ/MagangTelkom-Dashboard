<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add Foreign Key Constraint to account_managers
 * 
 * Menambahkan FK constraint yang tertunda dari migration sebelumnya
 * Constraint ini baru bisa ditambahkan setelah table witels ter-restructure
 * 
 * FK: account_managers.idwitels → witels.idwitels
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_managers', function (Blueprint $table) {
            // Add FOREIGN KEY CONSTRAINT: idwitels → witels.idwitels
            $table->foreign('idwitels')
                  ->references('idwitels')
                  ->on('witels')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_managers', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['idwitels']);
        });
    }
};
