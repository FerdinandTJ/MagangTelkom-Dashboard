<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Performance Upload Logs Table
 * 
 * Table untuk mencatat semua aktivitas upload, update, dan delete file Performance AM
 * 
 * Struktur:
 * - id (auto increment, primary key)
 * - tahun (year) - Tahun data yang diupload
 * - quartal (ENUM: Q1, Q2, Q3, Q4) - Quarter data
 * - file_name (varchar 255) - Nama file original yang diupload user
 * - stored_path (varchar 255, nullable) - Path file tersimpan di storage (jika disimpan)
 * - uploaded_by (FK to users) - User yang melakukan aksi
 * - row_count (integer) - Jumlah baris data yang diimport
 * - file_size (decimal) - Ukuran file dalam KB
 * - status (ENUM: Upload, Update, Delete) - Jenis aktivitas
 * - created_at (timestamp) - Waktu aktivitas dilakukan
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_upload_logs', function (Blueprint $table) {
            // PRIMARY KEY: id (auto increment)
            $table->id()->comment('Primary Key - Auto Increment');
            
            // TAHUN: Tahun data yang diupload
            $table->year('tahun')->comment('Tahun data Performance AM');
            
            // QUARTAL: Quarter data (Q1, Q2, Q3, Q4)
            $table->enum('quartal', ['Q1', 'Q2', 'Q3', 'Q4'])
                  ->comment('Quarter data (Q1: Jan-Mar, Q2: Apr-Jun, Q3: Jul-Sep, Q4: Okt-Des)');
            
            // FILE_NAME: Nama file original dari user
            $table->string('file_name', 255)->comment('Nama file original yang diupload user');
            
            // STORED_PATH: Path file tersimpan (nullable jika tidak menyimpan file)
            $table->string('stored_path', 255)->nullable()->comment('Path file tersimpan di storage (optional)');
            
            // UPLOADED_BY: Foreign Key ke users table
            $table->foreignId('uploaded_by')
                  ->comment('FK to users.id - User yang melakukan upload/update/delete')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            // ROW_COUNT: Jumlah baris data yang diimport
            $table->integer('row_count')->default(0)->comment('Jumlah baris data yang diimport/diproses');
            
            // FILE_SIZE: Ukuran file dalam KB
            $table->decimal('file_size', 10, 2)->default(0)->comment('Ukuran file dalam KB');
            
            // STATUS: Jenis aktivitas (Upload, Update, Delete)
            $table->enum('status', ['Upload', 'Update', 'Delete'])
                  ->comment('Status aktivitas: Upload (upload baru), Update (replace data), Delete (hapus data)');
            
            // CREATED_AT: Timestamp aktivitas (hanya created_at, tidak perlu updated_at)
            $table->timestamp('created_at')->useCurrent()->comment('Waktu aktivitas dilakukan');
            
            // INDEXES untuk performa query
            $table->index(['tahun', 'quartal'], 'idx_year_quarter');
            $table->index('uploaded_by', 'idx_uploaded_by');
            $table->index('status', 'idx_status');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_upload_logs');
    }
};
