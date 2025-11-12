<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Account Managers Table
 * 
 * Table account_managers berisi data Account Manager (AM) yang mengelola perusahaan
 * 
 * Struktur:
 * - nik (VARCHAR 10, PRIMARY KEY) - NIK Account Manager
 * - nama (VARCHAR 50, NOT NULL) - Nama lengkap AM
 * - posisi (ENUM, NOT NULL) - Jabatan: AM, AM1, AM1PRO, AM2, AM2PRO, AM3, EAM, SAM
 * - no_gsm (VARCHAR 15, NULLABLE) - Nomor telepon
 * - idwitels (FK to witels) - Witel tempat AM bertugas (One-to-One)
 * 
 * Relasi:
 * - account_managers → witels (One-to-One via idwitels FK)
 * - account_managers ↔ companies (Many-to-Many via pivot: account_manager_company)
 * - account_managers → lini_waktu (One-to-Many)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_managers', function (Blueprint $table) {
            // PRIMARY KEY: nik (NIK Account Manager, 10 digit)
            $table->string('nik', 10)->primary()->comment('NIK Account Manager (Primary Key)');
            
            // BASIC INFO: Informasi dasar Account Manager
            $table->string('nama', 50)->comment('Nama lengkap Account Manager');
            
            // POSISI: Jabatan Account Manager dengan ENUM
            $table->enum('posisi', [
                'AM',       // Account Manager
                'AM1',      // Account Manager 1
                'AM1PRO',   // Account Manager 1 Professional
                'AM2',      // Account Manager 2
                'AM2PRO',   // Account Manager 2 Professional
                'AM3',      // Account Manager 3
                'EAM',      // Enterprise Account Manager
                'SAM'       // Senior Account Manager
            ])->comment('Jabatan/Posisi Account Manager');
            
            // CONTACT: Nomor telepon (optional)
            $table->string('no_gsm', 15)->nullable()->comment('Nomor telepon/HP Account Manager');
            
            // FOREIGN KEY: Relasi One-to-One dengan table witels
            // Setiap AM ditugaskan di satu WITEL tertentu
            $table->unsignedBigInteger('idwitels')->nullable()->comment('FK to witels - WITEL tempat AM bertugas');
            
            // TIMESTAMPS: created_at, updated_at
            $table->timestamps();
            
            // INDEX: untuk performa query berdasarkan witel dan posisi
            $table->index('idwitels', 'idx_am_witel');
            $table->index('posisi', 'idx_am_posisi');
            
            // FOREIGN KEY CONSTRAINT: idwitels → witels.idwitels
            // NOTE: Migration witels harus dijalankan terlebih dahulu
            // Untuk sementara tidak di-define, akan ditambahkan di migration berikutnya setelah witels ter-restructure
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop table account_managers
        Schema::dropIfExists('account_managers');
    }
};
