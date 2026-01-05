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
     * Download template Excel file for revenue import
     */
    public function downloadTemplate(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        try {
            $spreadsheet = new Spreadsheet();
            
            // Remove default sheet
            $spreadsheet->removeSheetByIndex(0);
            
            // Create sheet for the specified year
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle("Rev {$year}");
            
            // Set up headers
            $headers = [
                'A1' => 'SUB_SEGMENT',
                'B1' => 'NIP_NAS',
                'C1' => 'STANDARD_NAME',
                'D1' => 'SOURCE_DATA',
                'E1' => 'GROUP1',
                'F1' => 'GROUP2',
                'G1' => 'GROUP3',
                'H1' => 'GROUP4',
            ];
            
            // Add month columns (1-12)
            $monthColumns = range('I', 'T'); // I=1, J=2, ..., T=12
            for ($i = 0; $i < 12; $i++) {
                $headers[$monthColumns[$i] . '1'] = (string)($i + 1);
            }
            
            // Apply headers
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
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
            
            $sheet->getStyle('A1:T1')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(20); // SUB_SEGMENT
            $sheet->getColumnDimension('B')->setWidth(15); // NIP_NAS
            $sheet->getColumnDimension('C')->setWidth(35); // STANDARD_NAME
            $sheet->getColumnDimension('D')->setWidth(15); // SOURCE_DATA
            $sheet->getColumnDimension('E')->setWidth(25); // GROUP1
            $sheet->getColumnDimension('F')->setWidth(25); // GROUP2
            $sheet->getColumnDimension('G')->setWidth(25); // GROUP3
            $sheet->getColumnDimension('H')->setWidth(25); // GROUP4
            
            // Month columns width
            foreach ($monthColumns as $col) {
                $sheet->getColumnDimension($col)->setWidth(12);
            }
            
            // Add example row with instructions
            $exampleRow = 2;
            $sheet->setCellValue("A{$exampleRow}", "AIRLINES");
            $sheet->setCellValue("B{$exampleRow}", "760618");
            $sheet->setCellValue("C{$exampleRow}", "PELITA AIR SERVICE PT");
            $sheet->setCellValue("D{$exampleRow}", "TIBS-NP");
            $sheet->setCellValue("E{$exampleRow}", "CONNECTIVITY");
            $sheet->setCellValue("F{$exampleRow}", "Fixed Broadband");
            $sheet->setCellValue("G{$exampleRow}", "High Speed Internet");
            $sheet->setCellValue("H{$exampleRow}", "Abo HSI");
            
            // Add example revenue values for months
            for ($i = 0; $i < 12; $i++) {
                $sheet->setCellValue($monthColumns[$i] . $exampleRow, rand(1000000, 10000000));
            }
            
            // Style example row
            $sheet->getStyle("A{$exampleRow}:T{$exampleRow}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7E6E6']
                ],
                'font' => [
                    'italic' => true,
                    'color' => ['rgb' => '666666']
                ]
            ]);
            
            // Add notes
            $notesRow = 4;
            $sheet->setCellValue("A{$notesRow}", "NOTES:");
            $sheet->getStyle("A{$notesRow}")->getFont()->setBold(true);
            
            $sheet->setCellValue("A" . ($notesRow + 1), "1. NIP_NAS is required and must be unique (max 25 characters)");
            $sheet->setCellValue("A" . ($notesRow + 2), "2. STANDARD_NAME (company name) is required (max 55 characters)");
            $sheet->setCellValue("A" . ($notesRow + 3), "3. SOURCE_DATA must be: TIBS-NP, SISKA, or NGTMA");
            $sheet->setCellValue("A" . ($notesRow + 4), "4. All GROUP fields (1-4) are required (max 45 characters each)");
            $sheet->setCellValue("A" . ($notesRow + 5), "5. Month columns (1-12) should contain revenue values (numbers only)");
            $sheet->setCellValue("A" . ($notesRow + 6), "6. Empty or zero revenue values will be skipped");
            $sheet->setCellValue("A" . ($notesRow + 7), "7. For Quick Upload: Create sheets named 'Rev 2024', 'Rev 2025', etc.");
            
            $sheet->getStyle("A{$notesRow}:A" . ($notesRow + 7))->getFont()->setSize(9);
            
            // Freeze header row
            $sheet->freezePane('A2');
            
            // Create writer and download
            $writer = new Xlsx($spreadsheet);
            $filename = "revenue_import_template_{$year}.xlsx";
            $tempFile = tempnam(sys_get_temp_dir(), 'revenue_template');
            
            $writer->save($tempFile);
            
            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Template generation failed', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to generate template: ' . $e->getMessage());
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
}
