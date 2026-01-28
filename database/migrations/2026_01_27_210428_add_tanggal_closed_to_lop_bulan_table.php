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
        Schema::table('lop_bulan', function (Blueprint $table) {
            $table->integer('tanggal_closed')->nullable()->after('Progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lop_bulan', function (Blueprint $table) {
            $table->dropColumn('tanggal_closed');
        });
    }
};
