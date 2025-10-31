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
        Schema::table('revenues', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('notes')->constrained()->onDelete('set null');
            $table->foreignId('witel_id')->nullable()->after('region_id')->constrained()->onDelete('set null');
            
            $table->index('region_id');
            $table->index('witel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenues', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['witel_id']);
            $table->dropColumn(['region_id', 'witel_id']);
        });
    }
};
