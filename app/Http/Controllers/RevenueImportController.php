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
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'year' => 'nullable|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
        ], [
            'file.required' => 'File harus diupload',
            'file.file' => 'File yang diupload tidak valid',
            'file.mimes' => 'Format file harus Excel (.xlsx, .xls) atau CSV (.csv)',
            'file.max' => 'Ukuran file maksimal 10 MB',
            'year.integer' => 'Tahun harus berupa angka',
            'year.min' => 'Tahun minimal 2020',
            'year.max' => 'Tahun maksimal 210',
            'month.integer' => 'Bulan harus berupa angka',
            'month.min' => 'Bulan harus antara 1-12',
            'month.max' => 'Bulan harus antara 1-12',
        ]);

        $file = $request->file('file');
        
        // Additional validation: Check file extension explicitly
        $allowedExtensions = ['xlsx', 'xls', 'csv'];
        $fileExtension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            return response()->json([
                'message' => 'Format file tidak didukung',
                'error' => "File dengan ekstensi .{$fileExtension} tidak diperbolehkan. Hanya menerima: .xlsx, .xls, .csv",
                'errors' => [
                    'file' => ["Format file .{$fileExtension} tidak valid. Gunakan Excel (.xlsx, .xls) atau CSV (.csv)"]
                ]
            ], 422);
        }
        
        // Validate actual file content (MIME type)
        $allowedMimeTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel', // .xls
            'text/csv', // .csv
            'text/plain', // some CSV files
            'application/csv',
            'application/excel',
        ];
        
        $fileMimeType = $file->getMimeType();
        
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            return response()->json([
                'message' => 'Tipe file tidak valid',
                'error' => "File yang diupload bukan file Excel atau CSV yang valid (MIME: {$fileMimeType})",
                'errors' => [
                    'file' => ['File yang diupload bukan file Excel atau CSV yang valid. Pastikan file tidak corrupt atau diubah ekstensinya.']
                ]
            ], 422);
        }
        
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
            // Create importer instance with year and month (if specified)
            $importer = new RevenueImport($year, $month);
            
            // Import the file
            Excel::import($importer, $file);
            
            // Get import statistics
            $stats = $importer->getStats();
            $errors = $importer->getErrorReport();
            
            // Validasi: Cek apakah ada data yang berhasil di-import
            if ($stats['total_records'] === 0 || empty($stats['years_imported'])) {
                DB::rollBack();
                
                Log::warning('No valid data imported from file', [
                    'filename' => $file->getClientOriginalName(),
                    'stats' => $stats
                ]);
                
                return response()->json([
                    'message' => 'Tidak ada data yang berhasil di-import',
                    'error' => 'File yang Anda upload tidak memiliki struktur revenue yang valid. ' .
                              'Pastikan file Excel memiliki sheet dengan nama "Rev YYYY" (contoh: Rev 2024) ' .
                              'dan memiliki kolom: NIP_NAS, STANDARD_NAME, SOURCE_DATA, GROUP1-GROUP4, serta kolom bulan 1-12.',
                    'errors' => [
                        'file' => [
                            'File tidak memiliki data revenue yang valid',
                            'Pastikan file menggunakan template yang benar dengan kolom: NIP_NAS, STANDARD_NAME, SOURCE_DATA, GROUP1-4, dan bulan 1-12',
                            'File harus memiliki sheet bernama "Rev YYYY" (contoh: Rev 2024, Rev 2025)'
                        ]
                    ]
                ], 422);
            }
            
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
                            // Get latest record for this month to check if it's deleted
                            $existingUpload = RevenueUpload::where('tahun', $year)
                                ->where('bulan', $month)
                                ->orderBy('created_at', 'desc')
                                ->first();

                            // Only delete file if latest record is not a delete action
                            if ($existingUpload && $existingUpload->action != 'delete' && $existingUpload->stored_path) {
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
                            
                            // Determine action: replace if existing, upload if new
                            $action = $existingUpload ? 'replace' : 'upload';
                            $description = $this->buildUploadDescription(
                                (bool) $existingUpload,
                                $year,
                                $month,
                                $yearStats,
                                $stats
                            );
                            
                            RevenueUpload::create([
                                'tahun' => $year,
                                'bulan' => $month,
                                'original_filename' => $originalFilename,
                                'stored_path' => $storedPath,
                                'uploaded_by' => $uploadedBy,
                                'action' => $action,
                                'description' => $description,
                                'ip_address' => $request->ip(),
                                'user_agent' => $request->userAgent(),
                                'row_count' => $yearStats['success'] ?? 0,
                                'file_size_kb' => $fileSize,
                            ]);
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
                        // Get latest record for this month to check if it's deleted
                        $existingUpload = RevenueUpload::where('tahun', $year)
                            ->where('bulan', $month)
                            ->orderBy('created_at', 'desc')
                            ->first();

                        // Only delete file if latest record is not a delete action
                        if ($existingUpload && $existingUpload->action != 'delete' && $existingUpload->stored_path) {
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
                        
                        // Determine action: replace if existing, upload if new
                        $action = $existingUpload ? 'replace' : 'upload';
                        $description = $this->buildUploadDescription(
                            (bool) $existingUpload,
                            $year,
                            $month,
                            $yearStats,
                            $stats
                        );
                        
                        RevenueUpload::create([
                            'tahun' => $year,
                            'bulan' => $month,
                            'original_filename' => $originalFilename,
                            'stored_path' => $storedPath,
                            'uploaded_by' => $uploadedBy,
                            'action' => $action,
                            'description' => $description,
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'row_count' => $yearStats['success'] ?? 0,
                            'file_size_kb' => $fileSize,
                        ]);
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
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            Log::error('Revenue import validation failed', [
                'errors' => $errorMessages,
                'file' => $file->getClientOriginalName()
            ]);
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'message' => 'Validasi data gagal',
                    'error' => 'File Excel mengandung data yang tidak valid. Silakan periksa format dan isi data Anda.',
                    'errors' => [
                        'file' => $errorMessages
                    ]
                ], 422);
            }
            
            return back()->with([
                'error' => 'Validasi data gagal. File Excel mengandung data yang tidak valid.',
                'validation_errors' => $errorMessages
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = $e->getMessage();
            
            Log::error('Revenue import failed', [
                'error' => $errorMessage,
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Cek apakah ini error validasi struktur
            $isStructureError = str_contains($errorMessage, 'Struktur Excel') || 
                               str_contains($errorMessage, 'Kolom yang hilang') ||
                               str_contains($errorMessage, 'tidak memiliki data') ||
                               str_contains($errorMessage, 'File Excel kosong') ||
                               str_contains($errorMessage, 'tidak lengkap');
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'message' => $isStructureError ? 'Struktur file tidak sesuai' : 'Import gagal',
                    'error' => $errorMessage,
                    'errors' => [
                        'file' => [$errorMessage]
                    ]
                ], $isStructureError ? 422 : 500);
            }
            
            return back()->with('error', 'Import gagal: ' . $errorMessage);
        }
    }

    /**
     * Build upload description with region/witel stats
     */
    protected function buildUploadDescription(bool $isReplace, int $year, int $month, array $yearStats, array $allStats): string
    {
        $action = $isReplace ? "Replaced existing" : "Uploaded new";
        $recordCount = $yearStats['success'] ?? 0;
        
        $description = "{$action} data for {$year}/{$month} with {$recordCount} records";
        
        // Add region & witel stats if available
        if (isset($allStats['region_witel_stats']) && !empty($allStats['region_witel_stats'])) {
            $rwStats = $allStats['region_witel_stats'];
            $parts = [];
            
            if (($rwStats['regions_updated'] ?? 0) > 0) {
                $parts[] = "{$rwStats['regions_updated']} regions updated";
            }
            if (($rwStats['regions_created'] ?? 0) > 0) {
                $parts[] = "{$rwStats['regions_created']} regions created";
            }
            if (($rwStats['witels_updated'] ?? 0) > 0) {
                $parts[] = "{$rwStats['witels_updated']} witels updated";
            }
            if (($rwStats['witels_created'] ?? 0) > 0) {
                $parts[] = "{$rwStats['witels_created']} witels created";
            }
            
            if (!empty($parts)) {
                $description .= "; Master data: " . implode(', ', $parts);
            }
        }
        
        return $description;
    }

    /**
     * Download revenue data for a specific month (generated from database)
     */
    public function downloadFile(Request $request, int $year, int $month)
    {
        try {
            // Get revenue data for the specific month
            $revenues = DB::table('revenues as r')
                ->join('group4 as g4', 'r.group4_id', '=', 'g4.idGroup4')
                ->join('group3 as g3', 'g4.group3_id', '=', 'g3.idGroup3')
                ->join('group2 as g2', 'g3.group2_id', '=', 'g2.idGroup2')
                ->join('group1 as g1', 'g2.group1_id', '=', 'g1.idGroup1')
                ->join('companies as c', 'g1.company_id', '=', 'c.nip_nas')
                ->leftJoin('company_targets as ct', function($join) use ($year, $month) {
                    $join->on('c.nip_nas', '=', 'ct.nip_nas')
                         ->where('ct.tahun', '=', $year)
                         ->where('ct.bulan', '=', $month);
                })
                ->where('r.tahun', $year)
                ->where('r.bulan', $month)
                ->select(
                    'c.subsegment as SUB_SEGMENT',
                    'c.nip_nas as NIP_NAS',
                    'c.nama_perusahaan as STANDARD_NAME',
                    'c.source_data as SOURCE_DATA',
                    'c.idwitels as WITEL_ID',
                    'g1.nama_group1 as GROUP1',
                    'g2.nama_group2 as GROUP2',
                    'g3.nama_group3 as GROUP3',
                    'g4.nama_group4 as GROUP4',
                    'r.revenue_realisasi',
                    DB::raw('COALESCE(ct.target_revenue, 0) as revenue_target')
                )
                ->orderBy('c.nip_nas')
                ->orderBy('g1.nama_group1')
                ->get();

            if ($revenues->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Tidak ada data revenue untuk bulan {$month} tahun {$year}"
                ], 404);
            }

            // Create Excel file
            $spreadsheet = new Spreadsheet();
            
            // First, add reference sheet at index 0
            $this->addRegionWitelReferenceSheet($spreadsheet);
            
            // Now create data sheet
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $dataSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, "{$monthName} {$year}");
            $spreadsheet->addSheet($dataSheet, 1);

            // Set headers - match import format
            $headers = ['SUB_SEGMENT', 'NIP_NAS', 'STANDARD_NAME', 'SOURCE_DATA', 'WITEL_ID', 'GROUP1', 'GROUP2', 'GROUP3', 'GROUP4', $month, 'TARGET_' . $month];

            // Write headers
            $col = 'A';
            foreach ($headers as $header) {
                $dataSheet->setCellValue($col . '1', $header);
                $dataSheet->getStyle($col . '1')->getFont()->setBold(true);
                $col++;
            }

            // Write data rows with grouping
            $row = 2;
            $prevNipNas = null;
            $prevGroup1 = null;
            $prevGroup2 = null;
            
            foreach ($revenues as $data) {
                $col = 'A';
                
                // SUB_SEGMENT - show only if different
                $dataSheet->setCellValue($col . $row, 
                    ($data->NIP_NAS !== $prevNipNas) ? $data->SUB_SEGMENT : '');
                $col++;
                
                // NIP_NAS - show only if different
                $dataSheet->setCellValue($col . $row, 
                    ($data->NIP_NAS !== $prevNipNas) ? $data->NIP_NAS : '');
                $col++;
                
                // STANDARD_NAME - show only if NIP_NAS different
                $dataSheet->setCellValue($col . $row, 
                    ($data->NIP_NAS !== $prevNipNas) ? $data->STANDARD_NAME : '');
                $col++;
                
                // SOURCE_DATA - show only if NIP_NAS different
                $dataSheet->setCellValue($col . $row, 
                    ($data->NIP_NAS !== $prevNipNas) ? $data->SOURCE_DATA : '');
                $col++;
                
                // WITEL_ID - show only if NIP_NAS different
                $dataSheet->setCellValue($col . $row, 
                    ($data->NIP_NAS !== $prevNipNas) ? $data->WITEL_ID : '');
                $col++;
                
                // GROUP1 - show only if different
                $dataSheet->setCellValue($col . $row, 
                    ($data->NIP_NAS !== $prevNipNas || $data->GROUP1 !== $prevGroup1) ? $data->GROUP1 : '');
                $col++;
                
                // GROUP2 - show only if different
                $dataSheet->setCellValue($col . $row, 
                    ($data->NIP_NAS !== $prevNipNas || $data->GROUP1 !== $prevGroup1 || $data->GROUP2 !== $prevGroup2) ? $data->GROUP2 : '');
                $col++;
                
                // GROUP3 and GROUP4 - always show
                $dataSheet->setCellValue($col . $row, $data->GROUP3);
                $col++;
                $dataSheet->setCellValue($col . $row, $data->GROUP4);
                $col++;
                
                // Revenue for this month
                $dataSheet->setCellValue($col . $row, $data->revenue_realisasi);
                $col++;
                
                // Target for this month - show only if NIP_NAS different (target is per company per month)
                $dataSheet->setCellValue($col . $row, 
                    ($data->NIP_NAS !== $prevNipNas) ? $data->revenue_target : '');
                
                // Update previous values
                $prevNipNas = $data->NIP_NAS;
                $prevGroup1 = $data->GROUP1;
                $prevGroup2 = $data->GROUP2;
                
                $row++;
            }

            // Set data sheet as active sheet (user will see this when opening file)
            $spreadsheet->setActiveSheetIndex(1);

            // Save to temporary file
            $filename = "Revenue_{$year}_{$monthName}_" . date('Ymd_His') . ".xlsx";
            $tempPath = storage_path('app/temp/' . $filename);
            
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);

            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Error downloading month data', [
                'year' => $year,
                'month' => $month,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete revenue data for a specific month
     */
    public function deleteMonth(Request $request, int $year, int $month)
    {
        try {
            DB::beginTransaction();
            
            // Get latest upload record for this month
            $upload = RevenueUpload::where('tahun', $year)
                ->where('bulan', $month)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Check if data exists and is not already deleted
            if (!$upload || $upload->action == 'delete') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Tidak ada data untuk bulan {$month} tahun {$year}"
                ], 404);
            }
            
            // Get all group4 IDs that belong to companies in this month's data
            $group4Ids = DB::table('revenues as r')
                ->join('group4 as g4', 'r.group4_id', '=', 'g4.idGroup4')
                ->join('group3 as g3', 'g4.group3_id', '=', 'g3.idGroup3')
                ->join('group2 as g2', 'g3.group2_id', '=', 'g2.idGroup2')
                ->join('group1 as g1', 'g2.group1_id', '=', 'g1.idGroup1')
                ->where('r.tahun', $year)
                ->where('r.bulan', $month)
                ->pluck('r.group4_id')
                ->unique();
            
            // Delete revenues for this month
            $deletedRevenues = DB::table('revenues')
                ->where('tahun', $year)
                ->where('bulan', $month)
                ->delete();
            
            // Delete company targets for this month
            $deletedTargets = DB::table('company_targets')
                ->where('tahun', $year)
                ->where('bulan', $month)
                ->delete();
            
            // Delete file from storage
            if ($upload->stored_path && Storage::exists($upload->stored_path)) {
                Storage::delete($upload->stored_path);
            }
            
            // Create deletion log entry - keep all logs for full audit trail
            RevenueUpload::create([
                'tahun' => $year,
                'bulan' => $month,
                'original_filename' => $upload->original_filename,
                'stored_path' => null, // File already deleted
                'uploaded_by' => auth()->id(),
                'action' => 'delete',
                'description' => "Deleted data for {$year}/{$month}: {$deletedRevenues} revenues, {$deletedTargets} targets removed",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'row_count' => 0,
                'file_size_kb' => 0,
            ]);
            
            // DO NOT delete the upload record - keep all logs for audit trail
            // To check if month has data, query the LATEST record and check if action='delete'
            
            DB::commit();
            
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            
            Log::info('Revenue data deleted for month', [
                'year' => $year,
                'month' => $month,
                'deleted_revenues' => $deletedRevenues,
                'deleted_targets' => $deletedTargets,
                'deleted_by' => auth()->user()->name ?? 'system'
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => "Data {$monthName} {$year} berhasil dihapus",
                'details' => [
                    'revenues' => $deletedRevenues,
                    'targets' => $deletedTargets
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Revenue month deletion failed', [
                'year' => $year,
                'month' => $month,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download all revenue data for a year (combined 12 months in one Excel)
     */
    public function downloadYear(Request $request, int $year)
    {
        try {
            // Get revenue data for the year with company details
            $revenues = DB::table('revenues as r')
                ->join('group4 as g4', 'r.group4_id', '=', 'g4.idGroup4')
                ->join('group3 as g3', 'g4.group3_id', '=', 'g3.idGroup3')
                ->join('group2 as g2', 'g3.group2_id', '=', 'g2.idGroup2')
                ->join('group1 as g1', 'g2.group1_id', '=', 'g1.idGroup1')
                ->join('companies as c', 'g1.company_id', '=', 'c.nip_nas')
                ->leftJoin('company_targets as ct', function($join) use ($year) {
                    $join->on('c.nip_nas', '=', 'ct.nip_nas')
                         ->on('r.bulan', '=', 'ct.bulan')
                         ->where('ct.tahun', '=', $year);
                })
                ->where('r.tahun', $year)
                ->select(
                    'c.subsegment as SUB_SEGMENT',
                    'c.nip_nas as NIP_NAS',
                    'c.nama_perusahaan as STANDARD_NAME',
                    'c.source_data as SOURCE_DATA',
                    'c.idwitels as WITEL_ID',
                    'g1.nama_group1 as GROUP1',
                    'g2.nama_group2 as GROUP2',
                    'g3.nama_group3 as GROUP3',
                    'g4.nama_group4 as GROUP4',
                    'r.bulan',
                    'r.revenue_realisasi',
                    DB::raw('COALESCE(ct.target_revenue, 0) as revenue_target')
                )
                ->orderBy('c.nip_nas')
                ->orderBy('g1.nama_group1')
                ->orderBy('r.bulan')
                ->get();

            if ($revenues->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Tidak ada data revenue untuk tahun {$year}"
                ], 404);
            }

            // Transform data to pivot format (one row per company-group combination)
            $pivotData = [];
            foreach ($revenues as $revenue) {
                $key = $revenue->NIP_NAS . '|' . $revenue->GROUP1 . '|' . $revenue->GROUP2 . '|' . $revenue->GROUP3 . '|' . $revenue->GROUP4;
                
                if (!isset($pivotData[$key])) {
                    $pivotData[$key] = [
                        'SUB_SEGMENT' => $revenue->SUB_SEGMENT,
                        'NIP_NAS' => $revenue->NIP_NAS,
                        'STANDARD_NAME' => $revenue->STANDARD_NAME,
                        'SOURCE_DATA' => $revenue->SOURCE_DATA,
                        'WITEL_ID' => $revenue->WITEL_ID,
                        'GROUP1' => $revenue->GROUP1,
                        'GROUP2' => $revenue->GROUP2,
                        'GROUP3' => $revenue->GROUP3,
                        'GROUP4' => $revenue->GROUP4,
                    ];
                    // Initialize all 12 months to 0
                    for ($m = 1; $m <= 12; $m++) {
                        $pivotData[$key][$m] = 0;
                        $pivotData[$key]['TARGET_' . $m] = 0;
                    }
                }
                // Set the revenue and target for this specific month
                $pivotData[$key][$revenue->bulan] = $revenue->revenue_realisasi;
                $pivotData[$key]['TARGET_' . $revenue->bulan] = $revenue->revenue_target;
            }

            // Create Excel file with PhpSpreadsheet
            $spreadsheet = new Spreadsheet();
            
            // First, add reference sheet at index 0
            $this->addRegionWitelReferenceSheet($spreadsheet);
            
            // Now create data sheet
            $dataSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, "Rev {$year}");
            $spreadsheet->addSheet($dataSheet, 1);

            // Set headers - match import format
            $headers = ['SUB_SEGMENT', 'NIP_NAS', 'STANDARD_NAME', 'SOURCE_DATA', 'WITEL_ID', 'GROUP1', 'GROUP2', 'GROUP3', 'GROUP4'];
            for ($m = 1; $m <= 12; $m++) {
                $headers[] = (string)$m;
                $headers[] = 'TARGET_' . $m;
            }

            // Write headers
            $col = 'A';
            foreach ($headers as $header) {
                $dataSheet->setCellValue($col . '1', $header);
                $dataSheet->getStyle($col . '1')->getFont()->setBold(true);
                $col++;
            }

            // Write data rows with grouping (hide duplicate company info)
            $row = 2;
            $prevNipNas = null;
            $prevGroup1 = null;
            $prevGroup2 = null;
            
            foreach ($pivotData as $data) {
                $col = 'A';
                
                // SUB_SEGMENT - show only if different from previous
                $dataSheet->setCellValue($col . $row, 
                    ($data['NIP_NAS'] !== $prevNipNas) ? $data['SUB_SEGMENT'] : '');
                $col++;
                
                // NIP_NAS - show only if different from previous
                $dataSheet->setCellValue($col . $row, 
                    ($data['NIP_NAS'] !== $prevNipNas) ? $data['NIP_NAS'] : '');
                $col++;
                
                // STANDARD_NAME - show only if NIP_NAS different
                $dataSheet->setCellValue($col . $row, 
                    ($data['NIP_NAS'] !== $prevNipNas) ? $data['STANDARD_NAME'] : '');
                $col++;
                
                // SOURCE_DATA - show only if NIP_NAS different
                $dataSheet->setCellValue($col . $row, 
                    ($data['NIP_NAS'] !== $prevNipNas) ? $data['SOURCE_DATA'] : '');
                $col++;
                
                // WITEL_ID - show only if NIP_NAS different
                $dataSheet->setCellValue($col . $row, 
                    ($data['NIP_NAS'] !== $prevNipNas) ? $data['WITEL_ID'] : '');
                $col++;
                
                // GROUP1 - show only if NIP_NAS or GROUP1 different
                $dataSheet->setCellValue($col . $row, 
                    ($data['NIP_NAS'] !== $prevNipNas || $data['GROUP1'] !== $prevGroup1) ? $data['GROUP1'] : '');
                $col++;
                
                // GROUP2 - show only if NIP_NAS, GROUP1, or GROUP2 different
                $dataSheet->setCellValue($col . $row, 
                    ($data['NIP_NAS'] !== $prevNipNas || $data['GROUP1'] !== $prevGroup1 || $data['GROUP2'] !== $prevGroup2) ? $data['GROUP2'] : '');
                $col++;
                
                // GROUP3 and GROUP4 - always show
                $dataSheet->setCellValue($col . $row, $data['GROUP3']);
                $col++;
                $dataSheet->setCellValue($col . $row, $data['GROUP4']);
                $col++;
                
                // Month columns with target - revenue always show, target only if NIP_NAS different
                for ($m = 1; $m <= 12; $m++) {
                    $dataSheet->setCellValue($col . $row, $data[$m] ?? 0);
                    $col++;
                    // Target per company per month - only show on first row of each company
                    $dataSheet->setCellValue($col . $row, 
                        ($data['NIP_NAS'] !== $prevNipNas) ? ($data['TARGET_' . $m] ?? 0) : '');
                    $col++;
                }
                
                // Update previous values
                $prevNipNas = $data['NIP_NAS'];
                $prevGroup1 = $data['GROUP1'];
                $prevGroup2 = $data['GROUP2'];
                
                $row++;
            }

            // Set data sheet as active sheet (user will see this when opening file)
            $spreadsheet->setActiveSheetIndex(1);

            // Save to temporary file
            $filename = "Revenue_{$year}_Full_Export_" . date('Ymd_His') . ".xlsx";
            $tempPath = storage_path('app/temp/' . $filename);
            
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);

            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error downloading year data', [
                'year' => $year,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Add Region and Witel reference sheet to spreadsheet
     * Creates a reference sheet with region and witel data for user guidance
     */
    private function addRegionWitelReferenceSheet(Spreadsheet $spreadsheet): void
    {
        // Query fresh data from database
        $regions = DB::table('regions')->orderBy('id')->get();
        $witels = DB::table('witels')->orderBy('region_id')->orderBy('idwitels')->get();
        
        // Create new sheet at index 0
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Region_and_Witel');
        $spreadsheet->addSheet($sheet, 0);
        
        // Set headers
        $headers = ['Region ID', 'Nama Region', 'Description', 'ID WITEL', 'Nama Witel'];
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $col++;
        }
        
        // Style header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'] // Blue background (matching revenue template)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        
        $sheet->getStyle([1, 1, 5, 1])->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12);  // Region ID
        $sheet->getColumnDimension('B')->setWidth(15);  // Nama Region
        $sheet->getColumnDimension('C')->setWidth(25);  // Description
        $sheet->getColumnDimension('D')->setWidth(12);  // ID WITEL
        $sheet->getColumnDimension('E')->setWidth(30);  // Nama Witel
        
        // Write data and apply merging for regions
        $row = 2;
        $startMergeRow = 2;
        $currentRegionId = null;
        
        foreach ($witels as $witel) {
            // Find corresponding region
            $region = $regions->firstWhere('id', $witel->region_id);
            
            if (!$region) {
                continue; // Skip if region not found
            }
            
            // Check if we need to merge previous region cells
            if ($currentRegionId !== null && $currentRegionId !== $witel->region_id) {
                // Merge cells for previous region (columns A, B, C)
                if ($row - 1 > $startMergeRow) {
                    $sheet->mergeCells([1, $startMergeRow, 1, $row - 1]); // Region ID
                    $sheet->mergeCells([2, $startMergeRow, 2, $row - 1]); // Nama Region
                    $sheet->mergeCells([3, $startMergeRow, 3, $row - 1]); // Description
                    
                    // Center align merged cells
                    $sheet->getStyle([1, $startMergeRow, 1, $row - 1])
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle([2, $startMergeRow, 2, $row - 1])
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle([3, $startMergeRow, 3, $row - 1])
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
                $startMergeRow = $row;
            }
            
            // Write region data (will be merged later if multiple witels)
            $sheet->setCellValueByColumnAndRow(1, $row, $region->id);
            $sheet->setCellValueByColumnAndRow(2, $row, $region->name);
            $sheet->setCellValueByColumnAndRow(3, $row, $region->description ?? '');
            
            // Write witel data
            $sheet->setCellValueByColumnAndRow(4, $row, $witel->idwitels);
            $sheet->setCellValueByColumnAndRow(5, $row, $witel->nama_witels);
            
            // Apply borders to all cells
            $sheet->getStyle([1, $row, 5, $row])->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);
            
            $currentRegionId = $witel->region_id;
            $row++;
        }
        
        // Merge last region's cells if needed
        if ($row - 1 > $startMergeRow) {
            $sheet->mergeCells([1, $startMergeRow, 1, $row - 1]); // Region ID
            $sheet->mergeCells([2, $startMergeRow, 2, $row - 1]); // Nama Region
            $sheet->mergeCells([3, $startMergeRow, 3, $row - 1]); // Description
            
            // Center align merged cells
            $sheet->getStyle([1, $startMergeRow, 1, $row - 1])
                ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle([2, $startMergeRow, 2, $row - 1])
                ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle([3, $startMergeRow, 3, $row - 1])
                ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        
        // Freeze header row
        $sheet->freezePane('A2');
    }

    /**
     * Download Excel template for import (headers only with interleaved format)
     */
    public function downloadTemplate($year)
    {
        try {
            $spreadsheet = new Spreadsheet();
            
            // First, add reference sheet at index 0
            $this->addRegionWitelReferenceSheet($spreadsheet);
            
            // Now create revenue template sheet
            $revenueSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Revenue ' . $year);
            $spreadsheet->addSheet($revenueSheet, 1);
            
            // Set timezone
            date_default_timezone_set('Asia/Jakarta');
            
            // Build headers in interleaved format: SUB_SEGMENT, NIP_NAS, STANDARD_NAME, SOURCE_DATA, WITEL_ID, GROUP1-4, then 1, TARGET_1, 2, TARGET_2, ...
            $headers = [
                'SUB_SEGMENT',
                'NIP_NAS',
                'STANDARD_NAME',
                'SOURCE_DATA',
                'WITEL_ID',
                'GROUP1',
                'GROUP2',
                'GROUP3',
                'GROUP4',
            ];
            
            // Add interleaved month and target columns
            for ($month = 1; $month <= 12; $month++) {
                $headers[] = (string)$month;
                $headers[] = 'TARGET_' . $month;
            }
            
            // Write headers
            $col = 1;
            foreach ($headers as $header) {
                $revenueSheet->setCellValueByColumnAndRow($col, 1, $header);
                $col++;
            }
            
            // Style header row
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
            
            $lastCol = count($headers);
            $revenueSheet->getStyle([1, 1, $lastCol, 1])->applyFromArray($headerStyle);
            
            // Set column widths
            $revenueSheet->getColumnDimension('A')->setWidth(15); // SUB_SEGMENT
            $revenueSheet->getColumnDimension('B')->setWidth(12); // NIP_NAS
            $revenueSheet->getColumnDimension('C')->setWidth(30); // STANDARD_NAME
            $revenueSheet->getColumnDimension('D')->setWidth(12); // SOURCE_DATA
            $revenueSheet->getColumnDimension('E')->setWidth(10); // WITEL_ID
            $revenueSheet->getColumnDimension('F')->setWidth(20); // GROUP1
            $revenueSheet->getColumnDimension('G')->setWidth(20); // GROUP2
            $revenueSheet->getColumnDimension('H')->setWidth(20); // GROUP3
            $revenueSheet->getColumnDimension('I')->setWidth(25); // GROUP4
            
            // Month and target columns
            for ($i = 0; $i < 12; $i++) {
                $colIndex = 10 + ($i * 2); // Start from column J (10)
                $revenueSheet->getColumnDimensionByColumn($colIndex)->setWidth(12); // Month
                $revenueSheet->getColumnDimensionByColumn($colIndex + 1)->setWidth(12); // Target
            }
            
            // Freeze header row
            $revenueSheet->freezePane('A2');
            
            // Set revenue sheet as active sheet (user will see this when opening file)
            $spreadsheet->setActiveSheetIndex(1);
            
            // Generate file
            $writer = new Xlsx($spreadsheet);
            $fileName = "Revenue_Import_Template_{$year}.xlsx";
            $tempFile = tempnam(sys_get_temp_dir(), 'template_');
            
            $writer->save($tempFile);
            
            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Template download error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Gagal men-download template: ' . $e->getMessage());
        }
    }

    /**
     * Delete all revenue data for a specific year
     */
    public function deleteYear(Request $request, int $year)
    {
        try {
            DB::beginTransaction();
            
            // Get unique months that have active (not deleted) uploads for this year
            $uploads = RevenueUpload::where('tahun', $year)
                ->select('bulan', DB::raw('MAX(created_at) as latest_created_at'))
                ->groupBy('bulan')
                ->get()
                ->map(function($item) use ($year) {
                    // Get the actual latest record for each month
                    return RevenueUpload::where('tahun', $year)
                        ->where('bulan', $item->bulan)
                        ->where('created_at', $item->latest_created_at)
                        ->first();
                })
                ->filter(function($upload) {
                    // Only include months where latest action is not delete
                    return $upload && $upload->action != 'delete';
                });
            
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
            
            // Create deletion log entry for each month - keep all logs for full audit trail
            foreach ($uploads as $upload) {
                RevenueUpload::create([
                    'tahun' => $year,
                    'bulan' => $upload->bulan,
                    'original_filename' => $upload->original_filename,
                    'stored_path' => null, // File already deleted
                    'uploaded_by' => auth()->id(),
                    'action' => 'delete',
                    'description' => "Deleted data for {$year}/{$upload->bulan}: {$deletedRevenues} revenues, {$deletedTargets} targets removed",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'row_count' => 0,
                    'file_size_kb' => 0,
                ]);
            }
            
            // DO NOT delete any records - keep all logs for audit trail
            // To check if month has data, query the LATEST record and check if action='delete'
            
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
