<?php

namespace App\Http\Controllers;

use App\Services\RevenueAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $analyticsService;

    public function __construct(RevenueAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display main dashboard page
     */
    public function index(Request $request)
    {
        $currentYear = $request->input('year', date('Y'));
        
        return Inertia::render('Dashboard', [
            'dashboardSummary' => $this->analyticsService->getDashboardSummary($currentYear),
            'yearlyRevenue' => $this->analyticsService->getYearlyRevenue(),
            'monthlyRevenue' => $this->analyticsService->getMonthlyRevenue($currentYear),
            'ytdComparison' => $this->analyticsService->getYtdComparison($currentYear),
            'subsegmentRevenue' => $this->analyticsService->getSubsegmentRevenue($currentYear),
            'subsegmentRegionalData' => $this->analyticsService->getSubsegmentWithRegionalBreakdown($currentYear),
            'topCompanies' => $this->analyticsService->getTopCompanies($currentYear, 5),
            'currentYear' => (int)$currentYear,
        ]);
    }

    /**
     * Display Performance AM page
     * Fungsi ini untuk menampilkan halaman Performance Account Manager dengan data dari database
     */
    public function performanceAM(Request $request)
    {
        // Get current year or from request
        $currentYear = $request->input('year', date('Y'));
        
        // Get current quarter or from request
        $currentQuartal = $request->input('quartal', $this->getCurrentQuartal());
        
        // Get available years and quartals from lini_waktu
        $availableYears = $this->getAvailableYears();
        $availableQuartals = $this->getAvailableQuartals($currentYear);
        
        // Get current period details (bulan_awal, bulan_akhir)
        $periodDetails = $this->getPeriodDetails($currentYear, $currentQuartal);
        
        $revenueTarget = $this->getTotalRevenueTarget($currentYear, $currentQuartal);
        
        return Inertia::render('PerformanceAm', [
            // Metrics untuk nav cards
            'amMetrics' => [
                // Fungsi ini untuk mendapatkan total Account Manager yang terdaftar
                'total_am' => $this->getTotalAM(),
                
                // Fungsi ini untuk mendapatkan total revenue target dari semua AM
                'revenue_target' => $revenueTarget,
                'formatted_revenue_target' => $this->formatCurrency($revenueTarget, 2),
                
                // Year info dengan bulan range
                'year' => $currentYear,
                'month_start' => $periodDetails['bulan_awal'] ?? null,
                'month_end' => $periodDetails['bulan_akhir'] ?? null,
                
                // Quartal info
                'quartal' => $currentQuartal,
            ],
            
            // Dropdown options
            'availableYears' => $availableYears,
            'availableQuartals' => $availableQuartals,
            
            // Chart data - Target Revenue AM Ranking
            'amRevenueRanking' => $this->getAMRevenueRanking($currentYear, $currentQuartal),
            
            // Chart data - Region Distribution
            'regionDistribution' => $this->getRegionDistribution(),
            
            // Table data - List Account Manager
            'accountManagerList' => $this->getAccountManagerList(),
            
            'currentYear' => $currentYear,
            'currentQuartal' => $currentQuartal,
        ]);
    }

    /**
     * Fungsi ini untuk mendapatkan total Account Manager yang terdaftar di database
     */
    private function getTotalAM(): int
    {
        return \DB::table('account_managers')->count();
    }

    /**
     * Fungsi ini untuk mendapatkan total revenue target dari semua AM berdasarkan tahun dan quartal
     */
    private function getTotalRevenueTarget(int $year, string $quartal): float
    {
        // Get all lini_waktu_ids for the selected year and quartal
        $liniWaktuIds = \DB::table('lini_waktu')
            ->where('tahun', $year)
            ->where('quartal', $quartal)
            ->pluck('id');
        
        if ($liniWaktuIds->isEmpty()) {
            return 0;
        }
        
        // Get sum of t_revenue from target_account_m through lini_waktu_target
        $totalRevenue = \DB::table('lini_waktu_target')
            ->join('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
            ->whereIn('lini_waktu_target.lini_waktu_id', $liniWaktuIds)
            ->sum('target_account_m.t_revenue');
        
        return (float) $totalRevenue;
    }

    /**
     * Fungsi ini untuk mendapatkan quartal saat ini berdasarkan bulan sekarang
     */
    private function getCurrentQuartal(): string
    {
        $currentMonth = (int) date('n');
        
        if ($currentMonth >= 1 && $currentMonth <= 3) {
            return 'Q1';
        } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
            return 'Q2';
        } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
            return 'Q3';
        } else {
            return 'Q4';
        }
    }

    /**
     * Fungsi ini untuk mendapatkan list tahun yang tersedia di database
     */
    private function getAvailableYears(): array
    {
        return \DB::table('lini_waktu')
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
    }

    /**
     * Fungsi ini untuk mendapatkan list quartal yang tersedia untuk tahun tertentu
     */
    private function getAvailableQuartals(int $year): array
    {
        return \DB::table('lini_waktu')
            ->select('quartal')
            ->where('tahun', $year)
            ->distinct()
            ->orderBy('quartal', 'asc')
            ->pluck('quartal')
            ->toArray();
    }

    /**
     * Fungsi ini untuk mendapatkan detail periode (bulan_awal dan bulan_akhir) berdasarkan tahun dan quartal
     */
    private function getPeriodDetails(int $year, string $quartal): array
    {
        $period = \DB::table('lini_waktu')
            ->where('tahun', $year)
            ->where('quartal', $quartal)
            ->first(['bulan_awal', 'bulan_akhir']);
        
        if (!$period) {
            return [
                'bulan_awal' => null,
                'bulan_akhir' => null
            ];
        }
        
        return [
            'bulan_awal' => $period->bulan_awal,
            'bulan_akhir' => $period->bulan_akhir
        ];
    }

    /**
     * Fungsi ini untuk mendapatkan data ranking revenue target per Account Manager
     * Target revenue diambil berdasarkan filter tahun dan quartal yang dipilih
     * Flow: account_managers -> lini_waktu (filter tahun & quartal) -> lini_waktu_target -> target_account_m (t_revenue)
     * Data diurutkan berdasarkan t_revenue tertinggi
     */
    private function getAMRevenueRanking(int $year, string $quartal): array
    {
        // Get all Account Managers with their target revenue filtered by year and quartal
        // Include region_code untuk filter dropdown
        $rankingData = \DB::table('account_managers')
            ->select(
                'account_managers.nik',
                'account_managers.nama as am_name',
                'regions.code as region_code',
                \DB::raw('COALESCE(SUM(target_account_m.t_revenue), 0) as t_revenue')
            )
            ->leftJoin('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->leftJoin('regions', 'witels.region_id', '=', 'regions.id')
            ->leftJoin('lini_waktu', function($join) use ($year, $quartal) {
                $join->on('account_managers.nik', '=', 'lini_waktu.nik_am')
                     ->where('lini_waktu.tahun', '=', $year)
                     ->where('lini_waktu.quartal', '=', $quartal);
            })
            ->leftJoin('lini_waktu_target', 'lini_waktu.id', '=', 'lini_waktu_target.lini_waktu_id')
            ->leftJoin('target_account_m', 'lini_waktu_target.target_id', '=', 'target_account_m.id')
            ->groupBy('account_managers.nik', 'account_managers.nama', 'regions.code')
            ->orderBy('t_revenue', 'desc')
            ->get();
        
        return $rankingData->map(function($item) {
            return [
                'am_name' => $item->am_name,
                'region_code' => $item->region_code,
                't_revenue' => (float) $item->t_revenue,
                'formatted_revenue' => $this->formatCurrency($item->t_revenue, 2)
            ];
        })->toArray();
    }

    /**
     * Fungsi ini untuk mendapatkan distribusi Account Manager per Region
     * Data diambil dari table regions, dengan perhitungan:
     * - idwitels di account_managers -> region_id di witels -> code di regions
     * - Persentase: (jumlah AM di region / total AM) * 100
     */
    private function getRegionDistribution(): array
    {
        // Get total AM
        $totalAM = \DB::table('account_managers')->count();
        
        if ($totalAM === 0) {
            return [];
        }
        
        // Get AM count per region menggunakan kolom 'code' dari table regions
        $distribution = \DB::table('account_managers')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->join('regions', 'witels.region_id', '=', 'regions.id')
            ->select(
                'regions.id as region_id',
                'regions.code as region_code',
                'regions.code as region_name',
                \DB::raw('COUNT(account_managers.nik) as am_count')
            )
            ->groupBy('regions.id', 'regions.code')
            ->orderBy('am_count', 'desc')
            ->get();
        
        return $distribution->map(function($item) use ($totalAM) {
            $percentage = ($item->am_count / $totalAM) * 100;
            
            return [
                'region_id' => $item->region_id,
                'region_code' => $item->region_code,
                'region_name' => $item->region_name,
                'am_count' => $item->am_count,
                'percentage' => round($percentage, 2)
            ];
        })->toArray();
    }

    /**
     * Fungsi ini untuk mendapatkan list semua Account Manager dengan detail lengkap
     * Termasuk: nama, NIK, posisi, no_gsm, dan lokasi (nama witel)
     */
    private function getAccountManagerList(): array
    {
        $amList = \DB::table('account_managers')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->select(
                'account_managers.nik',
                'account_managers.nama',
                'account_managers.posisi',
                'account_managers.no_gsm',
                'witels.nama_witels as lokasi_am'
            )
            ->orderBy('account_managers.nama', 'asc')
            ->get();
        
        return $amList->map(function($am, $index) {
            return [
                'no' => $index + 1,
                'nik' => $am->nik,
                'nama' => $am->nama,
                'posisi' => $am->posisi,
                'no_gsm' => $am->no_gsm,
                'lokasi_am' => $am->lokasi_am
            ];
        })->toArray();
    }

    /**
     * Get monthly revenue data for specific year
     */
    public function getMonthlyData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        
        return response()->json([
            'success' => true,
            'data' => [
                'monthly_revenue' => $this->analyticsService->getMonthlyRevenue($year),
                'ytd_comparison' => $this->analyticsService->getYtdComparison($year),
                'year' => $year
            ]
        ]);
    }

    /**
     * Get subsegment details for specific month
     */
    public function getMonthDetails(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12'
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'subsegments' => $this->analyticsService->getSubsegmentRevenue($year, $month),
                'month_name' => $monthNames[$month],
                'month' => $month,
                'year' => $year
            ]
        ]);
    }

    /**
     * Get company details for specific subsegment and month
     */
    public function getCompanyDetails(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'subsegment' => 'required|string|in:Airport,Hospital,PTN,PTS,Media'
        ]);

        $year = $request->input('year');
        $month = $request->input('month');
        $subsegment = $request->input('subsegment');

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $this->analyticsService->getCompanyDetails($year, $month, $subsegment),
                'subsegment' => $subsegment,
                'month' => $month,
                'year' => $year
            ]
        ]);
    }

    /**
     * Get subsegment companies with revenue details
     */
    public function getSubsegmentDetails(Request $request)
    {
        $request->validate([
            'subsegment' => 'required|string',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'nullable|integer|min:1|max:12'
        ]);

        $subsegment = $request->input('subsegment');
        $year = $request->input('year');
        $month = $request->input('month');

        $companies = $this->analyticsService->getCompanyDetails($year, $month, $subsegment);

        // Calculate summary
        $totalRevenue = collect($companies)->sum('revenue');
        $totalCompanies = count($companies);
        $avgRevenue = $totalCompanies > 0 ? $totalRevenue / $totalCompanies : 0;

        // Get regional breakdown
        $regionalBreakdown = $this->getRegionalBreakdown($year, $month, $subsegment);

        $summaryData = [
            'total_revenue' => $totalRevenue,
            'total_companies' => $totalCompanies,
            'avg_revenue' => $avgRevenue,
            'formatted_total_revenue' => 'Rp ' . number_format($totalRevenue / 1000000000, 2) . 'M',
            'formatted_avg_revenue' => 'Rp ' . number_format($avgRevenue / 1000000000, 2) . 'M'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies,
                'summary' => $summaryData,
                'regional_breakdown' => $regionalBreakdown,
                'subsegment' => $subsegment,
                'year' => $year,
                'month' => $month
            ]
        ]);
    }

    /**
     * Get regional breakdown for subsegment
     */
    private function getRegionalBreakdown(int $year, ?int $month, string $subsegment): array
    {
        $query = \DB::table('revenues')
            ->join('companies', 'revenues.nip_nas', '=', 'companies.nip_nas')
            ->join('account_manager_company', 'companies.nip_nas', '=', 'account_manager_company.nip_nas')
            ->join('account_managers', 'account_manager_company.nik_am', '=', 'account_managers.nik')
            ->join('witels', 'account_managers.idwitels', '=', 'witels.idwitels')
            ->join('regions', 'witels.region_id', '=', 'regions.id')
            ->where('companies.subsegment', $subsegment)
            ->where('revenues.tahun', $year);

        if ($month) {
            $query->where('revenues.bulan', $month);
        }

        $regionalData = $query
            ->select(
                'regions.id',
                'regions.code',
                'regions.description as name',
                \DB::raw('COUNT(DISTINCT companies.nip_nas) as total_companies'),
                \DB::raw('SUM(revenues.total_revenue) as total_revenue')
            )
            ->groupBy('regions.id', 'regions.code', 'regions.description')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return $regionalData->map(function ($item) {
            $revenue = $item->total_revenue ?? 0;
            return [
                'region_id' => $item->id,
                'region_code' => $item->code,
                'region_name' => $item->name,
                'total_companies' => $item->total_companies,
                'total_revenue' => (float) $revenue,
                'formatted_revenue' => $this->formatCurrency($revenue, 2),
                'percentage' => 0 // Will be calculated on frontend
            ];
        })->toArray();
    }

    /**
     * Get individual company details with historical data
     */
    public function getIndividualCompanyDetails(Request $request)
    {
        $request->validate([
            'company_id' => 'required|string|exists:companies,nip_nas'
        ]);

        $companyId = $request->input('company_id');
        
        // Get company basic info with Account Managers and Witel->Region
        $company = \App\Models\Company::where('nip_nas', $companyId)
            ->with(['accountManagers.witel.region', 'witel.region'])
            ->firstOrFail();
        
        // Get all revenue history for summary calculations
        $allMonthlyData = \App\Models\Revenue::where('nip_nas', $companyId)
            ->selectRaw('tahun, bulan, total_revenue')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get();
        
        // Get last 12 months revenue data for chart
        $monthlyData = \App\Models\Revenue::where('nip_nas', $companyId)
            ->selectRaw('tahun, bulan, total_revenue')
            ->selectRaw('CASE 
                WHEN bulan = 1 THEN "Januari"
                WHEN bulan = 2 THEN "Februari" 
                WHEN bulan = 3 THEN "Maret"
                WHEN bulan = 4 THEN "April"
                WHEN bulan = 5 THEN "Mei"
                WHEN bulan = 6 THEN "Juni"
                WHEN bulan = 7 THEN "Juli"
                WHEN bulan = 8 THEN "Agustus"
                WHEN bulan = 9 THEN "September"
                WHEN bulan = 10 THEN "Oktober"
                WHEN bulan = 11 THEN "November"
                WHEN bulan = 12 THEN "Desember"
                END as bulan_name')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->limit(12)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'bulan' => $item->bulan,
                    'bulan_name' => $item->bulan_name,
                    'revenue' => $item->total_revenue,
                    'formatted_revenue' => 'Rp ' . number_format($item->total_revenue / 1000000000, 2) . 'M',
                    'period_label' => $item->bulan_name . ' ' . $item->tahun
                ];
            });

        // Get yearly totals
        $yearlyData = \App\Models\Revenue::where('nip_nas', $companyId)
            ->selectRaw('tahun, SUM(total_revenue) as total_revenue, COUNT(*) as months_count')
            ->groupBy('tahun')
            ->orderBy('tahun', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'total_revenue' => $item->total_revenue,
                    'formatted_total_revenue' => 'Rp ' . number_format($item->total_revenue / 1000000000, 2) . 'M',
                    'months_count' => $item->months_count
                ];
            });

        // Calculate summary using all data
        $totalRevenue = $allMonthlyData->sum('total_revenue');
        $avgMonthlyRevenue = $allMonthlyData->count() > 0 ? $totalRevenue / $allMonthlyData->count() : 0;
        $bestMonthRecord = $allMonthlyData->sortByDesc('total_revenue')->first();
        $bestYear = $yearlyData->sortByDesc('total_revenue')->first();
        
        // Get month name for best month
        $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                      7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        $summaryData = [
            'total_revenue' => $totalRevenue,
            'avg_monthly_revenue' => $avgMonthlyRevenue,
            'best_month' => $bestMonthRecord ? $monthNames[$bestMonthRecord->bulan] . ' ' . $bestMonthRecord->tahun : null,
            'best_year' => $bestYear ? $bestYear['tahun'] : null,
            'formatted_total_revenue' => 'Rp ' . number_format($totalRevenue / 1000000000, 2) . 'M',
            'formatted_avg_monthly' => 'Rp ' . number_format($avgMonthlyRevenue / 1000000000, 2) . 'M'
        ];

        // Format Account Managers data
        $accountManagers = $company->accountManagers->map(function ($am) {
            return [
                'nik' => $am->nik,
                'nama' => $am->nama,
                'posisi' => $am->posisi,
                'no_gsm' => $am->no_gsm,
                'proporsi' => $am->pivot->proporsi,
                'pembagian' => $am->pivot->pembagian,
                'segment' => $am->pivot->segment,
                'witel_name' => $am->witel ? $am->witel->nama_witels : null,
                'region_name' => $am->witel && $am->witel->region ? $am->witel->region->name : null,
                'region_code' => $am->witel && $am->witel->region ? $am->witel->region->code : null,
            ];
        });

        // Format Region data dari company->witel->region
        $regions = [];
        if ($company->witel && $company->witel->region) {
            $regions[] = [
                'region_code' => $company->witel->region->code,
                'region_name' => $company->witel->region->name,
                'witel_name' => $company->witel->nama_witels,
                'is_primary' => true, // Company hanya punya 1 witel, jadi selalu primary
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'company' => $company,
                'account_managers' => $accountManagers,
                'regions' => $regions,
                'monthly_data' => $monthlyData,
                'yearly_data' => $yearlyData,
                'summary' => $summaryData
            ]
        ]);
    }

    /**
     * Get subsegment trend data
     */
    public function getSubsegmentTrend(Request $request)
    {
        $request->validate([
            'subsegment' => 'required|string|in:Airport,Hospital,PTN,PTS,Media',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $subsegment = $request->input('subsegment');
        $year = $request->input('year');

        return response()->json([
            'success' => true,
            'data' => [
                'trend' => $this->analyticsService->getSubsegmentTrend($subsegment, $year),
                'subsegment' => $subsegment,
                'year' => $year
            ]
        ]);
    }

    /**
     * Get yearly comparison data
     */
    public function getYearlyComparison(Request $request)
    {
        $startYear = $request->input('start_year', date('Y') - 4);
        $endYear = $request->input('end_year', date('Y'));

        return response()->json([
            'success' => true,
            'data' => [
                'yearly_revenue' => $this->analyticsService->getYearlyRevenue($startYear, $endYear),
                'start_year' => $startYear,
                'end_year' => $endYear
            ]
        ]);
    }

    /**
     * Get dashboard analytics summary
     */
    public function getAnalyticsSummary(Request $request)
    {
        $year = $request->input('year', date('Y'));

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $this->analyticsService->getDashboardSummary($year),
                'top_companies' => $this->analyticsService->getTopCompanies($year, 10),
                'subsegment_breakdown' => $this->analyticsService->getSubsegmentRevenue($year),
                'year' => $year
            ]
        ]);
    }

    /**
     * Format currency helper
     */
    private function formatCurrency(float $value, int $decimals = 1): string
    {
        if ($value >= 1000000000000) {
            // Triliun (>= 1000 Miliar)
            return 'Rp ' . number_format($value / 1000000000000, $decimals) . 'T';
        } else {
            // Miliar
            return 'Rp ' . number_format($value / 1000000000, $decimals) . 'M';
        }
    }
}
