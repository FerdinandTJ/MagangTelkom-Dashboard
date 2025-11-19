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
        // Add target column to companies (to store target at company level)
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'target')) {
                $table->decimal('target', 15, 2)->nullable()->default(0)->after('source_data');
            }
        });

        // Drop the old revenues table - we will keep revenue realization in group4
        if (Schema::hasTable('revenues')) {
            Schema::dropIfExists('revenues');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove target column from companies
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'target')) {
                $table->dropColumn('target');
            }
        });

        // Recreate a minimal revenues table in case of rollback
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->string('nip_nas', 25)->index();
            $table->year('tahun')->nullable();
            $table->tinyInteger('bulan')->unsigned()->nullable();
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->timestamps();
        });
    }
};
