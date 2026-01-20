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
        Schema::table('revenue_uploads', function (Blueprint $table) {
            // Action type: 'upload', 'replace', 'delete'
            $table->string('action', 20)->default('upload')->after('uploaded_by');
            
            // Optional description/notes for the action
            $table->text('description')->nullable()->after('action');
            
            // IP Address of the user performing the action
            $table->string('ip_address', 45)->nullable()->after('description');
            
            // User agent for additional tracking
            $table->string('user_agent')->nullable()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenue_uploads', function (Blueprint $table) {
            $table->dropColumn(['action', 'description', 'ip_address', 'user_agent']);
        });
    }
};
