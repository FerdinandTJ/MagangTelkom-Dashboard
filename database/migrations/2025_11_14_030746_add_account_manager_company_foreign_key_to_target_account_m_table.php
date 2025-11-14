<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add Foreign Key to target_account_m table
 * 
 * Purpose: Establish One-to-One relationship between target_account_m and account_manager_company
 * 
 * Changes:
 * - Add account_manager_company_id column (nullable, bigint unsigned)
 * - Add foreign key constraint to account_manager_company.id
 * - Add index for query performance
 * 
 * Relationship Logic:
 * - Each target record belongs to ONE account_manager_company assignment
 * - This links targets directly to the AM-Company relationship
 * - Allows tracking which AM's target for which specific company
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('target_account_m', function (Blueprint $table) {
            // Add foreign key column (after id, nullable for existing data)
            $table->unsignedBigInteger('account_manager_company_id')
                  ->nullable()
                  ->after('id')
                  ->comment('FK to account_manager_company.id - One-to-One relation');
            
            // Add index for query performance
            $table->index('account_manager_company_id', 'idx_target_am_company');
            
            // Add foreign key constraint
            $table->foreign('account_manager_company_id', 'fk_target_am_company')
                  ->references('id')
                  ->on('account_manager_company')
                  ->onDelete('cascade')  // Delete target when AM-Company assignment is deleted
                  ->onUpdate('cascade'); // Update if ID changes (unlikely with auto-increment)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_account_m', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign('fk_target_am_company');
            
            // Drop index
            $table->dropIndex('idx_target_am_company');
            
            // Drop column
            $table->dropColumn('account_manager_company_id');
        });
    }
};
