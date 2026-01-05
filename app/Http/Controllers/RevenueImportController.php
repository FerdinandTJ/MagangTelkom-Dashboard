<?php

namespace App\Http\Controllers;

use App\Imports\RevenueImport;
use App\Models\RevenueUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RevenueImportController extends Controller
{
    /**
     * Handle revenue data import (both Quick Upload and per-month upload)
     */
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'year' => 'nullable|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $file = $request->file('file');
        $year = $request->input('year');
        $month = $request->input('month');
        
        // Determine import type
        $isQuickUpload = !$year; // Quick Upload if no year specified (auto-detect)
        $isMonthlyUpload = $year && $month; // Monthly upload if both year and month specified
        
        Log::info('Revenue import started', [
            'type' => $isQuickUpload ? 'quick' : ($isMonthlyUpload ? 'monthly' : 'yearly'),
            'year' => $year,
            'month' => $month,
            'filename' => $file->getClientOriginalName()
        ]);

        DB::beginTransaction();
        
        try {
            // Create importer instance
            $importer = new RevenueImport($year);
            
            // Import the file
            Excel::import($importer, $file);
            
            // Get import statistics
            $stats = $importer->getStats();
            $errors = $importer->getErrorReport();
            
            // Check if import was successful
            if ($stats['total_errors'] > 0) {
                // Partial success - still save upload history before committing
                $fileSize = $file->getSize() / 1024; // KB
                $originalFilename = $file->getClientOriginalName();
                $uploadedBy = auth()->id();
                
                foreach ($stats['year_stats'] as $year => $yearStats) {
                    // Double check: only update if months_imported exists and not empty
                    if (isset($yearStats['months_imported']) && !empty($yearStats['months_imported'])) {
                        foreach ($yearStats['months_imported'] as $month) {
                            // Hapus file lama jika ada
                            $existingUpload = RevenueUpload::where('tahun', $year)
                                ->where('bulan', $month)
                                ->first();

                            if ($existingUpload && $existingUpload->stored_path) {
                                if (Storage::exists($existingUpload->stored_path)) {
                                    Storage::delete($existingUpload->stored_path);
                                }
                            }

                            // Simpan file baru
                            $timestamp = now()->format('YmdHis');
                            $storedPath = $file->storeAs(
                                "revenue-uploads/{$year}",
                                "{$month}_{$timestamp}_{$originalFilename}",
                                'local'
                            );
                            
                            RevenueUpload::updateOrCreate(
                                [
                                    'tahun' => $year,
                                    'bulan' => $month,
                                ],
                                [
                                    'original_filename' => $originalFilename,
                                    'stored_path' => $storedPath,
                                    'uploaded_by' => $uploadedBy,
                                    'row_count' => $yearStats['success'] ?? 0,
                                    'file_size_kb' => $fileSize,
                                ]
                            );
                        }
                    }
                }
                
                DB::commit();
                
                $warningMessage = "Import completed with {$stats['total_errors']} errors. {$stats['total_success']} records imported successfully.";
                
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => $warningMessage,
                        'import_stats' => $stats,
                        'import_errors' => $errors
                    ], 200);
                }
                
                return back()->with([
                    'warning' => $warningMessage,
                    'import_stats' => $stats,
                    'import_errors' => $errors
                ]);
            }
            
            // Full success - save upload history
            $fileSize = $file->getSize() / 1024; // KB
            $originalFilename = $file->getClientOriginalName();
            $uploadedBy = auth()->id();
            
            foreach ($stats['year_stats'] as $year => $yearStats) {
                // Double check: only update if months_imported exists and not empty
                if (isset($yearStats['months_imported']) && !empty($yearStats['months_imported'])) {
                    foreach ($yearStats['months_imported'] as $month) {
                        // Hapus file lama jika ada
                        $existingUpload = RevenueUpload::where('tahun', $year)
                            ->where('bulan', $month)
                            ->first();

                        if ($existingUpload && $existingUpload->stored_path) {
                            if (Storage::exists($existingUpload->stored_path)) {
                                Storage::delete($existingUpload->stored_path);
                            }
                        }

                        // Simpan file baru
                        $timestamp = now()->format('YmdHis');
                        $storedPath = $file->storeAs(
                            "revenue-uploads/{$year}",
                            "{$month}_{$timestamp}_{$originalFilename}",
                            'local'
                        );
                        
                        RevenueUpload::updateOrCreate(
                            [
                                'tahun' => $year,
                                'bulan' => $month,
                            ],
                            [
                                'original_filename' => $originalFilename,
                                'stored_path' => $storedPath,
                                'uploaded_by' => $uploadedBy,
                                'row_count' => $yearStats['success'] ?? 0,
                                'file_size_kb' => $fileSize,
                            ]
                        );
                    }
                }
            }
            
            // Commit transaction
            DB::commit();
            
            $message = $isQuickUpload 
                ? "Successfully imported {$stats['total_success']} records across " . count($stats['years_imported']) . " year(s): " . implode(', ', $stats['years_imported'])
                : "Successfully imported {$stats['total_success']} records for year {$year}";
            
            Log::info('Revenue import completed successfully', $stats);
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'import_stats' => $stats
                ], 200);
            }
            
            return back()->with([
                'success' => $message,
                'import_stats' => $stats
            ]);
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            Log::error('Revenue import validation failed', [
                'errors' => $errorMessages
            ]);
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Import validation failed. Please check your file format.',
                    'validation_errors' => $errorMessages
                ], 422);
            }
            
            return back()->with([
                'error' => 'Import validation failed. Please check your file format.',
                'validation_errors' => $errorMessages
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Revenue import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Import failed: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download uploaded revenue file
     */
    public function downloadFile(Request $request, int $year, int $month)
    {
        try {
            $upload = RevenueUpload::where('tahun', $year)
                ->where('bulan', $month)
                ->firstOrFail();
            
            // Check if file path exists
            if (!$upload->stored_path) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File tidak tersedia. Data diimport sebelum fitur penyimpanan file diaktifkan.'
                ], 404);
            }
            
            // Check if file exists in storage
            if (!Storage::exists($upload->stored_path)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File tidak ditemukan di server. Mungkin sudah dihapus.'
                ], 404);
            }
            
            return Storage::download($upload->stored_path, $upload->original_filename);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data upload tidak ditemukan untuk bulan dan tahun tersebut.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengunduh file: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete all revenue data for a specific year
     */
    public function deleteYear(Request $request, int $year)
    {
        try {
            DB::beginTransaction();
            
            // Get all uploads for this year
            $uploads = RevenueUpload::where('tahun', $year)->get();
            
            if ($uploads->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data untuk tahun ' . $year
                ], 404);
            }
            
            $deletedFiles = 0;
            $deletedRevenues = 0;
            $deletedTargets = 0;
            
            // Delete files from storage
            foreach ($uploads as $upload) {
                if ($upload->stored_path && Storage::exists($upload->stored_path)) {
                    Storage::delete($upload->stored_path);
                    $deletedFiles++;
                }
            }
            
            // Delete revenues
            $deletedRevenues = DB::table('revenues')
                ->where('tahun', $year)
                ->delete();
            
            // Delete company targets
            $deletedTargets = DB::table('company_targets')
                ->where('tahun', $year)
                ->delete();
            
            // Delete upload records
            RevenueUpload::where('tahun', $year)->delete();
            
            DB::commit();
            
            Log::info('Revenue data deleted for year', [
                'year' => $year,
                'deleted_files' => $deletedFiles,
                'deleted_revenues' => $deletedRevenues,
                'deleted_targets' => $deletedTargets,
                'deleted_by' => auth()->user()->name ?? 'system'
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => "Data tahun {$year} berhasil dihapus",
                'details' => [
                    'files' => $deletedFiles,
                    'revenues' => $deletedRevenues,
                    'targets' => $deletedTargets
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Revenue year deletion failed', [
                'year' => $year,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
