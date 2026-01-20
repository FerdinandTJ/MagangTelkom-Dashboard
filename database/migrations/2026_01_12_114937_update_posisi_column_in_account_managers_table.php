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
        Schema::table('account_managers', function (Blueprint $table) {
            // Change posisi column from enum to varchar(50)
            $table->string('posisi', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_managers', function (Blueprint $table) {
            // Revert back to enum if needed
            $table->enum('posisi', ['AM', 'SM', 'ASM'])->nullable()->change();
        });
    }
};
