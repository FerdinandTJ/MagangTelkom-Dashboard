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
        Schema::table('account_managers', function (Blueprint $table) {
            // Update posisi enum to include more values
            DB::statement("ALTER TABLE account_managers MODIFY posisi ENUM('AM', 'AM 1', 'AM 1 PRO', 'AM 2', 'AM 2 PRO', 'AM 3', 'EAM', 'SAM', 'SM', 'ASM') NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_managers', function (Blueprint $table) {
            // Revert back to original enum
            DB::statement("ALTER TABLE account_managers MODIFY posisi ENUM('AM', 'SM', 'ASM') NULL");
        });
    }
};
