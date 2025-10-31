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
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('primary_region_id')->nullable()->after('source_data')->constrained('regions')->onDelete('set null');
            $table->foreignId('primary_witel_id')->nullable()->after('primary_region_id')->constrained('witels')->onDelete('set null');
            
            $table->index('primary_region_id');
            $table->index('primary_witel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['primary_region_id']);
            $table->dropForeign(['primary_witel_id']);
            $table->dropColumn(['primary_region_id', 'primary_witel_id']);
        });
    }
};
