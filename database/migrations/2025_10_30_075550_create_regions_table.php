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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('HQ, REG1, REG2, REG3, REG4, REG5');
            $table->string('name', 100)->comment('Region name');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('code');
        });

        // Insert master data
        DB::table('regions')->insert([
            ['code' => 'HQ', 'name' => 'Headquarters', 'description' => 'Telkom Headquarters', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'REG1', 'name' => 'Telkom Region 1', 'description' => 'Sumatera', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'REG2', 'name' => 'Telkom Region 2', 'description' => 'Jakarta, Banten, Jawa Barat', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'REG3', 'name' => 'Telkom Region 3', 'description' => 'Jawa Tengah & DIY', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'REG4', 'name' => 'Telkom Region 4', 'description' => 'Jawa Timur', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'REG5', 'name' => 'Telkom Region 5', 'description' => 'Bali, Nusa Tenggara, Kalimantan, Sulawesi, Maluku, Papua', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
