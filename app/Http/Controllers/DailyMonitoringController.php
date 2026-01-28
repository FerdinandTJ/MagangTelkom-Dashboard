<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Bulan;
use App\Models\Hari;
use App\Models\LopBulan;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BulananImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyMonitoringController extends Controller
{
    /**
     * Display the daily monitoring page
     */
    public function index(Request $request): Response
    {
        // Get filter values from request or use current date
        $currentMonth = $request->input('month', now()->month);
        $currentYear = $request->input('year', now()->year);
        $currentDate = $request->input('date', now()->day);

        // Validate inputs
        $currentMonth = max(1, min(12, (int) $currentMonth));
        $currentYear = max(2020, min(2050, (int) $currentYear));
        $currentDate = max(1, min(31, (int) $currentDate));

        // Get bulan data for selected month/year
        $bulan = Bulan::where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->first();

        // Auto-generate record hari jika belum ada
        $hari = null;
        if ($bulan) {
            $hari = Hari::firstOrCreate(
                [
                    'bulan_id' => $bulan->id,
                    'tanggal' => $currentDate,
                    'tahun' => $currentYear,
                ],
                [
                    'progress_scaling' => 0,
                    'sodomoro' => 0,
                    'adjustment' => 0,
                ]
            );
        }

        // Calculate CM (Current Month) metrics - Daily
        $metricsCM = $this->calculateMetrics($bulan, $hari, 'CM');
        $achievementCM = $metricsCM['achievement'];
        unset($metricsCM['achievement']);

        // Calculate CM (Current Month) metrics - Monthly (accumulated to last date)
        $metricsMonthly = $this->calculateMonthlyMetrics($bulan, $currentMonth, $currentYear);
        $achievementMonthly = $metricsMonthly['achievement'];
        unset($metricsMonthly['achievement']);
        
        // Debug: Log nilai yang akan dikirim ke frontend
        Log::info('Data sent to frontend', [
            'daily_progress_scaling' => $metricsCM[3]['value'] ?? 'N/A',
            'monthly_progress_scaling' => $metricsMonthly[3]['value'] ?? 'N/A',
            'daily_sodomoro' => $metricsCM[4]['value'] ?? 'N/A',
            'monthly_sodomoro' => $metricsMonthly[4]['value'] ?? 'N/A',
        ]);

        // Calculate YTD metrics
        $metricsYTD = $this->calculateYTDMetrics($currentMonth, $currentYear, $currentDate);
        $achievementYTD = $metricsYTD['achievement'];
        unset($metricsYTD['achievement']);

        // Get table data (LOP-Bulan pivot data)
        $tableData = [];
        if ($bulan) {
            $tableData = LopBulan::where('bulan_id', $bulan->id)
                ->with(['lop', 'region'])
                ->orderBy('ID_LOP', 'asc')  // Urutkan berdasarkan ID_LOP ascending
                ->get()
                ->map(function ($item) {
                    return [
                        'idlop' => $item->ID_LOP ?? '',
                        'am' => $item->AM ?? '',
                        'treg' => $item->region ? $item->region->code : '',
                        'namaCC' => $item->Nama_CC ?? '',
                        'project' => $item->Project ?? '',
                        'scaling' => number_format($item->Scaling ?? 0, 0, ',', '.'),
                        'progress' => $item->Progress ?? '',
                    ];
                })
                ->toArray();
        }

        // Get filter data from database
        $availableYears = Bulan::distinct()
            ->pluck('tahun')
            ->sort()
            ->values()
            ->toArray();
        
        $availableMonths = Bulan::where('tahun', $currentYear)
            ->distinct()
            ->pluck('bulan')
            ->sort()
            ->values()
            ->toArray();
        
        $availableDates = [];
        if ($bulan) {
            $availableDates = Hari::where('bulan_id', $bulan->id)
                ->where('tahun', $currentYear)
                ->distinct()
                ->pluck('tanggal')
                ->sort()
                ->values()
                ->toArray();
        }

        return Inertia::render('DailyMonitoring', [
            'metricsCM' => $metricsCM,
            'metricsMonthly' => $metricsMonthly,
            'metricsYTD' => $metricsYTD,
            'achievementCM' => $achievementCM,
            'achievementMonthly' => $achievementMonthly,
            'achievementYTD' => $achievementYTD,
            'tableData' => $tableData,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'currentDate' => $currentDate,
            'availableYears' => $availableYears,
            'availableMonths' => $availableMonths,
            'availableDates' => $availableDates,
        ]);
    }

    /**
     * Calculate metrics from bulan and hari data
     */
    private function calculateMetrics(?Bulan $bulan, ?Hari $hari, string $type = 'CM'): array
    {
        // Default values
        $targetRevenue = 0;
        $sustain = 0;
        $kebutuhanScaling = 0;
        $progressScaling = 0;
        $sodomoro = 0;
        $adjustment = 0;
        $progressScalingAkumulatif = 0;
        $sodomoroAkumulatif = 0;
        $adjustmentAkumulatif = 0;

        // Get values from bulan
        if ($bulan) {
            $targetRevenue = (float) ($bulan->target_cm ?? 0);
            $sustain = (float) ($bulan->t_sustain ?? 0);
        }

        // Get values from hari
        if ($hari) {
            // Nilai dari tanggal yang dipilih saja (TIDAK akumulasi untuk Daily)
            $progressScaling = (float) ($hari->progress_scaling ?? 0);
            $sodomoro = (float) ($hari->sodomoro ?? 0);
            $adjustment = (float) ($hari->adjustment ?? 0);
            
            // Untuk Daily, tidak perlu akumulasi
            $progressScalingAkumulatif = $progressScaling;
            $sodomoroAkumulatif = $sodomoro;
            $adjustmentAkumulatif = $adjustment;
        }

        // Calculate Kebutuhan Scaling
        // Formula: Target Revenue - Sustain
        // If negative, multiply by -1 (no negative values)
        $kebutuhanScaling = abs($targetRevenue - $sustain);

        // Calculate progress revenue CM
        // Formula untuk Daily: Sustain + Progress Scaling + Sodomoro + Adjustment (dari tanggal tersebut saja)
        $progressRevenueCm = $sustain + $progressScalingAkumulatif + $sodomoroAkumulatif + $adjustmentAkumulatif;

        // Calculate achievement revenue CM
        // Formula: (Progress Revenue / Target Revenue) * 100%
        $achRevenueCm = $targetRevenue > 0 
            ? ($progressRevenueCm / $targetRevenue) * 100 
            : 0;

        return [
            [
                'label' => 'TARGET REVENUE',
                'value' => number_format($targetRevenue, 2, ',', '.'),
                'type' => $type,
            ],
            [
                'label' => 'SUSTAIN',
                'value' => number_format($sustain, 2, ',', '.'),
                'type' => $type,
            ],
            [
                'label' => 'KEBUTUHAN SCALING',
                'value' => number_format($kebutuhanScaling, 2, ',', '.'),
                'type' => $type,
            ],
            [
                'label' => 'PROGRESS SCALING',
                'value' => number_format($progressScaling, 2, ',', '.'),
                'type' => $type,
            ],
            [
                'label' => 'SODOMORO',
                'value' => number_format($sodomoro, 2, ',', '.'),
                'type' => $type,
            ],
            [
                'label' => 'ADJUSTMENT',
                'value' => number_format($adjustment, 2, ',', '.'),
                'type' => $type,
            ],
            [
                'label' => 'PROGRESS REVENUE ' . $type,
                'value' => number_format($progressRevenueCm, 2, ',', '.'),
                'type' => $type,
            ],
            'achievement' => [
                'percentage' => round($achRevenueCm, 2),
                'label' => 'ACH REVENUE ' . $type,
                'formattedValue' => number_format($achRevenueCm, 0, ',', '.') . '%',
                'type' => $type,
            ],
        ];
    }

    /**
     * Calculate YTD (Year-to-Date) metrics
     * Sum dari bulan Januari sampai bulan & tanggal yang dipilih
     */
    private function calculateYTDMetrics(int $currentMonth, int $currentYear, int $currentDate): array
    {
        // Get all bulan data from January to current month
        $bulans = Bulan::where('tahun', $currentYear)
            ->where('bulan', '>=', 1)
            ->where('bulan', '<=', $currentMonth)
            ->get();

        // Sum values from bulan table
        $targetRevenueYTD = $bulans->sum('target_cm');
        $sustainYTD = $bulans->sum('t_sustain');
        
        // Calculate Kebutuhan Scaling YTD
        // Formula: Akumulasi (Target Revenue - Sustain) dari setiap bulan
        // If negative, multiply by -1 (no negative values)
        $kebutuhanScalingYTD = abs($targetRevenueYTD - $sustainYTD);

        // Get all hari data from Jan 1 to current date (filter berdasarkan tanggal)
        $bulanIds = $bulans->pluck('id')->toArray();
        
        // Query dengan kondisi:
        // - Bulan sebelum bulan sekarang: ambil semua tanggal
        // - Bulan sekarang: hanya sampai tanggal yang dipilih
        $haris = Hari::whereIn('bulan_id', $bulanIds)
            ->where('tahun', $currentYear)
            ->where(function($query) use ($currentMonth, $currentDate, $bulans) {
                // Untuk bulan-bulan sebelum bulan sekarang, ambil semua
                $previousMonthIds = $bulans->where('bulan', '<', $currentMonth)->pluck('id')->toArray();
                if (!empty($previousMonthIds)) {
                    $query->orWhereIn('bulan_id', $previousMonthIds);
                }
                
                // Untuk bulan sekarang, hanya sampai tanggal yang dipilih
                $currentMonthRecord = $bulans->where('bulan', $currentMonth)->first();
                if ($currentMonthRecord) {
                    $query->orWhere(function($q) use ($currentMonthRecord, $currentDate) {
                        $q->where('bulan_id', $currentMonthRecord->id)
                          ->where('tanggal', '<=', $currentDate);
                    });
                }
            })
            ->get();

        // Sum values from hari table
        $progressScalingYTD = $haris->sum('progress_scaling');
        $sodomoroYTD = $haris->sum('sodomoro');
        $adjustmentYTD = $haris->sum('adjustment');

        // Calculate progress revenue YTD
        // Formula: Sustain + Progress Scaling + Sodomoro + Adjustment
        $progressRevenueYTD = $sustainYTD + $progressScalingYTD + $sodomoroYTD + $adjustmentYTD;

        // Calculate achievement revenue YTD
        // Formula: (Progress Revenue / Target Revenue) * 100%
        $achRevenueYTD = $targetRevenueYTD > 0 
            ? ($progressRevenueYTD / $targetRevenueYTD) * 100 
            : 0;

        return [
            [
                'label' => 'TARGET REVENUE',
                'value' => number_format($targetRevenueYTD, 2, ',', '.'),
                'type' => 'YTD',
            ],
            [
                'label' => 'SUSTAIN',
                'value' => number_format($sustainYTD, 2, ',', '.'),
                'type' => 'YTD',
            ],
            [
                'label' => 'KEBUTUHAN SCALING',
                'value' => number_format($kebutuhanScalingYTD, 2, ',', '.'),
                'type' => 'YTD',
            ],
            [
                'label' => 'PROGRESS SCALING',
                'value' => number_format($progressScalingYTD, 2, ',', '.'),
                'type' => 'YTD',
            ],
            [
                'label' => 'SODOMORO',
                'value' => number_format($sodomoroYTD, 2, ',', '.'),
                'type' => 'YTD',
            ],
            [
                'label' => 'ADJUSTMENT',
                'value' => number_format($adjustmentYTD, 2, ',', '.'),
                'type' => 'YTD',
            ],
            [
                'label' => 'PROGRESS REVENUE YTD',
                'value' => number_format($progressRevenueYTD, 2, ',', '.'),
                'type' => 'YTD',
            ],
            'achievement' => [
                'percentage' => round($achRevenueYTD, 2),
                'label' => 'ACH REVENUE YTD',
                'formattedValue' => number_format($achRevenueYTD, 0, ',', '.') . '%',
                'type' => 'YTD',
            ],
        ];
    }

    /**
     * Calculate monthly metrics (accumulated to last available date in the month)
     */
    private function calculateMonthlyMetrics(?Bulan $bulan, int $currentMonth, int $currentYear): array
    {
        // Default values
        $targetRevenue = 0;
        $sustain = 0;
        $kebutuhanScaling = 0;
        $progressScaling = 0;
        $sodomoroTotal = 0;
        $adjustmentTotal = 0;

        if (!$bulan) {
            return [
                [
                    'label' => 'TARGET REVENUE',
                    'value' => number_format(0, 2, ',', '.'),
                    'type' => 'MONTHLY',
                ],
                [
                    'label' => 'SUSTAIN',
                    'value' => number_format(0, 2, ',', '.'),
                    'type' => 'MONTHLY',
                ],
                [
                    'label' => 'KEBUTUHAN SCALING',
                    'value' => number_format(0, 2, ',', '.'),
                    'type' => 'MONTHLY',
                ],
                [
                    'label' => 'PROGRESS SCALING',
                    'value' => number_format(0, 2, ',', '.'),
                    'type' => 'MONTHLY',
                ],
                [
                    'label' => 'SODOMORO',
                    'value' => number_format(0, 2, ',', '.'),
                    'type' => 'MONTHLY',
                ],
                [
                    'label' => 'ADJUSTMENT',
                    'value' => number_format(0, 2, ',', '.'),
                    'type' => 'MONTHLY',
                ],
                [
                    'label' => 'PROGRESS REVENUE MONTHLY',
                    'value' => number_format(0, 2, ',', '.'),
                    'type' => 'MONTHLY',
                ],
                'achievement' => [
                    'percentage' => 0,
                    'label' => 'ACH REVENUE MONTHLY',
                    'formattedValue' => '0%',
                    'type' => 'MONTHLY',
                ],
            ];
        }

        // Get target and sustain from bulan
        $targetRevenue = (float) ($bulan->target_cm ?? 0);
        $sustain = (float) ($bulan->t_sustain ?? 0);

        // Debug: Log query parameters
        Log::info('Monthly Metrics - Query Parameters', [
            'bulan_id' => $bulan->id,
            'tahun' => $currentYear,
            'bulan' => $currentMonth,
        ]);

        // Monthly = SUM SEMUA hari yang ada di bulan tersebut (berdasarkan bulan_id dari filter)
        // Progress Scaling: SUM dari semua hari dengan bulan_id yang sesuai
        $progressScaling = Hari::where('bulan_id', $bulan->id)
            ->where('tahun', $currentYear)
            ->sum('progress_scaling');
        
        // Sodomoro: SUM dari semua hari dengan bulan_id yang sesuai
        $sodomoroTotal = Hari::where('bulan_id', $bulan->id)
            ->where('tahun', $currentYear)
            ->sum('sodomoro');
            
        // Adjustment: SUM dari semua hari dengan bulan_id yang sesuai
        $adjustmentTotal = Hari::where('bulan_id', $bulan->id)
            ->where('tahun', $currentYear)
            ->sum('adjustment');
        
        // Debug: Log hasil query dan data hari yang ditemukan
        $hariRecords = Hari::where('bulan_id', $bulan->id)
            ->where('tahun', $currentYear)
            ->get(['tanggal', 'progress_scaling', 'sodomoro', 'adjustment'])
            ->toArray();
            
        Log::info('Monthly Metrics - Calculation Results', [
            'hari_count' => count($hariRecords),
            'hari_data' => $hariRecords,
            'sum_progress_scaling' => $progressScaling,
            'sum_sodomoro' => $sodomoroTotal,
            'sum_adjustment' => $adjustmentTotal,
        ]);

        // Calculate Kebutuhan Scaling
        $kebutuhanScaling = abs($targetRevenue - $sustain);

        // Calculate progress revenue
        $progressRevenue = $sustain + $progressScaling + $sodomoroTotal + $adjustmentTotal;

        // Calculate achievement
        $achRevenue = $targetRevenue > 0 
            ? ($progressRevenue / $targetRevenue) * 100 
            : 0;

        return [
            [
                'label' => 'TARGET REVENUE',
                'value' => number_format($targetRevenue, 2, ',', '.'),
                'type' => 'MONTHLY',
            ],
            [
                'label' => 'SUSTAIN',
                'value' => number_format($sustain, 2, ',', '.'),
                'type' => 'MONTHLY',
            ],
            [
                'label' => 'KEBUTUHAN SCALING',
                'value' => number_format($kebutuhanScaling, 2, ',', '.'),
                'type' => 'MONTHLY',
            ],
            [
                'label' => 'PROGRESS SCALING',
                'value' => number_format($progressScaling, 2, ',', '.'),
                'type' => 'MONTHLY',
            ],
            [
                'label' => 'SODOMORO',
                'value' => number_format($sodomoroTotal, 2, ',', '.'),
                'type' => 'MONTHLY',
            ],
            [
                'label' => 'ADJUSTMENT',
                'value' => number_format($adjustmentTotal, 2, ',', '.'),
                'type' => 'MONTHLY',
            ],
            [
                'label' => 'PROGRESS REVENUE MONTHLY',
                'value' => number_format($progressRevenue, 2, ',', '.'),
                'type' => 'MONTHLY',
            ],
            'achievement' => [
                'percentage' => round($achRevenue, 2),
                'label' => 'ACH REVENUE MONTHLY',
                'formattedValue' => number_format($achRevenue, 0, ',', '.') . '%',
                'type' => 'MONTHLY',
            ],
        ];
    }

    /**
     * Upload data bulanan dari Excel
     */
    public function uploadBulanan(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors([
                'file' => 'File tidak valid. Pastikan file berformat Excel (.xlsx atau .xls) dan maksimal 10MB.'
            ]);
        }

        try {
            $file = $request->file('file');
            Log::info('Starting Excel import for daily monitoring', ['filename' => $file->getClientOriginalName()]);
            
            // Validate that file has required sheets
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file->getRealPath());
                $sheetNames = $spreadsheet->getSheetNames();
                
                // Check for sheets with pattern "Target YYYY" and "List LOP YYYY"
                $hasTargetSheet = false;
                $hasLopSheet = false;
                
                foreach ($sheetNames as $sheetName) {
                    if (preg_match('/^Target\s+\d{4}$/i', $sheetName)) {
                        $hasTargetSheet = true;
                    }
                    if (preg_match('/^List\s+LOP\s+\d{4}$/i', $sheetName)) {
                        $hasLopSheet = true;
                    }
                }
                
                $missingSheets = [];
                if (!$hasTargetSheet) {
                    $missingSheets[] = 'Target YYYY (contoh: Target 2026)';
                }
                if (!$hasLopSheet) {
                    $missingSheets[] = 'List LOP YYYY (contoh: List LOP 2026)';
                }
                
                if (!empty($missingSheets)) {
                    Log::error('Missing required sheets', [
                        'found' => $sheetNames,
                        'missing' => $missingSheets
                    ]);
                    return redirect()->back()->withErrors([
                        'file' => 'File tidak valid: Sheet yang diperlukan tidak ditemukan (' . implode(', ', $missingSheets) . ')'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error reading Excel file structure: ' . $e->getMessage());
                return redirect()->back()->withErrors([
                    'file' => 'File tidak dapat dibaca. Pastikan file adalah Excel yang valid (.xlsx atau .xls)'
                ]);
            }
            
            DB::beginTransaction();
            
            // Import menggunakan BulananImport yang handle multiple sheets
            // Pass the actual sheet names from the file
            Excel::import(new BulananImport($sheetNames), $file);

            DB::commit();
            
            Log::info('Excel import completed successfully');
            return redirect()->back()->with('success', 'Data bulanan berhasil diupload!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            Log::error('Excel validation failed', ['errors' => $errorMessages]);
            return redirect()->back()->withErrors([
                'file' => 'Validasi Excel gagal: ' . implode(' | ', array_slice($errorMessages, 0, 3))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Excel import failed: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName() ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->withErrors([
                'file' => 'Gagal upload data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update data harian (sodomoro dan adjustment)
     */
    public function updateHarian(Request $request)
    {
        $request->validate([
            'date' => 'required|integer|min:1|max:31',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2050',
            'sodomoro' => 'required|numeric',
            'adjustment' => 'required|numeric',
        ]);

        try {
            $currentMonth = $request->input('month');
            $currentYear = $request->input('year');
            $currentDate = $request->input('date');

            // Get bulan record
            $bulan = Bulan::where('bulan', $currentMonth)
                ->where('tahun', $currentYear)
                ->first();

            if (!$bulan) {
                return redirect()->back()->with('error', 'Data bulan tidak ditemukan. Silakan upload data bulanan terlebih dahulu.');
            }

            // Update or create hari record
            $hari = Hari::updateOrCreate(
                [
                    'bulan_id' => $bulan->id,
                    'tanggal' => $currentDate,
                    'tahun' => $currentYear,
                ],
                [
                    'sodomoro' => $request->input('sodomoro'),
                    'adjustment' => $request->input('adjustment'),
                ]
            );

            Log::info('Data harian updated successfully', [
                'bulan_id' => $bulan->id,
                'tanggal' => $currentDate,
                'tahun' => $currentYear,
                'sodomoro' => $request->input('sodomoro'),
                'adjustment' => $request->input('adjustment'),
            ]);

            return redirect()->back()->with('success', 'Data harian berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error('Update harian failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal update data: ' . $e->getMessage());
        }
    }
}
