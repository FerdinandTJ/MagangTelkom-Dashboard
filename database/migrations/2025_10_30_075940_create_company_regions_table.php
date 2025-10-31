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
        Schema::create('company_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->foreignId('witel_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_primary')->default(false)->comment('Is this the primary region?');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'region_id', 'witel_id'], 'unique_company_region_witel');
            $table->index('company_id');
            $table->index('region_id');
            $table->index('witel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_regions');
    }
};
