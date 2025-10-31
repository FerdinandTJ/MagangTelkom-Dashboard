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
        Schema::create('witels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->string('code', 20)->unique()->comment('WITEL Code');
            $table->string('name', 100)->comment('WITEL Name');
            $table->string('province', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('region_id');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('witels');
    }
};
