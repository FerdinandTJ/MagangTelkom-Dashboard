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
        Schema::table('group4', function (Blueprint $table) {
            $table->decimal('revenue_target', 15, 2)->default(0)->after('revenue_realisasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group4', function (Blueprint $table) {
            $table->dropColumn('revenue_target');
        });
    }
};
