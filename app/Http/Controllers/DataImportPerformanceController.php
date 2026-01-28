<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PerformanceAMImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\LiniWaktu;
use App\Models\TargetAccountM;
use App\Models\AccountManager;
use App\Models\Region;
use App\Models\PerformanceUploadLog;

class DataImportPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        $currentYear = date('Y');

        // Define quarters
        $quarters = [];
        
        for ($q = 1; $q <= 4; $q++) {
            $quarterNames = [
                1 => 'Quarter 1 (Jan - Mar)',
                2 => 'Quarter 2 (Apr - Jun)',
                3 => 'Quarter 3 (Jul - Sep)',
                4 => 'Quarter 4 (Oct - Dec)',
            ];
            
            // Check if this quarter has data in database (quartal stored as Q1, Q2, Q3, Q4)
            $hasData = LiniWaktu::where('quartal', 'Q' . $q)
                ->where('tahun', $selectedYear)
                ->exists();
            
            $quarterData = [
                'quarter' => $q,
                'name' => $quarterNames[$q],
                'status' => $hasData ? 'uploaded' : 'pending',
            ];
            
            // If quarter has data, get details
            if ($hasData) {
                // Get counts and totals
                $amCount = LiniWaktu::where('quartal', 'Q' . $q)
                    ->where('tahun', $selectedYear)
                    ->distinct('nik_am')
                    ->count('nik_am');
                
                // Get region count through witel relationship
                $regionCount = DB::table('lini_waktu')
                    ->join('account_managers', 'lini_waktu.nik_am', '=', 'account_managers.nik')
                    ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
                    ->where('lini_waktu.quartal', 'Q' . $q)
                    ->where('lini_waktu.tahun', $selectedYear)
                    ->distinct('witels.region_id')
                    ->count('witels.region_id');
                
                $rowCount = LiniWaktu::where('quartal', 'Q' . $q)
                    ->where('tahun', $selectedYear)
                    ->count();
                
                // Get total target and realisasi from lini_waktu_target
                $liniWaktuIds = LiniWaktu::where('quartal', 'Q' . $q)
                    ->where('tahun', $selectedYear)
                    ->pluck('id');
                
                $totals = DB::table('lini_waktu_target')
                    ->whereIn('lini_waktu_id', $liniWaktuIds)
                    ->selectRaw('
                        SUM(r_revenue) as total_realisasi
                    ')
                    ->first();
                
                $targetTotals = DB::table('lini_waktu_target')
                    ->join('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
                    ->whereIn('lini_waktu_id', $liniWaktuIds)
                    ->selectRaw('
                        SUM(target_account_m.t_revenue) as total_target
                    ')
                    ->first();
                
                // Get most recent upload log for this quarter
                $uploadLog = PerformanceUploadLog::where('tahun', $selectedYear)
                    ->where('quartal', 'Q' . $q)
                    ->where('status', PerformanceUploadLog::STATUS_UPLOAD)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                $quarterData['uploadInfo'] = [
                    'fileName' => $uploadLog ? $uploadLog->file_name : 'performance_q' . $q . '_' . $selectedYear . '.xlsx',
                    'uploadDate' => $uploadLog ? $uploadLog->created_at->format('d M Y, H:i') : 'N/A',
                    'uploadedBy' => $uploadLog && $uploadLog->uploader ? $uploadLog->uploader->name : 'Admin',
                    'fileSize' => $uploadLog ? number_format($uploadLog->file_size, 2) . ' KB' : number_format($rowCount * 2.5, 2) . ' KB',
                    'rowCount' => $uploadLog ? $uploadLog->row_count : $rowCount,
                    'amCount' => $amCount,
                    'totalTarget' => 'Rp ' . number_format(($targetTotals->total_target ?? 0) / 1000000, 2) . 'M',
                    'totalRealisasi' => 'Rp ' . number_format(($totals->total_realisasi ?? 0) / 1000000, 2) . 'M',
                    'regionCount' => $regionCount,
                ];
                
                // Get activity logs for this quarter
                $logs = PerformanceUploadLog::where('tahun', $selectedYear)
                    ->where('quartal', 'Q' . $q)
                    ->with('uploader')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $quarterData['activityLogs'] = $logs->map(function ($log) {
                    return [
                        'action' => strtolower($log->status),
                        'fileName' => $log->file_name,
                        'user' => $log->uploader ? $log->uploader->name : 'Admin',
                        'timestamp' => $log->created_at->format('d M Y, H:i'),
                        'fileSize' => number_format($log->file_size, 2) . ' KB',
                        'rowCount' => $log->row_count,
                    ];
                })->toArray();
            }
            
            $quarters[] = $quarterData;
        }
        
        return Inertia::render('DataImportPerformance', [
            'initialQuartersData' => $quarters,
            'selectedYear' => (int)$selectedYear,
            'currentYear' => (int)$currentYear,
        ]);
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:10240',
                'quarter' => 'required|integer|between:1,4',
                'year' => 'required|integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'error_type' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $file = $request->file('file');
            $originalFileName = $file->getClientOriginalName();
            $fileSizeKB = $file->getSize() / 1024; // Convert bytes to KB
            
            $import = new PerformanceAMImport(
                $request->input('quarter'),
                $request->input('year')
            );
            
            Excel::import($import, $file);

            // Check for conflicts
            $conflicts = $import->getConflicts();

            if (!empty($conflicts)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data conflicts detected between sheets',
                    'error_type' => 'conflict_error',
                    'conflicts' => $conflicts,
                    'conflict_count' => count($conflicts),
                ], 422);
            }

            // Get authenticated user ID, fallback to admin (ID 2) if not found
            $userId = Auth::id();
            if (!$userId || !DB::table('users')->where('id', $userId)->exists()) {
                \Log::warning("Invalid user ID: {$userId}, using fallback admin ID 2");
                $userId = 2; // Fallback to admin
            }

            // Check if data already exists for this quarter (means it's an update/replace)
            $quartal = 'Q' . $request->input('quarter');
            $existingData = LiniWaktu::where('quartal', $quartal)
                ->where('tahun', $request->input('year'))
                ->exists();

            // Log the activity - use logUpdate if data existed, logUpload if new
            if ($existingData) {
                // This is a replace/update operation
                PerformanceUploadLog::logUpdate(
                    $request->input('year'),
                    $quartal,
                    $originalFileName,
                    $userId,
                    $import->getRowCount(),
                    $fileSizeKB
                );
            } else {
                // This is a new upload
                PerformanceUploadLog::logUpload(
                    $request->input('year'),
                    $quartal,
                    $originalFileName,
                    $userId,
                    $import->getRowCount(),
                    $fileSizeKB
                );
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data imported successfully',
                'summary' => [
                    'quarter' => $request->input('quarter'),
                    'year' => $request->input('year'),
                    'row_count' => $import->getRowCount(),
                ],
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            \Log::error('Performance AM Excel Validation Error', ['errors' => $errorMessages]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi Excel gagal',
                'error_type' => 'excel_validation_error',
                'details' => $errorMessages,
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Performance AM Import Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Check if error is about missing company (NIP NAS not found)
            if (strpos($e->getMessage(), 'Upload Data Revenue Dashboard First') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_type' => 'missing_revenue_data',
                ], 422);
            }
            
            // Generic error with more details
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage(),
                'error_type' => 'import_error',
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $templatePath = storage_path('app/templates/template_upload.xlsx');
        
        if (!file_exists($templatePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Template file not found',
            ], 404);
        }

        return response()->download(
            $templatePath,
            'performance_am_template.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    public function delete(Request $request, $year, $quarter = null)
    {
        try {
            DB::beginTransaction();
            
            if ($quarter) {
                // Delete specific quarter (convert to Q1, Q2, Q3, Q4 format)
                $quartal = 'Q' . $quarter;
                
                // Get all lini_waktu IDs for this quarter
                $liniWaktuIds = LiniWaktu::where('quartal', $quartal)
                    ->where('tahun', $year)
                    ->pluck('id');
                
                if ($liniWaktuIds->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => "No data found for Quarter $quarter $year",
                    ], 404);
                }
                
                // Get row count before deleting
                $rowCount = $liniWaktuIds->count();
                
                // Delete lini_waktu_target entries (contains realisasi data)
                DB::table('lini_waktu_target')
                    ->whereIn('lini_waktu_id', $liniWaktuIds)
                    ->delete();
                
                // Delete lini_waktu records
                LiniWaktu::whereIn('id', $liniWaktuIds)->delete();
                
                // Get authenticated user ID, fallback to admin (ID 2) if not found
                $userId = Auth::id();
                if (!$userId || !DB::table('users')->where('id', $userId)->exists()) {
                    \Log::warning("Invalid user ID: {$userId}, using fallback admin ID 2");
                    $userId = 2;
                }
                
                // Log the delete activity
                PerformanceUploadLog::logDelete(
                    $year,
                    $quartal,  // Already in Q1-Q4 format
                    $userId,
                    "Deleted Q{$quarter} {$year} data ({$rowCount} rows)"
                );
                
                // Note: We keep Target, AM, Company, and relationship data as they might be used by other quarters
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => "Quarter $quarter $year data deleted successfully",
                ]);
                
            } else {
                // Delete entire year
                
                // Get all lini_waktu IDs for this year
                $liniWaktuIds = LiniWaktu::where('tahun', $year)
                    ->pluck('id');
                
                if ($liniWaktuIds->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => "No data found for year $year",
                    ], 404);
                }
                
                // Get row count before deleting
                $rowCount = $liniWaktuIds->count();
                
                // Delete lini_waktu_target entries
                DB::table('lini_waktu_target')
                    ->whereIn('lini_waktu_id', $liniWaktuIds)
                    ->delete();
                
                // Delete all lini_waktu records for this year
                LiniWaktu::where('tahun', $year)->delete();
                
                // Get authenticated user ID, fallback to admin (ID 2) if not found
                $userId = Auth::id();
                if (!$userId || !DB::table('users')->where('id', $userId)->exists()) {
                    \Log::warning("Invalid user ID: {$userId}, using fallback admin ID 2");
                    $userId = 2;
                }
                
                // Log delete for entire year
                PerformanceUploadLog::logDelete(
                    $year,
                    'Q1',  // Use Q1 as representative quarter for year deletion
                    $userId,
                    "Deleted entire year {$year} data ({$rowCount} rows)"
                );
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => "Year $year data deleted successfully",
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Performance AM Delete Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
